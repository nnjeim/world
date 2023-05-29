<?php

namespace Nnjeim\World\Actions;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Nnjeim\World\Models;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

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
		foreach (config('world.modules') as $module => $enabled) {
			if ($enabled) {
				$this->modules[$module]['enabled'] = true;
				$this->initModule($module);
			}
		}
	}

	public function run(): void
	{
		$this->command->getOutput()->block('Seeding start');

		$this->command->getOutput()->progressStart(count($this->countries['data']));

		// country schema
		$countryFields = Schema::getColumnListing(config('world.migrations.countries.table_name'));

		$this->forgetFields($countryFields, ['id']);

		foreach (array_chunk($this->countries['data'], 20) as $countryChunks) {

			foreach ($countryChunks as $countryArray) {

				$countryArray = array_map(fn ($field) => gettype($field) === 'string' ? trim($field) : $field, $countryArray);

				$country = Models\Country::create(Arr::only($countryArray, $countryFields));
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
			Schema::disableForeignKeyConstraints();
			app($this->modules[$module]['class'])->truncate();
			Schema::enableForeignKeyConstraints();
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

		if (! empty(config('world.allowed_countries')))
			$this->countries['data'] = Arr::where($this->countries['data'], function ($value, $key) {
				return in_array($value['iso2'], config('world.allowed_countries'));
			});

		if (! empty(config('world.disallowed_countries')))
			$this->countries['data'] = Arr::where($this->countries['data'], function ($value, $key) {
				return !in_array($value['iso2'], config('world.disallowed_countries'));
			});
	}

	/**
	 * @param  Models\Country  $country
	 * @param array $countryArray
	 */
	private function seedStates(Models\Country $country, array $countryArray): void
	{
		// country states and cities
		$countryStates = Arr::where($this->modules['states']['data'], fn ($state) => $state['country_id'] === $countryArray['id']);
		// state schema
		$stateFields = Schema::getColumnListing(config('world.migrations.states.table_name'));

		$this->forgetFields($stateFields, ['id', 'country_id']);

		$bulk_states = [];

        foreach ($countryStates as $stateArray) {

            $stateArray = array_map(fn ($field) => gettype($field) === 'string' ? trim($field) : $field, $stateArray);

            $bulk_states[] = Arr::add(
                Arr::only($stateArray, $stateFields),
                'country_id',
                $country->id
            );
        }

        DB::beginTransaction();

        try {
            $last_state_id_before_insert = $this->findLastStateIdBeforeInsert();

            Models\State::query()
                ->insert($bulk_states);

            $bulk_states = $this->addStateIdAfterInsert($bulk_states, $last_state_id_before_insert);

            //state cities
            if ($this->isModuleEnabled('cities')) {
                $stateNames = array_column($bulk_states, 'name');

                $stateCities = Arr::where(
                    $this->modules['cities']['data'],
                    fn ($city) => $city['country_id'] === $countryArray['id'] && in_array($city['state_name'], $stateNames, true)
                );

                $this->seedCities($country, $bulk_states, $stateCities);
            }
        } catch (Exception $exception){
            throw $exception;
        } finally {
            DB::commit();
        }
	}

	/**
	 * @param  Models\Country  $country
	 * @param  array  $states
	 * @param  array  $cities
	 */
	private function seedCities(Models\Country $country, array $states, array $cities): void
	{
		// city schema
		$cityFields = Schema::getColumnListing(config('world.migrations.cities.table_name'));

		$this->forgetFields($cityFields, ['id', 'country_id', 'state_id']);

		//using array_chunk to prevent mySQL too many placeholders error
        foreach (array_chunk($cities, 500) as $cityChunks) {
            $cities_bulk = [];
            foreach ($cityChunks as $cityArray) {
                $cityArray = array_map(fn ($field) => gettype($field) === 'string' ? trim($field) : $field, $cityArray);

                $city = Arr::only($cityArray, $cityFields);

                $state = Arr::first($states, fn($state) => $state['name'] === $cityArray['state_name']);

                $city = Arr::add(
                    $city,
                    'state_id',
                    $state['id']
                );

                $city = Arr::add(
                    $city,
                    'country_id',
                    $country->id
                );

                $cities_bulk[] = $city;
            }

            Models\City::query()
                ->insert($cities_bulk);
        }
	}

	/**
	 * @param  Models\Country  $country
	 * @param $countryArray
	 * @return void
	 */
	private function seedTimezones(Models\Country $country, $countryArray): void
	{
	    $bulk_timezones = [];

		foreach ($countryArray['timezones'] as $timezone) {
		    $bulk_timezones[] = [
		        'country_id' => $country->id,
                'name' => (string) $timezone['zoneName']
            ];
		}

		Models\Timezone::query()
            ->insert($bulk_timezones);
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

	/**
	 * @param  array  $array
	 * @param  array  $values
	 * @return void
	 */
	private function forgetFields(array &$array, array $values)
	{
		foreach ($values as $value) {
			if (($key = array_search($value, $array)) !== false) {
				unset($array[$key]);
			}
		}
	}

    private function findLastStateIdBeforeInsert()
    {
        $state = Models\State::query()->orderByDesc('id')->first();

        $last_state_id_before_insert = 0;

        if (!is_null($state)) {
            $last_state_id_before_insert = $state->id;
        }

        return $last_state_id_before_insert;
    }

    private function addStateIdAfterInsert(array $bulk_states, $last_state_id_before_insert)
    {
        $count = count($bulk_states);

        for ($i = 1; $i <= $count; $i++) {
            $bulk_states[$i - 1]['id'] = $last_state_id_before_insert + $i;
        }
        return $bulk_states;
    }
}
