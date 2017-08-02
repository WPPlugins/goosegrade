<?php

	require_once( 'api.php' );
	require_once( 'settings.php' );
	require_once( 'compat.php' );

	global $goosegrade_settings;
	$base_url = $goosegrade_settings['base_url'];

	global $goosegrade_user;
	global $goosegrade_api;
	
	if ( $goosegrade_api && isset( $goosegrade_user ) ) {
			$user = $goosegrade_api->get_user_info( $goosegrade_user );

			if ( $user ) {
				include( 'html/profile_box.php' );
			}
	}
?>