<?php
	$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
	require_once( $parse_uri[0] . 'wp-load.php' );
	

	$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
	require_once( $parse_uri[0] . 'wp-includes/option.php' );
	
	
	$wpmps_mapas =  get_option( 'wpmps_plugin_mapas' );
	
	foreach ($_POST as $key => $valor){
	
		$v = explode( '-', $key);
	
		if (isset($wpmps_mapas[$v[0]][$v[1]][$v[2]][$v[3]])){
				
			$wpmps_mapas[$v[0]][$v[1]][$v[2]][$v[3]] = $valor;
				
		}
	
	}
	
	
	update_option( 'wpmps_plugin_mapas', $wpmps_mapas );
	
	/**
	 * Redirect back to the settings page that was submitted
	*/
	$goback = add_query_arg( 'settings-updated', 'true',  wp_get_referer() );
	wp_redirect( $goback );
	exit;