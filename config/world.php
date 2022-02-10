<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Supported locales.
	|--------------------------------------------------------------------------
	*/
	'accepted_locales' => [
		'ar',
		'bn',
		'br',
		'de',
		'en',
		'es',
		'fr',
		'ja',
		'kr',
		'pl',
		'pt',
		'ro',
		'ru',
		'zh',
	],
	/*
	|--------------------------------------------------------------------------
	| Enabled seeders.
	| The cities seeder depends on the states seeder.
	|--------------------------------------------------------------------------
	*/
	'seeders' => [
		'states' => true,
		'cities' => true,
		'timezones' => true,
		'currencies' => true,
		'languages' => true,
	],
];
