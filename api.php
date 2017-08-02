<?php

	require_once( 'compat.php' );

   // load default WordPress information
   require_once( ABSPATH . 'wp-config.php' );
   require_once( ABSPATH . 'wp-includes/class-snoopy.php' );

   require_once( 'xml.php' );
   require_once( 'variables.php' );

   global $goosegrade_xml;
   global $goosegrade_current_xml;
   global $goosegrade_tab_level;
   global $goosegrade_stack;

   function goosegrade_start_element( $parser, $name, $attrs ) {
      global $goosegrade_current_name;
      global $goosegrade_tab_level;
      global $goosegrade_current_xml;
      global $goosegrade_stack;
      global $goosegrade_xml;

      $goosegrade_tab_level = $goosegrade_tab_level + 1;

      $goosegrade_current_xml[$name] = array();
      $goosegrade_current_xml = &$goosegrade_current_xml[$name];
      array_push( $goosegrade_stack, $goosegrade_current_xml );

      $goosegrade_current_name = $name;
   }

   function goosegrade_tag_handler( $parser, $data ) {
      global $goosegrade_current_xml;

      $goosegrade_current_xml[0] = $data;

   }

   function goosegrade_end_element( $parser, $name ) {
      global $goosegrade_tab_level;
      global $goosegrade_stack;

      $goosegrade_tab_level = $goosegrade_tab_level - 1;
      $goosegrade_current_xml = array_pop( $goosegrade_stack );
   }

   class goosegrade {
      var $username;
      var $token;
      var $snoop;

      function goosegrade($username, $token) { //__construct
         $this->username = $username;
         $this->token = $token;

		// the amount of time to wait for a connection
         $this->snoop = new Snoopy();
	 $this->snoop->read_timeout = 3;
      }

	function fetch_content( $url ) {
		$result =  $this->snoop->fetch( $url );
		return $result;
      }

      function get_corrections( $correction_type = 'all', $site_id = 0, $items_per_page = 10,  $current_page = 1 ) {
        global $goosegrade_settings;
        $content = false;

         $url = $goosegrade_settings['base_url'].'api/corrections_list/' . $this->username . '/' . $this->token . '?method=xml&status=' .
            $correction_type . '&items_page=' . $items_per_page . '&page=' . ($current_page - 1);

         if ( $site_id > 0 ) {
         	$url = $url . '&sid=' . $site_id;
         }

         $did_fetch = @$this->fetch_content( $url );
         if ( $did_fetch ) {
            $content = $this->snoop->results;

            $xml = goosegrade_parsexml( $content );

            return goosegrade_array_to_object( $xml['api'] );
         }

         return $content;
      }

      function get_pending_correction_count( $site_id = 0 ) {
         $xml = $this->get_corrections( 'pending', $site_id );
         if ($xml) {
            return (integer)$xml->count;
         } else {
            return 0;
         }
      }

      function get_sites() {
        global $goosegrade_settings;
         $content = false;

         $url = $goosegrade_settings['base_url'].'api/sites_list/' . $this->username . '/' . $this->token . '?method=xml';
         $did_fetch = @$this->fetch_content( $url );
         if ( $did_fetch ) {
            $content = $this->snoop->results;

            $xml = goosegrade_parsexml( $content );
            return goosegrade_array_to_object( $xml['api'] );

            return $xml;
         }

         return $content;
      }

      function change_post( $post_id, $original_content, $changed_content ) {
         global $wpdb;

         $query = "SELECT * from $wpdb->posts WHERE ID = $post_id;";
         $result = $wpdb->get_results($query);

         if ( $result ) {
            $post_content = $result[0]->post_content;

            // check to see if the string is in the content
            $pos = strpos( $post_content, $original_content );
            if ( $pos !== false ) {
               $changed_content = str_replace( $original_content, $changed_content, $result[0]->post_content );

               $query = $wpdb->prepare( "UPDATE $wpdb->posts SET post_content = %s WHERE ID = '$post_id';", $changed_content );
               $result = $wpdb->get_results( $query );

               return true;
            }
         }

         return false;
      }

      function get_user_info( $username ) {
        global $goosegrade_settings;
         $content = false;

         $url = $goosegrade_settings['base_url'].'api/get_user_info/' . $this->username . '/' . $this->token . '?method=xml&user=' . $username;
         $did_fetch = @$this->fetch_content( $url );
         if ( $did_fetch ) {
            $content = $this->snoop->results;

            $xml = goosegrade_parsexml( $content );
            return goosegrade_array_to_object( $xml['api'] );
         }

         return $content;
      }


      function check_correct( $post_id, $original_content, $changed_content ) {
			global $wpdb;

         $query = "SELECT * from $wpdb->posts WHERE ID = $post_id;";
         $result = $wpdb->get_results($query);
       
         if ( $result ) {
            $post_content = $result[0]->post_content;

            // check to see if the string is in the content
            $pos = strpos( $post_content, $original_content );
            if ( $pos !== false ) {
					return true;
            }
         }

         return false;
      }      

      function correct( $id, $status, $correction_type, $post_id, $original, $changed ) {
      	global $goosegrade_settings;
         $content = false;
   
         $url = $goosegrade_settings['base_url'].'api/correction_set_status/' . $this->username . '/' . $this->token . '?method=xml&id=' . $id . '&type=' . $correction_type . '&status=' . $status;

         $did_fetch = @$this->fetch_content( $url );
         if ( $did_fetch ) {
            $content = $this->snoop->results;

            $xml = goosegrade_parsexml( $content );
         }

         // change post
         if ( $status == 'accepted' ) {
            $this->change_post( $post_id, $original, $changed );
         }

         return $content;
      }
   }

?>
