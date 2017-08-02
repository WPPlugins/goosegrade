<?php

   require_once( dirname(__FILE__) . '/../../../../wp-config.php' );

   header( 'Expires: 0'); // . gmdate( 'D, d M Y H:i:s', time() + 5 ) . ' GMT' );
   header( 'Cache-control: public, must-revalidate' );
   header( 'Content-type: text/css' );
   header( 'Etag: ' . md5(__FILE__ . get_bloginfo('home')));
   
   global $goosegrade_settings;
   
   $goosegrade_position = $goosegrade_settings['position'];
   
   if ($goosegrade_position == 'topleft' || $goosegrade_position == 'botleft' ) {
      echo ".goosegrade-badge { float: left; margin-right: 5px; }";  
   } else if ($goosegrade_position == 'topright' || $goosegrade_position == 'botright' ) {
      echo ".goosegrade-badge { float: right; margin-left: 5px; }"; 
   }

?>