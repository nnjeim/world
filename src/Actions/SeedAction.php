<?php

namespace Nnjeim\World\Actions;

use Nnjeim\World\Models;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;

class SeedAction
{
	private array $data = [
		'countries' => [],
		'states' => [],
		'cities' => [],
		'currencies' => [],
	];

	public function __construct()
	{
		/*--- truncate tables --*/
		Models\Country::truncate();
		Models\City::truncate();
		Models\Timezone::truncate();
		Models\State::truncate();
		Models\Currency::truncate();

		/*-- data source --*/
		$this->data['countries'] = json_decode(File::get(__DIR__ . '/../../resources/json/countries.json'), true);
		$this->data['states'] = json_decode(File::get(__DIR__ . '/../../resources/json/states.json'), true);
		$this->data['cities'] = json_decode(File::get(__DIR__ . '/../../resources/json/cities.json'), true);
		$this->data['currencies'] = json_decode(File::get(__DIR__ . '/../../resources/json/currencies.json'), true);
	}

	public function execute(): void
	{
		/*-- seed countries and timezones --*/
		$countryFillables = (new Models\Country())->getFillable();
		foreach ($this->data['countries'] as $country) {
			$transform = function($country) use ($countryFillables) {
				$return = [];
				foreach (array_keys($country) as $key) {
					if ($key === 'subregion') {
						$return['sub_region'] = trim((string) $country[$key]);
						continue;
					}

					if (in_array($key, $countryFillables)) {
						$return[$key] = trim((string) $country[$key]);
					}
				}
				return $return;
			};

			$countryInstance = Models\Country::create($transform($country));
			/*-- country states and cities --*/
			$countryStates = Arr::where($this->data['states'], fn($state) => $state['country_id'] === $country['id']);
			$countryCities = Arr::where($this->data['cities'], fn($city) => $city['country_id'] === $country['id']);

			/*-- states and cities --*/
			$this->seedStates($countryInstance, $countryStates, $countryCities);

			/*-- timezones --*/
			foreach ($country['timezones'] as $timezone) {
				$countryInstance
					->timezones()
					->create([
						'name' => (string) $timezone['zoneName']
					]);
			}

			/*-- currencies --*/
			$exists = in_array($country['currency'], array_keys($this->data['currencies']), true);
			$currency = $exists
				? $this->data['currencies'][$country['currency']]
				: [
					'name' => (string) $country['currency'],
					'code' => (string) $country['currency'],
					'symbol' => (string) $country['currency_symbol'],
					'symbol_native' => (string) $country['currency_symbol'],
					'decimal_digits' => 2,
				];
			$countryInstance
				->currency()
				->create([
					'name' => (string) $currency['name'],
					'code' => (string) $currency['code'],
					'symbol' => (string) $currency['symbol'],
					'symbol_native' => (string) $currency['symbol_native'],
					'precision' => (int) $currency['decimal_digits'],
				]);
		}
	}

	/**
	 * @param  Models\Country  $country
	 * @param $countryStates
	 * @param $countryCities
	 */
	private function seedStates(Models\Country $country, $countryStates, $countryCities): void
	{
		foreach ($countryStates as $state) {

			$stateInstance = Models\State::create([
				'country_id' => $country->id,
				'name' => trim((string) $state['name']),
			]);
			/*-- state cities --*/
			$stateCities = Arr::where($countryCities, fn($city) => $city['state_id'] === $state['id']);
			$this->seedCities($country, $stateInstance, $stateCities);
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
}
