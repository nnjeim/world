<?php

use Illuminate\Support\Facades\Route;
use Nnjeim\World\Http\Controllers;
use Nnjeim\World\Http\Middleware\Localization;

Route::group([
	'prefix' => '{prefix?}',
	'middleware' => [
		'throttle:60,1',
		Localization::class,
		'bindings',
	],
], function () {
	/*-- Countries ---------------------------------------------------------------------------------------------------*/
	Route::group([
		'prefix' => 'countries',
		'as' => 'countries',
	], function () {
		Route::get('/', [Controllers\Country\CountryController::class, 'index'])->name('index');
	});
	/*-- States ------------------------------------------------------------------------------------------------------*/
	Route::group([
		'prefix' => 'states',
		'as' => 'states',
	], function () {
		Route::get('/', [Controllers\State\StateController::class, 'index'])->name('index');
	});
	/*-- Cities ------------------------------------------------------------------------------------------------------*/
	Route::group([
		'prefix' => 'cities',
		'as' => 'cities',
	], function () {
		Route::get('/', [Controllers\City\CityController::class, 'index'])->name('index');
	});
	/*-- Timezones ---------------------------------------------------------------------------------------------------*/
	Route::group([
		'prefix' => 'timezones',
		'as' => 'timezones',
	], function () {
		Route::get('/', [Controllers\Timezone\TimezoneController::class, 'index'])->name('index');
	});
	/*-- Currencies --------------------------------------------------------------------------------------------------*/
	Route::group([
		'prefix' => 'currencies',
		'as' => 'currencies',
	], function () {
		Route::get('/', [Controllers\Currency\CurrencyController::class, 'index'])->name('index');
	});
	/*-- Phones ------------------------------------------------------------------------------------------------------*/
	Route::group([
		'prefix' => 'phones',
		'as' => 'phones',
	], function () {
		Route::post('/validate', [Controllers\Phone\PhoneController::class, 'validate'])->name('validate');
		Route::post('/format', [Controllers\Phone\PhoneController::class, 'format'])->name('format');
		Route::post('/strip', [Controllers\Phone\PhoneController::class, 'strip'])->name('strip');
	});
});
