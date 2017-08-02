<?php
  $goosegrade_settings_defaults = array(
      'username' => '',
      'password' => '',
      'position' => '',
      'color' => 'blue',
      'site_id' => 0,
   );

   $goosegrade_settings = array();
   if ( get_option( 'goosegrade_settings' ) ) {
      $goosegrade_settings = unserialize( get_option( 'goosegrade_settings' ) );
   }

   // initialize defaults
   foreach ( $goosegrade_settings_defaults as $key => $value ) {
      if ( !isset( $goosegrade_settings[$key] ) ) {
         $goosegrade_settings[$key] = $value;
      }
   }
   $goosegrade_settings['base_url'] = 'http://www.goosegrade.com/';
   $goosegrade_settings['js_url'] = 'http://js.goosegrade.com/';