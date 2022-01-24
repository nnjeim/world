<?php

return [
	'errors' => [
		'server_error' => 'Internal Server Error',
		'record_not_found' => 'No :attribute was found!',
	],
	'actions' => [
		'format_error' => 'The phone number is wrongly formatted!',
		'format_valid' => 'The phone number format is valid.',
	],
	'attributes' => [
		'phone' => 'phone|phones',
		'country' => 'country|countries',
		'city' => 'city|cities',
		'state' => 'state|states',
		'timezone' => 'timezone|timezones',
		'currency' => 'currency|currencies',
		'language' => 'language|languages',
	],
];
