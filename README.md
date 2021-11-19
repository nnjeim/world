<p><img src="./logo.jpg" width="100" alt="Laravel world"/></p>

A Laravel package to provide a list of the countries, states, cities, timezones, currencies and phone numbers
formatting/validation helpers.

The package can be consumed through Facades, Helpers and Api routes.

### Installation

```
composer require nnjeim/world

php artisan vendor:publish --tag=world

php artisan migrate

php artisan db:seed --class=WorldSeeder (requires ~ 5 - 10min)
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently. 

### Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Usage

#### World Facade

##### List all the countries.

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

##### Fetch a country with its states and cities.

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

#### World Helper Class

##### List all the cities by country id.

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

#### Available methods

| Name | Description | Argument* |
| :--- | :--- |:--- |
| countries | lists all the world countries | array* containing (string) fields* and (array) filters* |
| states | lists all the states | array* containing (string) fields* and (array) filters* |
| cities | lists all the cities | array* containing (string) fields* and (array) filters* |
| timezones | lists all the timezones | array* containing (string) fields* and (array) filters* |
| currencies | lists all the currencies | array* containing (string) fields* and (array) filters* |

The methods' return is structured as below:

* success (boolean)
* message (string)
* data (instance of Illuminate\Support\Collection)
* errors (array)

#### Countries method

* fields*: comma seperated string(countries table fields in addition to states, cities, currency and timezones).
* filters*: array of keys(countries table fields) and their correspondant values.

#### States method

* fields*: comma seperated string(states table fields in addition to country and states).
* filters*: array of keys(states table fields) and their correspondant values.

#### Cities method

* fields*: comma seperated string(cities table fields in addition to country and state).
* filters*: array of keys(cities table fields) and their correspondant values.

#### Timezones method

* fields*: comma seperated string(timezones table fields in addition to country).
* filters*: array of keys(timezones table fields) and their correspondant values.

#### Currencies method

* fields*: comma seperated string(currencies table fields in addition to country).
* filters*: array of keys(currencies table fields) and their correspondant values.

### Available routes

All routes can be prefixed by any string. Ex admin, api, v1 ...

##### Countries

| | |
| :--- | :--- |
| Method | GET |
| Route | /{prefix}/countries |
| Parameters* | comma seperated fields(countries table fields in addition to states, cities, currency and timezones), array filters |
| Example | /v1/countries?fields=iso2,cities&filters[phone_code]=44 |   
| response | success, message, data |  

##### States

| | |
| :--- | :--- |
| Method | GET |
| Route | /{prefix}/states |
| Parameters* | comma seperated fields(states table fields in addition to country and cities), array filters |
| Example | /v1/states?fields=country,cities&filters[country_id]=182 |   
| response | success, message, data |   

##### Cities

| | |
| :--- | :--- |
| Method | GET |
| Route | /{prefix}/cities |
| Parameters* | comma seperated fields(states table fields in addition to country and state), array filters |
| Example | /v1/cities?fields=country,state&filters[country_id]=182 |   
| response | success, message, data | 

##### Timezones

| | |
| :--- | :--- |
| Method | GET |
| Route | /{prefix}/timezones |
| Parameters* | comma seperated fields(states table fields in addition to the country), array filters |
| Example | /v1/timezones?fields=country&filters[country_id]=182 |   
| response | success, message, data | 

##### Currencies

| | |
| :--- | :--- |
| Method | GET |
| Route | /{prefix}/timezones |
| Parameters* | comma seperated fields(states table fields in addition to the country), array filters |
| Example | /v1/timezones?fields=country&filters[country_id]=182 |   
| response | success, message, data |

##### Validate Number

| | |
| :--- | :--- |
| Method | POST |
| Route | /{prefix}/phones/validate |
| Parameters | key, value |
| Example | /v1/phones/validate?number=060550987&phone_code=33 |

##### Strip Number

| | |
| :--- | :--- |
| Method | POST |
| Route | /{prefix}/phones/strip |
| Parameters | key, value |
| Example | /v1/phones/strip?number=060550987&phone_code=33 |

##### Format Number

| | |
| :--- | :--- |
| Method | POST |
| Route | /{prefix}/phones/format |
| Parameters | key, value |
| Example | /v1/phones/format?number=060550987&phone_code=33 |

### Helpers

```
formatNumber($number, $phone_code = null);

ex. formatNumber('06 78 909 876', '33')   returns +33 67 890 9876

stripNumber($number, $phone_code = null);

ex. stripNumber('06 78 909 876', '33') return 33678909876

if the argument $phone_code is not passed to the helpers, the used dialling code would be taken from

config('world.default_phone_code') 
```

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

<p><img src="./schema.jpg" width="600px"/></p>

#### Countries database table fields

```
id, name, iso2, iso3, phone_code, dialling_pattern, region, sub_region, status
```

#### States database table fields

```
id, name, country_id
```

#### Cities database table fields

```
id, name, state_id, country_id
```

#### Timezones database table fields

```
id, name, country_id
```

#### Currencies database table fields

```
id, country_id, name, code, precision, symbol, symbol_native, symbol_first, decimal_mark, thousands_separator
```

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
