<?php

namespace Nnjeim\World\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Nnjeim\World\Models;
use Illuminate\Database\Seeder;

class SeedAction extends Seeder
{
	private array $countries = [
		'data' => [],
	];

	private array $modules = [
		'states' => [
			'class' => Models\State::class,
			'data' => [],
			'enabled' => false,
		],
		'cities' => [
			'class' => Models\City::class,
			'data' => [],
			'enabled' => false,
		],
		'timezones' => [
			'class' => Models\Timezone::class,
			'enabled' => false,
		],
		'currencies' => [
			'class' => Models\Currency::class,
			'data' => [],
			'enabled' => false,
		],
		'languages' => [
			'class' => Models\Language::class,
			'data' => [],
			'enabled' => false,
		],
	];

	public function __construct()
	{
		// countries
		$this->initCountries();
		// init modules
		foreach (config('world.seeders') as $module => $enabled) {
			if ($enabled) {
				$this->modules[$module]['enabled'] = true;
				$this->initModule($module);
			}
		}
	}

	public function run(): void
	{
		$this->command->getOutput()->block('Seeding start');

		$countryFillables = (new Models\Country())->getFillable();

		$this->command->getOutput()->progressStart(count($this->countries['data']));

		$transform = function ($countryArray) use ($countryFillables) {
			$return = [];

			foreach (array_keys($countryArray) as $key) {
				if ($key === 'subregion') {
					$return['sub_region'] = trim((string) $countryArray[$key]);
					continue;
				}

				if (in_array($key, $countryFillables)) {
					$return[$key] = trim((string) $countryArray[$key]);
				}
			}

			return $return;
		};

		foreach ($this->countries['data'] as $countryArray) {

			$country = Models\Country::create($transform($countryArray));
			// states and cities
			if ($this->isModuleEnabled('states')) {
				$this->seedStates($country, $countryArray);
			}
			// timezones
			if ($this->isModuleEnabled('timezones')) {
				$this->seedTimezones($country, $countryArray);
			}
			// currencies
			if ($this->isModuleEnabled('currencies')) {
				$this->seedCurrencies($country, $countryArray);
			}

			$this->command->getOutput()->progressAdvance();
		}

		// languages
		if ($this->isModuleEnabled('languages')) {
			$this->seedLanguages();
		}

		$this->command->getOutput()->progressFinish();

		$this->command->getOutput()->block('Seeding end');
	}

	/**
	 * @param  string  $module
	 * @return void
	 */
	private function initModule(string $module)
	{
		if (array_key_exists($module, $this->modules)) {
			// truncate module database table.
			app($this->modules[$module]['class'])->truncate();
			// import json data.
			$moduleSourcePath = __DIR__ . '/../../resources/json/' . $module . '.json';

			if (File::exists($moduleSourcePath)) {
				$this->modules[$module]['data'] = json_decode(File::get($moduleSourcePath), true);
			}
		}
	}

	/**
	 * @param  string  $module
	 * @return bool
	 */
	private function isModuleEnabled(string $module): bool
	{
		return $this->modules[$module]['enabled'];
	}

	/**
	 * @return void
	 */
	private function initCountries(): void
	{
		app(Models\Country::class)->truncate();
		$this->countries['data'] = json_decode(File::get(__DIR__ . '/../../resources/json/countries.json'), true);
	}

	/**
	 * @param  Models\Country  $country
	 * @param array $countryArray
	 */
	private function seedStates(Models\Country $country, array $countryArray): void
	{
		// country states and cities
		$countryStates = Arr::where($this->modules['states']['data'], fn ($state) => $state['country_id'] === $countryArray['id']);

		foreach ($countryStates as $state) {
			$stateInstance = Models\State::create([
				'country_id' => $country->id,
				'name' => trim((string) $state['name']),
			]);
			// state cities
			if ($this->isModuleEnabled('cities')) {
				$countryCities = Arr::where($this->modules['cities']['data'], fn ($city) => $city['country_id'] === $countryArray['id']);
				$stateCities = Arr::where($countryCities, fn ($city) => $city['state_id'] === $state['id']);
				$this->seedCities($country, $stateInstance, $stateCities);
			}
		}
	}

	/**
	 * @param  Models\Country  $country
	 * @param  Models\State  $state
	 * @param  array  $stateCities
	 */
	private function seedCities(Models\Country $country, Models\State $state, array $stateCities): void
	{
		foreach ($stateCities as $city) {
			Models\City::create([
				'country_id' => $country->id,
				'state_id' => $state->id,
				'name' => trim((string) $city['name']),
			]);
		}
	}

	/**
	 * @param  Models\Country  $country
	 * @param $countryArray
	 * @return void
	 */
	private function seedTimezones(Models\Country $country, $countryArray): void
	{
		foreach ($countryArray['timezones'] as $timezone) {
			$country
				->timezones()
				->create([
					'name' => (string) $timezone['zoneName'],
				]);
		}
	}

	private function seedCurrencies(Models\Country $country, array $countryArray): void
	{
		// currencies
		$exists = in_array($countryArray['currency'], array_keys($this->modules['currencies']['data']), true);
		$currency = $exists
			? $this->modules['currencies']['data'][$countryArray['currency']]
			: [
				'name' => (string) $countryArray['currency'],
				'code' => (string) $countryArray['currency'],
				'symbol' => (string) $countryArray['currency_symbol'],
				'symbol_native' => (string) $countryArray['currency_symbol'],
				'decimal_digits' => 2,
			];
		$country
			->currency()
			->create([
				'name' => (string) $currency['name'],
				'code' => (string) $currency['code'],
				'symbol' => (string) $currency['symbol'],
				'symbol_native' => (string) $currency['symbol_native'],
				'precision' => (int) $currency['decimal_digits'],
			]);
	}

	/**
	 * @return void
	 */
	private function seedLanguages(): void
	{
		// languages
		foreach ($this->modules['languages']['data'] as $language) {
			Models\Language::create($language);
		}
	}
}
