<?php
  include_once 'variables.php';

   // initialize GooseGrade API
   if ( $goosegrade_settings['username'] && $goosegrade_settings['password'] ) {
      $goosegrade_api = new goosegrade( $goosegrade_settings['username'], $goosegrade_settings['password'] );
   } else {
      $goosegrade_api = false;
   }

?>