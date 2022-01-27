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
	// countries
	Route::get('/countries', [Controllers\Country\CountryController::class, 'index'])->name('countries.index');
	// states
	Route::get('/states', [Controllers\State\StateController::class, 'index'])->name('states.index');
	// cities
	Route::get('/cities', [Controllers\City\CityController::class, 'index'])->name('cities.index');
	// timezones
	Route::get('/timezones', [Controllers\Timezone\TimezoneController::class, 'index'])->name('timezones.index');
	// currencies
	Route::get('/currencies', [Controllers\Currency\CurrencyController::class, 'index'])->name('currencies.index');
	// languages
	Route::get('/languages', [Controllers\Language\LanguageController::class, 'index'])->name('languages.index');
	// phones
	Route::post('/phones/validate', [Controllers\Phone\PhoneController::class, 'validate'])->name('phones.validate');
	Route::post('/phones/format', [Controllers\Phone\PhoneController::class, 'format'])->name('phones.format');
	Route::post('/phones/strip', [Controllers\Phone\PhoneController::class, 'strip'])->name('phones.strip');
});
