<p style="text-align: center; padding: 3rem;"><img src="./logo.jpg" width="150" alt="Laravel world"/></p>

The World is a Laravel package that provides a comprehensive list of countries, states, cities, timezones, currencies, and languages. You can access the data using the **World Facade** or through defined API routes.

## Table of Contents

- [Installation](#installation)
  - [Automated Installation](#automated-installation)
  - [Manual Installation](#manual-installation)
- [What's New in v1.1.34](#whats-new-in-v1134)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Examples](#examples)
- [Usage](#usage)
  - [List All Countries](#list-all-countries)
  - [Fetch Country with States & Cities](#fetch-country-with-states--cities)
  - [List All Cities by Country ID](#list-all-cities-by-country-id)
- [Available Actions](#available-actions)
- [Available API Routes](#available-api-routes)
  - [Countries](#countries)
  - [States](#states)
  - [Cities](#cities)
  - [Timezones](#timezones)
  - [Currencies](#currencies)
  - [Languages](#languages)
- [Localization](#localization)
- [Schema](#schema)
- [Configuration](#configuration)
- [Testing](#testing)
- 
### Installation

First, set your application environment to local:

```bash
set APP_ENV=local
```

Then, install the package via composer:

```
composer require nnjeim/world
```

Optionally, set the WORLD_DB_CONNECTION environment variable to your desired database connection.

#### Automated Installation

Run the following Artisan command to automate the installation process:

```
php artisan world:install
```
#### Manual Installation
If you prefer to install the package manually, follow these steps:

1. Publish the package configuration file:

```bash
php artisan vendor:publish --tag=world --force
```
2. Run the migrations:

```bash
php artisan migrate 
```
3. Seed the database:

```bash
php artisan db:seed --class=WorldSeeder
````

### What's new in v1.1.34?  
- Tested with Laravel 11 and php 8.3/8.4
- Tested with Laravel 12 and php 8.3/8.4
- Minor code improvements

### Changelog

For detailed information on recent changes, please see the [CHANGELOG](CHANGELOG.md).

### Contributing

We welcome contributions! For details on how to get started, please review our [CONTRIBUTING](CONTRIBUTING.md) guidlines.
  
Examples  
--------
Explore the API examples on our live site:

List all countries:  
https://laravel-world.com/api/countries  
Search for a country:   
https://laravel-world.com/api/countries?search=rom  
Get states by country code:  
https://laravel-world.com/api/states?filters[country_code]=RO&fields=cities  

### Usage

#### List all the countries

Use the `World` facade:

```php
use Nnjeim\World\World;

$action =  World::countries();

if ($action->success) {
  $countries = $action->data;
}

response (object)
{
  "success": true,
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

Use the API countries endpoint:

```
https://myDomain.local/api/countries
```

#### Fetch a country with its states and cities.

Use the `World` facade:

```php
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
```

Response: 
```
(object)
{
  "success": true,
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

Use the API countries endpoint:

```
https://myDomain.local/api/countries?fields=states,cities&filters[iso2]=FR
```

#### List all the cities by country id

```php
use Nnjeim\World\WorldHelper;

new class {
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
}
```

Use the API cities endpoint:

```
https://myDomain.local/api/cities?filters[country_code]=RO 
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

* `success` (boolean)
* `message` (string)
* `data` (instance of `Illuminate\Support\Collection`)
* `errors` (array)

#### Countries action

* `fields`*: comma seperated string (countries table fields in addition to states, cities, currency and timezones).
* `filters`*: array of keys (countries table fields) and their corresponding values.
* `search`*: string.

#### States action

* `fields`*: comma seperated string (states table fields in addition to country and states).
* `filters`*: array of keys (states table fields) and their corresponding values.
* `search`*: string.

#### Cities action

* `fields`*: comma seperated string (cities table fields in addition to country and state).
* `filters`*: array of keys (cities table fields) and their corresponding values.
* `search`*: string.

#### Timezones action

* `fields`*: comma seperated string (timezones table fields in addition to country).
* `filters`*: array of keys (timezones table fields) and their corresponding values.
* `search`*: string.

#### Currencies action

* `fields`*: comma seperated string (currencies table fields in addition to country).
* `filters`*: array of keys (currencies table fields) and their corresponding values.
* `search`*: string.

#### Languages action

* `fields`*: comma seperated string (languages table fields).
* `filters`*: array of keys (languages table fields) and their corresponding values.
* `search`*: string.

### Available API routes

All routes can be prefixed by any string. Ex.: `admin`, `api`...

#### Countries

|             |                                                                                                                                     |
|:------------|:------------------------------------------------------------------------------------------------------------------------------------|
| Method      | GET                                                                                                                                 |
| Route       | `/{prefix}/countries`                                                                                                               |
| Parameters* | comma seperated fields (countries table fields in addition to states, cities, currency and timezones), array filters, string search |
| Example     | `/api/countries?fields=iso2,cities&filters[phone_code]=44  `                                                                        |   
| response    | success, message, data                                                                                                              |  

#### States

|             |                                                                                                              |
|:------------|:-------------------------------------------------------------------------------------------------------------|
| Method      | GET                                                                                                          |
| Route       | `/{prefix}/states`                                                                                           |
| Parameters* | comma seperated fields (states table fields in addition to country and cities), array filters, string search |
| Example     | `/api/states?fields=country,cities&filters[country_code]=RO`                                                 |   
| response    | success, message, data                                                                                       |   

#### Cities

|             |                                                                                                             |
|:------------|:------------------------------------------------------------------------------------------------------------|
| Method      | GET                                                                                                         |
| Route       | `/{prefix}/cities`                                                                                          |
| Parameters* | comma seperated fields (cities table fields in addition to country and state), array filters, string search |
| Example     | `/api/cities?fields=country,state&filters[country_code]=RO`                                                 |   
| response    | success, message, data                                                                                      | 

#### Timezones

|             |                                                                                                          |
|:------------|:---------------------------------------------------------------------------------------------------------|
| Method      | GET                                                                                                      |
| Route       | `/{prefix}/timezones`                                                                                    |
| Parameters* | comma seperated fields (timezones table fields in addition to the country), array filters, string search |
| Example     | `/api/timezones?fields=country&filters[country_code]=RO`                                                 |   
| response    | success, message, data                                                                                   | 

#### Currencies

|             |                                                                                                           |
|:------------|:----------------------------------------------------------------------------------------------------------|
| Method      | GET                                                                                                       |
| Route       | `/{prefix}/currencies`                                                                                    |
| Parameters* | comma seperated fields (currencies table fields in addition to the country), array filters, string search |
| Example     | `/api/currencies?fields=code&filters[country_code]=RO`                                                    |   
| response    | success, message, data                                                                                    |

#### Languages

|             |                                       |
|:------------|:--------------------------------------|
| Method      | GET                                   |
| Route       | `/{prefix}/languages`                 |
| Parameters* | comma seperated fields, string search |
| Example     | `/api/languages?fields=dir`           |   
| response    | success, message, data                |

### Localization

The available locales are 
```
ar, az, bn, br, de, en, es, fr, it, ja, kr, nl, pl, pt, ro, ru, tr and zh.  
```
The default locale is en.

Header option

```
accept-language=locale
```

Alternatively, you can use specific locale with the `World` Facade `setLocale('locale')` helper method. Example: 

```php
World::setLocale('zh')->countries();
```

### Schema

<p><img src="./schema.jpg" width="800px" /></p>

### Configuration  
The configuration for the World package is located in the world.php file.  
If you're upgrading from a previous version, you may want to re-publish the config file:

```bash
php artisan vendor:publish --tag=world --force
```

#### Customizing database connection

By default, this package uses the default database connection, but it's possible to customize it
using the `WORLD_DB_CONNECTION` variable in your `.env` file.

### Countries restrictions
Countries can be restricted while seeding the database either by adding the ISO2 country codes in the `allowed_countries` or `disallowed_countries` array lists.  

#### Supported Locales  
A list of the accepted locales which relate to the localized [`lang/` files](/resources/lang).

#### Modules enablement  
The states, cities, timezones, currencies and languages modules can be optionally disabled.    
Please note that the cities module depends on the states module.  

#### Routes  
If you don't wish to use the packages as an API service, you can disable all the routes by assigning `false` to `routes`.  

#### Migrations  
It offers the ability to enable or disable the database fields.  
When changing this configuration the database should be dropped and the seeder should be re-run.  

### Testing  

Requirements  
- The database is seeded.
- The database connection is defined in the .env file. 

Browse to the package root folder and run:

```bash
composer install # installs the package dev dependencies
composer test
```

`* optional`
