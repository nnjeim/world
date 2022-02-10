<?php

use Illuminate\Support\Facades\Route;
use Nnjeim\World\Http\Controllers;
use Nnjeim\World\Http\Middleware\Localization;

Route::group([
	'prefix' => '{prefix?}',
	'middleware' => [
		'throttle:60,1',
		Localization::class,
	],
], function () {

	Route::get('/countries', [Controllers\Country\CountryController::class, 'index'])->name('countries.index');

	Route::get('/states', [Controllers\State\StateController::class, 'index'])->name('states.index');

	Route::get('/cities', [Controllers\City\CityController::class, 'index'])->name('cities.index');

	Route::get('/timezones', [Controllers\Timezone\TimezoneController::class, 'index'])->name('timezones.index');

	Route::get('/currencies', [Controllers\Currency\CurrencyController::class, 'index'])->name('currencies.index');

	Route::get('/languages', [Controllers\Language\LanguageController::class, 'index'])->name('languages.index');
});
