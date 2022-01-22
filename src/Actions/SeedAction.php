<?php

namespace Nnjeim\World\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Nnjeim\World\Models;

class SeedAction
{
	private array $data = [
		'countries' => [],
		'states' => [],
		'cities' => [],
		'currencies' => [],
		'languages' => [],
	];

	public function __construct()
	{
		// countries
		$this->initCountries();
		// states
		if (config('world.modules.states')) {
			$this->initStates();
		}
		// cities
		if (config('world.modules.cities')) {
			$this->initCities();
		}
		// timezones
		if (config('world.modules.timezones')) {
			$this->initTimezones();
		}
		// currencies
		if (config('world.modules.currencies')) {
			$this->initCurrencies();
		}
		// languages
		if (config('world.modules.languages')) {
			$this->initLanguages();
		}
	}

	public function execute(): void
	{
		$countryFillables = (new Models\Country())->getFillable();

		foreach ($this->data['countries'] as $countryArray) {
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

			$country = Models\Country::create($transform($countryArray));
			// states and cities
			if (config('world.modules.states')) {
				$this->seedStates($country, $countryArray);
			}
			// timezones
			if (config('world.modules.timezones')) {
				$this->seedTimezones($country, $countryArray);
			}
			// currencies
			if (config('world.modules.currencies')) {
				$this->seedCurrencies($country, $countryArray);
			}
		}

		// languages
		if (config('world.modules.languages')) {
			$this->seedLanguages();
		}
	}

	/**
	 * @return void
	 */
	private function initCountries(): void
	{
		Models\Country::truncate();
		$this->data['countries'] = json_decode(File::get(__DIR__ . '/../../resources/json/countries.json'), true);
	}

	/**
	 * @return void
	 */
	private function initStates(): void
	{
		Models\State::truncate();
		$this->data['states'] = json_decode(File::get(__DIR__ . '/../../resources/json/states.json'), true);
	}

	/**
	 * @param  Models\Country  $country
	 * @param array $countryArray
	 */
	private function seedStates(Models\Country $country, array $countryArray): void
	{
		// country states and cities
		$countryStates = Arr::where($this->data['states'], fn ($state) => $state['country_id'] === $countryArray['id']);

		foreach ($countryStates as $state) {
			$stateInstance = Models\State::create([
				'country_id' => $country->id,
				'name' => trim((string) $state['name']),
			]);
			// state cities
			if (config('world.modules.cities')) {
				$countryCities = Arr::where($this->data['cities'], fn ($city) => $city['country_id'] === $countryArray['id']);
				$stateCities = Arr::where($countryCities, fn ($city) => $city['state_id'] === $state['id']);
				$this->seedCities($country, $stateInstance, $stateCities);
			}
		}
	}

	/**
	 * @return void
	 */
	private function initCities(): void
	{
		Models\City::truncate();
		$this->data['cities'] = json_decode(File::get(__DIR__ . '/../../resources/json/cities.json'), true);
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
	 * @return void
	 */
	private function initTimezones(): void
	{
		Models\Timezone::truncate();
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

	/**
	 * @return void
	 */
	private function initCurrencies(): void
	{
		Models\Currency::truncate();
		$this->data['currencies'] = json_decode(File::get(__DIR__ . '/../../resources/json/currencies.json'), true);
	}

	private function seedCurrencies(Models\Country $country, array $countryArray): void
	{
		// currencies
		$exists = in_array($countryArray['currency'], array_keys($this->data['currencies']), true);
		$currency = $exists
			? $this->data['currencies'][$countryArray['currency']]
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
	private function initLanguages(): void
	{
		Models\Language::truncate();
		$this->data['languages'] = json_decode(File::get(__DIR__ . '/../../resources/json/languages.json'), true);
	}

	/**
	 * @return void
	 */
	private function seedLanguages(): void
	{
		// languages
		foreach ($this->data['languages'] as $language) {
			Models\Language::create($language);
		}
	}
}
