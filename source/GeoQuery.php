<?php

namespace WP_Forge\GeoQuery;

/**
 * Class GeoQuery
 *
 * Inspired by https://gschoppe.com/wordpress/location-searches/
 *
 * @package WP_Forge\GeoQuery
 */
class GeoQuery {

	/**
	 * The earth's radius in kilometers.
	 *
	 * @var float
	 */
	const EARTH_RADIUS_IN_KILOMETERS = 6378.137;

	/**
	 * The earth's radius in kilometers.
	 *
	 * @var float
	 */
	const EARTH_RADIUS_IN_MILES = 3963.1906;

	/**
	 * GeoQuery constructor.
	 */
	public static function hooks() {
		add_filter( 'posts_fields', array( __CLASS__, 'posts_fields' ), 10, 2 );
		add_filter( 'posts_join', array( __CLASS__, 'posts_join' ), 10, 2 );
		add_filter( 'posts_where', array( __CLASS__, 'posts_where' ), 10, 2 );
		add_filter( 'posts_orderby', array( __CLASS__, 'posts_orderby' ), 10, 2 );
	}

	/**
	 * Check if a query is a geo query.
	 *
	 * @param \WP_Query $query The WordPress query object.
	 *
	 * @return bool
	 */
	public static function isGeoQuery( \WP_Query $query ) {
		$geo = $query->get( 'geo_query', array() );

		return ! empty( $geo );
	}

	/**
	 * Get the geo query parameters from a WordPress query object.
	 *
	 * @param \WP_Query $query The WordPress query object.
	 *
	 * @return array
	 */
	public static function getGeoQuery( \WP_Query $query ) {
		return array_merge(
			array(
				// Default distance is 25 miles
				'distance' => 25,
				'units'    => 'miles',
				// Default coordinates set to the Gulf of Guinea
				'lat'      => 0,
				'lng'      => 0,
				// Default keys are the WordPress default geo coordinate meta keys
				'lat_key'  => 'geo_latitude',
				'lng_key'  => 'geo_longitude',
			),
			$query->get( 'geo_query', array() )
		);
	}

	/**
	 * Add a calculated "distance" parameter to the SQL query using the Haversine formula.
	 *
	 * @param string    $sql   The original SQL.
	 * @param \WP_Query $query The WordPress query object.
	 *
	 * @return string
	 */
	public static function posts_fields( $sql, \WP_Query $query ) {
		if ( self::isGeoQuery( $query ) ) {

			if ( $sql ) {
				$sql .= ', ';
			}

			$sql .= self::haversine_sql( self::getGeoQuery( $query ) ) . ' AS geo_query_distance';
		}

		return $sql;
	}

	/**
	 * Add joins for latitude and longitude to the SQL query.
	 *
	 * @param string    $sql   The original SQL.
	 * @param \WP_Query $query The WordPress query object.
	 *
	 * @return string
	 */
	public static function posts_join( $sql, \WP_Query $query ) {
		global $wpdb;

		if ( self::isGeoQuery( $query ) ) {
			if ( $sql ) {
				$sql .= ' ';
			}
			$sql .= "INNER JOIN {$wpdb->prefix}postmeta AS geo_query_lat ON ( {$wpdb->prefix}posts.ID = geo_query_lat.post_id ) ";
			$sql .= "INNER JOIN {$wpdb->prefix}postmeta AS geo_query_lng ON ( {$wpdb->prefix}posts.ID = geo_query_lng.post_id ) ";
		}

		return $sql;
	}

	/**
	 * Match on the correct meta keys and filter by distance.
	 *
	 * @param string    $sql   The original SQL.
	 * @param \WP_Query $query The WordPress query object.
	 *
	 * @return string
	 */
	public static function posts_where( $sql, \WP_Query $query ) {
		global $wpdb;

		if ( self::isGeoQuery( $query ) ) {

			$geo = self::getGeoQuery( $query );

			if ( $sql ) {
				$sql .= ' AND ';
			}

			$sql .= $wpdb->prepare(
				'( geo_query_lat.meta_key = %s AND geo_query_lng.meta_key = %s AND ' . self::haversine_sql( $geo ) . ' <= %f )', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$geo['lat_key'],
				$geo['lng_key'],
				$geo['distance']
			);

		}

		return $sql;
	}

	/**
	 * Handle ordering results by distance.
	 *
	 * @param string    $sql   The original SQL.
	 * @param \WP_Query $query The WordPress query object.
	 *
	 * @return string
	 */
	public static function posts_orderby( $sql, \WP_Query $query ) {
		if ( self::isGeoQuery( $query ) ) {
			if ( 'distance' === $query->get( 'orderby' ) ) {
				$order = $query->get( 'order', 'ASC' );
				$sql   = "geo_query_distance {$order}";
			}
		}

		return $sql;
	}

	/**
	 * Generate the Haversine formula in MySQL.
	 *
	 * @param array $geo The geo query parameters.
	 *
	 * @return string
	 */
	protected static function haversine_sql( array $geo ) {

		global $wpdb;

		// Set units
		$units = 'mi';
		if ( isset( $geo['units'] ) && 'mi' !== substr( strtolower( $geo['units'] ), 0, 2 ) ) {
			$units = 'km';
		}

		$radius = 'mi' === $units ? self::EARTH_RADIUS_IN_MILES : self::EARTH_RADIUS_IN_KILOMETERS;

		$sql = <<<'FORMULA'
( 
	%f * ACOS( 
		COS( RADIANS( %f ) ) * 
		COS( RADIANS( geo_query_lat.meta_value ) ) * 
		COS( RADIANS( geo_query_lng.meta_value ) - RADIANS( %f ) ) + 
		SIN( RADIANS( %f ) ) * SIN( RADIANS( geo_query_lat.meta_value ) ) 
	)
)
FORMULA;

		return $wpdb->prepare( $sql, $radius, $geo['lat'], $geo['lng'], $geo['lat'] ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

}
