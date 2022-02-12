<p style="text-align: center; padding: 3rem;"><img src="./logo.jpg" width="150" alt="Laravel world"/></p>

The World is a Laravel package which provides a list of the countries, states, cities, timezones, currencies and languages.

It can be consumed with the World Facade or the defined Api routes.

### Installation

```
composer require nnjeim/world

php artisan vendor:publish --tag=world

php artisan migrate

php artisan db:seed --class=WorldSeeder (requires ~ 10 - 15min)
```

### Upgrading to the latest version? 
- Delete the published `world.php` file from `config`.   
- Delete the `WorldSeeder.php` file from `database/seeders`
- Re-publish the package assets by issuing the command `php artisan vendor:publish --tag=world`

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently. 

### Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Demo
Please feel free to query https://laravel-world.com 
  
Examples  
https://laravel-world.com/api/countries  
https://laravel-world.com/api/states?filters[country_id]=182&fields=cities

### Usage

#### List all the countries.

Use the World facade  

``` 
use Nnjeim\World\World;

$action =  World::countries();

if ($action->success) {

	$countries = $action->data;
}

response 
{
	"success": true,
	"message": "countries",
	"data": [
		{
			"id": 1,
			"name": "Afghanistan"
		},
		{
			"id": 2,
			"name": "Åland Islands"
		},
		.
		.
		.
	],
}
``` 

Use the Api countries endpoint
```
https://myDomain.local/api/countries
```

#### Fetch a country with its states and cities.

Use the World facade  

``` 
use Nnjeim\World\World;

$action =  World::countries([
	'fields' => 'states,cities',
	'filters' => [
		'iso2' => 'FR',
	]
]);

if ($action->success) {

	$countries = $action->data;
}

response 
{
	"success": true,
	"message": "countries",
	"data": [
		"id": 77,
		"name": "France",
		"states": [
			 {
				"id": 1271,
				"name": "Alo"
			},
			{
				"id": 1272,
				"name": "Alsace"
			},
			.
			.
			.
		],
		"cities": [
			{
				"id": 25148,
				"name": "Abondance"
			},
			{
				"id": 25149,
				"name": "Abrest"
			},
			.
			.
			.
		]
	],
}
```

Use the Api countries endpoint
```
https://myDomain.local/api/countries?fields=states,cities&filters[iso2]=FR
```

#### List all the cities by country id.

``` 
use Nnjeim\World\WorldHelper;

protected $world;

public function __construct(WorldHelper $world) {

	$this->world = $world;
}

$action = $this->world->cities([
	'filters' => [
		'country_id' => 182,
	],
]);

if ($action->success) {

	$cities = $action->data;
}
```

Use the Api cities endpoint
```
https://myDomain.local/api/cities?filters[country_id]=182
```

### Available actions

| Name       | Description                   |
|:-----------|:------------------------------|
| countries  | lists all the world countries |
| states     | lists all the states          |
| cities     | lists all the cities          |
| timezones  | lists all the timezones       |
| currencies | lists all the currencies      |
| languages  | lists all the languages       |

An action response is formed as below:

* success (boolean)
* message (string)
* data (instance of Illuminate\Support\Collection)
* errors (array)

#### Countries action

* fields*: comma seperated string(countries table fields in addition to states, cities, currency and timezones).
* filters*: array of keys(countries table fields) and their corresponding values.

#### States action

* fields*: comma seperated string(states table fields in addition to country and states).
* filters*: array of keys(states table fields) and their corresponding values.

#### Cities action

* fields*: comma seperated string(cities table fields in addition to country and state).
* filters*: array of keys(cities table fields) and their corresponding values.

#### Timezones action

* fields*: comma seperated string(timezones table fields in addition to country).
* filters*: array of keys(timezones table fields) and their corresponding values.

#### Currencies action

* fields*: comma seperated string(currencies table fields in addition to country).
* filters*: array of keys(currencies table fields) and their corresponding values.

#### Languages action

* fields*: comma seperated string(languages table fields).
* filters*: array of keys(languages table fields) and their corresponding values.

### Available Api routes

All routes can be prefixed by any string. Ex admin, api, api ...

#### Countries

| | |
| :--- | :--- |
| Method | GET |
| Route | /{prefix}/countries |
| Parameters* | comma seperated fields(countries table fields in addition to states, cities, currency and timezones), array filters |
| Example | /api/countries?fields=iso2,cities&filters[phone_code]=44 |   
| response | success, message, data |  

#### States

| | |
| :--- | :--- |
| Method | GET |
| Route | /{prefix}/states |
| Parameters* | comma seperated fields(states table fields in addition to country and cities), array filters |
| Example | /api/states?fields=country,cities&filters[country_id]=182 |   
| response | success, message, data |   

#### Cities

| | |
| :--- | :--- |
| Method | GET |
| Route | /{prefix}/cities |
| Parameters* | comma seperated fields(states table fields in addition to country and state), array filters |
| Example | /api/cities?fields=country,state&filters[country_id]=182 |   
| response | success, message, data | 

#### Timezones

| | |
| :--- | :--- |
| Method | GET |
| Route | /{prefix}/timezones |
| Parameters* | comma seperated fields(states table fields in addition to the country), array filters |
| Example | /api/timezones?fields=country&filters[country_id]=182 |   
| response | success, message, data | 

#### Currencies

| |                                                                                       |
| :--- |:--------------------------------------------------------------------------------------|
| Method | GET                                                                                   |
| Route | /{prefix}/currencies                                                                  |
| Parameters* | comma seperated fields(states table fields in addition to the country), array filters |
| Example | /api/timezones?fields=country&filters[country_id]=182                                  |   
| response | success, message, data                                                                |

#### Languages

| |                          |
| :--- |:-------------------------|
| Method | GET                      |
| Route | /{prefix}/languages      |
| Parameters* | comma seperated fields   
| Example | /api/languages?fields=dir |   
| response | success, message, data   |

### Localization

The available locales are ar, bn, br, de, en, es, fr, ja, kr, pl, pt, ro, ru and zh.  
The default locale is en.  
Include in the request header

```
accept-language=locale
```
Alternativley, you can use specific locale with the `World` Facade `setLocale('locale')` helper method. Example:  
```
World::setLocale('zh')->countries();
```

### Schema

<p><img src="./schema.jpg" width="800px"/></p>

### Testing  

Requirements  
- The database is seeded.
- The database connection is defined in the .env file. 

Browse to the package root folder and run:

``` bash
composer install //installs the package dev dependencies
composer test
```

`* optional`
