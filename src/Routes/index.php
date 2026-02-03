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

	if (config('world.routes', true)) {

		Route::get('/countries', [Controllers\Country\CountryController::class, 'index'])->name('countries.index');

		if (config('world.modules.states', true)) {
			Route::get('/states', [Controllers\State\StateController::class, 'index'])->name('states.index');
		}

		if (config('world.modules.cities', true)) {
			Route::get('/cities', [Controllers\City\CityController::class, 'index'])->name('cities.index');
		}

		if (config('world.modules.timezones', true)) {
			Route::get('/timezones', [Controllers\Timezone\TimezoneController::class, 'index'])->name('timezones.index');
		}

		if (config('world.modules.currencies', true)) {
			Route::get('/currencies', [Controllers\Currency\CurrencyController::class, 'index'])->name('currencies.index');
		}

		if (config('world.modules.languages', true)) {
			Route::get('/languages', [Controllers\Language\LanguageController::class, 'index'])->name('languages.index');
		}

		if (config('world.modules.geolocate', true)) {
			Route::get('/geolocate', [Controllers\Geolocate\GeolocateController::class, 'index'])->name('geolocate.index');
		}
	}
});
