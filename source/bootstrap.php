<?php

if ( function_exists( 'add_action' ) ) {

	add_action( 'after_setup_theme', array( 'WP_Forge\\GeoQuery\\GeoQuery', 'hooks' ) );

}
