# WordPress Geo Query

<a href="https://wordpress.org/" target="_blank">
    <img src="https://img.shields.io/static/v1?label=&message=4.7+-+5.4&color=blue&style=flat-square&logo=wordpress&logoColor=white" alt="WordPress Versions">
</a>
<a href="https://www.php.net/" target="_blank">
    <img src="https://img.shields.io/static/v1?label=&message=5.3+-+7.4&color=777bb4&style=flat-square&logo=php&logoColor=white" alt="PHP Versions">
</a>

Perform location based searches in WordPress.

## Installation

Install [Composer](https://getcomposer.org/).

In your WordPress plugin or theme directory, run:

```
composer require wp-forge/wp-geo-query
```

Make sure you have this line of code in your project:

```php
<?php

require __DIR__ . '/vendor/autoload.php';
```

## Usage

When creating a custom query:

```php
<?php

$query = new WP_Query(
	array(
		'geo_query' => array(
			'lat'      => 0,
			'lng'      => 0,
			'distance' => 25,               // Default value is 25
			'units'    => 'miles',          // Default is miles, could also be 'mi', 'km', or 'kilometers'
			'lat_key'  => 'geo_latitude',   // Default value is 'geo_latitude'
			'lng_key'  => 'geo_longitude',  // Default value is 'geo_longitude'
		),
		// ...
	)
);
```

When using `pre_get_posts`:

```php
<?php

add_action(
	'pre_get_posts',
	function ( WP_Query $query ) {
		if ( $query->is_main_query() /* Customize your conditional to meet your needs */ ) {
			$query->set(
				'geo_query',
				array(
					'lat'      => 0,
					'lng'      => 0,
					'distance' => 25,               // Default value is 25
					'units'    => 'miles',          // Default value is 'miles', could also be 'mi', 'km', or 'kilometers'
					'lat_key'  => 'geo_latitude',   // Default value is 'geo_latitude'
					'lng_key'  => 'geo_longitude',  // Default value is 'geo_longitude'
				)
			);
		}
	}
);
```
