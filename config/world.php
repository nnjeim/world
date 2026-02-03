<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Allowed countries to be loaded.
	| Leave it empty to load all countries else include the country iso2
	| value in the allowed_countries array.
	|--------------------------------------------------------------------------
	*/

	'allowed_countries' => [],

	/*
	|--------------------------------------------------------------------------
	| Disallowed countries to not be loaded.
	| Leave it empty to allow all countries to be loaded else include the
	| country iso2 value in the disallowed_countries array.
	|--------------------------------------------------------------------------
	*/

	'disallowed_countries' => [],

	/*
	|--------------------------------------------------------------------------
	| Supported locales.
	|--------------------------------------------------------------------------
	*/

	'accepted_locales' => [
		'ar',
		'az',
		'bn',
		'br',
		'de',
		'en',
		'es',
		'fa',
		'fr',
		'hr',
		'hy',
		'it',
		'ja',
		'kr',
		'ne',
		'nl',
		'pl',
		'pt',
		'ro',
		'ru',
		'sw',
		'tr',
		'zh',
	],

	/*
	|--------------------------------------------------------------------------
	| Enabled modules.
	| The cities module depends on the states module.
	|--------------------------------------------------------------------------
	*/

	'modules' => [
		'states' => true,
		'cities' => true,
		'timezones' => true,
		'currencies' => true,
		'languages' => true,
		'geolocate' => true,
	],

	/*
	|--------------------------------------------------------------------------
	| Routes.
	|--------------------------------------------------------------------------
	*/

	'routes' => true,

	/*
	|--------------------------------------------------------------------------
	| Connection.
	|--------------------------------------------------------------------------
	*/

	'connection' => env('WORLD_DB_CONNECTION', env('DB_CONNECTION')),

	/*
	|--------------------------------------------------------------------------
	| Migrations.
	|--------------------------------------------------------------------------
	*/

	'migrations' => [
		'countries' => [
			'table_name' => 'countries',
			'optional_fields' => [
				'phone_code' => [
					'required' => true,
					'type' => 'string',
					'length' => 5,
				],
				'iso3' => [
					'required' => true,
					'type' => 'string',
					'length' => 3,
				],
				'native' => [
					'required' => false,
					'type' => 'string',
				],
				'region' => [
					'required' => true,
					'type' => 'string',
				],
				'subregion' => [
					'required' => true,
					'type' => 'string',
				],
				'latitude' => [
					'required' => false,
					'type' => 'string',
				],
				'longitude' => [
					'required' => false,
					'type' => 'string',
				],
				'emoji' => [
					'required' => false,
					'type' => 'string',
				],
				'emojiU' => [
					'required' => false,
					'type' => 'string',
				],
			],
		],
		'states' => [
			'table_name' => 'states',
			'optional_fields' => [
				'country_code' => [
					'required' => true,
					'type' => 'string',
					'length' => 3,
				],
				'state_code' => [
					'required' => false,
					'type' => 'string',
					'length' => 5,
				],
				'type' => [
					'required' => false,
					'type' => 'string',
				],
				'latitude' => [
					'required' => false,
					'type' => 'string',
				],
				'longitude' => [
					'required' => false,
					'type' => 'string',
				],
			],
		],
		'cities' => [
			'table_name' => 'cities',
			'optional_fields' => [
				'country_code' => [
					'required' => true,
					'type' => 'string',
					'length' => 3,
				],
				'state_code' => [
					'required' => false,
					'type' => 'string',
					'length' => 5,
				],
				'latitude' => [
					'required' => false,
					'type' => 'string',
				],
				'longitude' => [
					'required' => false,
					'type' => 'string',
				],
			],
		],
		'timezones' => [
			'table_name' => 'timezones',
		],
		'currencies' => [
			'table_name' => 'currencies',
		],
		'languages' => [
			'table_name' => 'languages',
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Fully qualified class names for package models.
	| You can extend package models with your custom ones.
	|--------------------------------------------------------------------------
	*/

	'models' => [
		'cities' => \Nnjeim\World\Models\City::class,
		'countries' => \Nnjeim\World\Models\Country::class,
		'currencies' => \Nnjeim\World\Models\Currency::class,
		'languages' => \Nnjeim\World\Models\Language::class,
		'states' => \Nnjeim\World\Models\State::class,
		'timezones' => \Nnjeim\World\Models\Timezone::class,
	],

	/*
	|--------------------------------------------------------------------------
	| Geolocation Settings.
	| Uses MaxMind GeoLite2 database for IP-based geolocation.
	|--------------------------------------------------------------------------
	*/

	'geolocate' => [
		/*
		|--------------------------------------------------------------------------
		| GeoIP Database Path.
		| Path to the MaxMind GeoLite2-City.mmdb database file.
		|--------------------------------------------------------------------------
		*/
		'database_path' => storage_path('app/geoip/GeoLite2-City.mmdb'),

		/*
		|--------------------------------------------------------------------------
		| Cache TTL.
		| How long to cache geolocation results in seconds.
		| Default: 86400 (24 hours)
		|--------------------------------------------------------------------------
		*/
		'cache_ttl' => env('WORLD_GEOLOCATE_CACHE_TTL', 86400),

		/*
		|--------------------------------------------------------------------------
		| MaxMind License Key.
		| Required for automatic database downloads.
		| Get a free license key at: https://www.maxmind.com/en/geolite2/signup
		|--------------------------------------------------------------------------
		*/
		'maxmind_license_key' => env('MAXMIND_LICENSE_KEY'),

		/*
		|--------------------------------------------------------------------------
		| Fallback API.
		| If true, uses ip-api.com when GeoLite2 database is not available.
		| ip-api.com is free (45 requests/minute) and requires no license.
		|--------------------------------------------------------------------------
		*/
		'fallback_api' => env('WORLD_GEOLOCATE_FALLBACK_API', true),
	],

];
