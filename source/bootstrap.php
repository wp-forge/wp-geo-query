<?php

use WP_Forge\GeoQuery\GeoQuery;

if ( ! function_exists( 'wpforge_add_wp_geo_query_hooks' ) && function_exists( 'add_action' ) ) {

	/**
	 * Setup GeoQuery hooks.
	 */
	function wpforge_add_wp_geo_query_hooks() {
		GeoQuery::hooks();
	}

	add_action( 'after_setup_theme', 'wpforge_add_wp_geo_query_hooks' );
	
}
