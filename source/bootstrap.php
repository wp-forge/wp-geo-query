<?php

if ( ! function_exists( 'wpforge_initialize_wp_geo_query' ) ) {

	function wpforge_initialize_wp_geo_query() {
		add_action( 'after_setup_theme', array( 'WP_Forge\\GeoQuery\\GeoQuery', 'hooks' ) );
	}

	if ( function_exists( 'add_action' ) ) {
		wpforge_initialize_wp_geo_query();
	}

}
