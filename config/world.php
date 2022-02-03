<?php

return [
	/* -----------------------------------------------------
	 * Default dialling country code.
	 * Used when the default dialling argument is not passed
	 * to the helper methods.
	 * --------------------------------------------------- */
	'default_phone_code' => '40',
	/* -----------------------------------------------------
	 * Supported locales.
	 * --------------------------------------------------- */
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
	/* -----------------------------------------------------
	 * Enable the seeder to be run.
	 * The cities seeder depends on the states set to true.
	 * --------------------------------------------------- */
	'seeders' => [
		'states' => true,
		'cities' => true,
		'timezones' => true,
		'currencies' => true,
		'languages' => true,
	],
];
