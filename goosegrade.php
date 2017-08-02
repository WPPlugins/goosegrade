<?php
/*
Plugin Name: gooseGrade
Plugin URI: http://goosegrade.com/wordpress
Description: To setup go to Settings->gooseGrade. After setup you can view corrections under Posts-> Corrections.
Author: BraveNewCode Inc.
Version: 1.29
Author URI: http://www.bravenewcode.com
*/
	require_once( 'compat.php' );

include_once 'variables.php';
   // load GooseGrade API
   require_once( compat_get_plugin_dir( 'goosegrade' ) . '/api.php' );

   // set up WordPress hooks
   add_action( 'admin_menu', 'goosegrade_alter_menu', 1 );
   add_action( 'admin_head', 'goosegrade_admin_head');
   add_filter( 'init', 'goosegrade_init' );

   add_action( 'wp_head', 'goosegrade_head' );
	remove_action( 'the_content', 'wptexturize' );

	add_filter( 'query_vars', 'goosegrade_query_vars' );
	add_filter( 'parse_request', 'goosegrade_parse_request' );	
	
   global $goosegrade_settings;

	// initialize gooseGrade API
	if ( $goosegrade_settings['username'] && $goosegrade_settings['password'] ) {
		global $goosegrade_api;
		$goosegrade_api = new goosegrade( $goosegrade_settings['username'], $goosegrade_settings['password'] );
		if ( $goosegrade_settings["site_id"] == 0 ) {
			// save the site ID
			$goosegrade_settings["site_id"] = goosegrade_check_site_id();
			update_option( 'goosegrade_settings', serialize($goosegrade_settings) );
		}
   } else {
      $goosegrade_api = false;
   }

	function goosegrade_query_vars( $vars ) {
		$vars[] = "goosegrade";
		$vars[] = "goosegrade_user";
		return $vars;
	}   
	
	function goosegrade_parse_request( $wp ) {
		global $goosegrade_user;
		
		if  ( array_key_exists( "goosegrade", $wp->query_vars ) && array_key_exists( "goosegrade_user", $wp->query_vars ) ) {
			switch ( $wp->query_vars["goosegrade"] ) {
				case "profile":
					$goosegrade_user = $wp->query_vars["goosegrade_user"];
					include( 'ajax.php' );	
					break;
			}
			exit;
		}	
	}	

   // check for a form submissions
   if (isset($_GET['action']) && $_GET['action'] == 'correct') {
      $split_array = explode( '?', $_SERVER['REQUEST_URI'] );
      $base_url = $split_array[0];
      $redirect_url = $base_url . '?page=' . $_GET['page'];
      $post_id = $_GET['extra'];
      $original = $_GET['original'];
      $changed = $_GET['new'];

      $goosegrade_api->correct( $_GET['id'], $_GET['status'], $_GET['type'], $post_id , $original, $changed );

      header( 'Location: ' . $redirect_url );
      die;
   }

   function goosegrade_init() {
   	$priority = 10;
    	if ( class_exists( 'GA_Filter' ) ) {
    		// for Google analytics plugin
    		$priority = 100;
    	}
   	add_filter( 'the_content', 'goosegrade_content_filter', $priority );
   }

   function goosegrade_admin_head() {
      echo '<link rel="stylesheet" type="text/css" href="' . compat_get_plugin_url( 'goosegrade' ) . '/css/admin.css"></link>';
   	}

   function goosegrade_head() {
      echo '<link rel="stylesheet" type="text/css" href="' . compat_get_plugin_url( 'goosegrade' ) . '/css/style.css"></link>';
   	}

   function goosegrade_insert_widget() {
  		  global $goosegrade_settings;
  		  if ( $goosegrade_settings['position'] == 'topleft' || $goosegrade_settings['position'] == 'botleft' ) {
  		  	$goose_class = 'goosegrade-badge-left';
  		  } else {
  		  	$goose_class = 'goosegrade-badge-right';
  		  }

        $new_content  = '<div class="'.$goose_class.'"><script src="'.$goosegrade_settings['js_url'].'grade.php?sid=' . $goosegrade_settings['site_id'] . '" type="text/javascript"></script>';
        $new_content .= '<a href="javascript:void(0);"><img  border="0" onmouseover="return gg_load(this);" onclick="return gg_grade(\'' . get_permalink() . '\',' . get_the_ID() . ');" ';
        $new_content .= 'title="Suggest spelling, factual, grammar, and other corrections to the author. Click here." src="'.$goosegrade_settings['base_url'].'badge.php?sid=' . $goosegrade_settings['site_id'] . '&amp;page=' . get_permalink() . '" /></a>';
        $new_content .= '</div>';

        return $new_content;
   }

   function goosegrade_content_filter($content) {
      global $goosegrade_settings;

      if ( $goosegrade_settings['site_id'] ) {

      	if ( !$goosegrade_settings['show_on_blog'] && !is_single() ) {
      	  return $content;
      	}

      	if ( $goosegrade_settings['position'] == 'topleft' || $goosegrade_settings['position'] == 'topright' ) {
      		$new_content = goosegrade_insert_widget() . $content . '<div class="goosegrade-clear"></div>';
      	} else if ( $goosegrade_settings['position'] == 'botleft' || $goosegrade_settings['position'] == 'botright' ) {
      		$new_content = $content . goosegrade_insert_widget() . '<div class="goosegrade-clear"></div>';
      	} else {
      	  // custom content, do nothing
      	  return $content;
      	}

         return $new_content;
      } else {
         return $content;
      }

   }

   function goosegrade_alter_menu() {
      global $goosegrade_api;
      global $goosegrade_settings;
      $site_id = 0;
      if ( isset( $goosegrade_settings['site_id'] ) ) {
      	$site_id = $goosegrade_settings['site_id'];
      }

      $wordpress_version = (float)get_bloginfo('version');

      if ($wordpress_version >= 2.7 && $goosegrade_api) {
         // add the number of outstanding corrections in a little circle on the menu
         add_posts_page( 'Posts', 'Corrections <span class="update-plugins count-1"><span class="pending-count">' .
         $goosegrade_api->get_pending_correction_count( $site_id  ) . '</span></span>', 8, __FILE__, 'goosegrade_corrections_page' );
      } else {
         add_management_page( 'Corrections', 'Corrections', 8, __FILE__, 'goosegrade_corrections_page' );
      }

      add_options_page( 'gooseGrade', 'gooseGrade', 9, __FILE__, 'goosegrade_options_page' );
   }

   function goosegrade_options_page() {
   	global $goosegrade_wpmu_dir;
   	
   	include( compat_get_plugin_dir( 'goosegrade' ) . '/html/settings.php' );
   }

   function goosegrade_corrections_page() {
   	global $goosegrade_wpmu_dir;
   	
      include( compat_get_plugin_dir( 'goosegrade' ). '/html/table.php' );
   }

   function goosegrade_short_content( $text_area, $content ) {
   	$char_count = 200;

   	$content = strip_tags( $content );

   	$pos = strpos( $content, $text_area );

   	if ( $pos > $char_count ) {
   		$left_pos = $pos - $char_count;
   	} else {
   		$left_pos = 0;
   	}

   	$right_pos = $pos + strlen( $text_area ) + $char_count;

   	$total_length = strlen( $content );

   	if ( $right_pos >= $total_length )
   	{
   		$right_pos = $total_length;
   	}

   	$text = substr( $content, $left_pos, $right_pos - $left_pos + 1 );
   	return "..." . $text . "...";
	}

	function goosegrade_check_site_id() {
		global $goosegrade_api;
		$sites = $goosegrade_api->get_sites();
		$site_id = 0;
		if ($sites !== false && $sites->status == 0 && $sites->count > 0) {
			for ($i = 1; $i <= $sites->count; $i++) {
				eval( '$x = goosegrade_array_to_object( $sites->site_' . $i . '); $url = $x->url;' );
				if ( rtrim( strtolower($url), '/' ) == rtrim( strtolower( get_bloginfo( 'wpurl' ) ), '/' ) ) {
					eval( '$site_id = (integer)$x->id;' );
					break;
		             			}
		         	 	}
		       	}
		return $site_id;
	}

?>
