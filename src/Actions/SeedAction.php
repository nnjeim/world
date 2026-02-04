<?php

namespace Nnjeim\World\Actions;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Nnjeim\World\Models;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class SeedAction extends Seeder
{
	protected SchemaBuilder $schema;

	private array $countries = [
		'data' => [],
	];

	private array $modules = [
		'states' => [
			'data' => [],
			'enabled' => false,
		],
		'cities' => [
			'data' => [],
			'enabled' => false,
		],
		'timezones' => [
			'enabled' => false,
		],
		'currencies' => [
			'data' => [],
			'enabled' => false,
		],
		'languages' => [
			'data' => [],
			'enabled' => false,
		],
	];

	public function __construct()
	{
		foreach ($this->modules as $name => $data) {
			$this->modules[$name]['class'] = config('world.models.' . $name);
		}

		$this->schema = Schema::connection(config('world.connection'));

		//check memory limit
		$this->checkMemoryLimit();

		// countries
		$this->initCountries();

		// init modules (only seedable modules - skip geolocate, phone, etc.)
		foreach (config('world.modules') as $module => $enabled) {
			if ($enabled && array_key_exists($module, $this->modules)) {
				$this->modules[$module]['enabled'] = true;
				$this->initModule($module);
			}
		}
	}

	/**
	 * Check if the current memory limit is sufficient for seeding large datasets
	 */
	private function checkMemoryLimit(): void
	{
		$currentLimit = ini_get('memory_limit');
		$recommendedLimit = '512M';
		$currentBytes = $this->convertToBytes($currentLimit);
		$recommendedBytes = $this->convertToBytes($recommendedLimit);

		if ($currentBytes < $recommendedBytes && $currentLimit !== '-1') {
			$commandName = $this->getRunningCommand();
			$message = "Insufficient memory limit detected! Current: {$currentLimit}, Recommended: {$recommendedLimit}\n";

			if ($commandName === 'world:install') {
				$message .= "To fix this, run the command with increased memory:\n" .
					"php -d memory_limit={$recommendedLimit} artisan world:install";
			} elseif ($commandName === 'db:seed') {
				$message .= "To fix this, run the command with increased memory:\n" .
					"php -d memory_limit={$recommendedLimit} artisan db:seed --class=WorldSeeder";
			} else {
				// If we can't determine the command, show both options
				$message .= "To fix this, run either command with increased memory:\n" .
					"php -d memory_limit={$recommendedLimit} artisan world:install\n" .
					"php -d memory_limit={$recommendedLimit} artisan db:seed --class=WorldSeeder";
			}

			if ($this->command && method_exists($this->command, 'getOutput')) {
				$this->command->getOutput()->error($message);
			} else {
				fwrite(STDERR, $message . "\n");
			}
			exit(1);
		}
	}

	/**
	 * Determine which command is currently running
	 */
	private function getRunningCommand(): ?string
	{
		// First try to get the command from the $this->command object
		if ($this->command && method_exists($this->command, 'getName')) {
			return $this->command->getName();
		}

		// Fallback to checking the command line arguments
		if (isset($_SERVER['argv']) && is_array($_SERVER['argv']) && count($_SERVER['argv']) >= 2) {
			$command = $_SERVER['argv'][1];

			// Check if it's the db:seed command with the WorldSeeder class
			if ($command === 'db:seed' && isset($_SERVER['argv'][2]) && $_SERVER['argv'][2] === '--class=WorldSeeder') {
				return 'db:seed';
			}

			// Check if it's the world:install command
			if ($command === 'world:install') {
				return 'world:install';
			}

			// Return the command name if it's one of the expected ones
			if (in_array($command, ['db:seed', 'world:install'])) {
				return $command;
			}
		}

		return null;
	}

	/**
	 * Convert memory limit string to bytes
	 */
	private function convertToBytes(string $memoryLimit): int
	{
		if ($memoryLimit === '-1') {
			return PHP_INT_MAX;
		}

		$unit = strtolower(substr($memoryLimit, -1));
		$value = (int) substr($memoryLimit, 0, -1);

		switch ($unit) {
			case 'g':
				return $value * 1024 * 1024 * 1024;
			case 'm':
				return $value * 1024 * 1024;
			case 'k':
				return $value * 1024;
			default:
				return (int) $memoryLimit;
		}
	}

	public function run(): void
	{
		$this->command->getOutput()->block('Seeding start');

		$this->command->getOutput()->progressStart(count($this->countries['data']));

		// country schema
		$countryFields = $this->schema
			->getColumnListing(config('world.migrations.countries.table_name'));

		$this->forgetFields($countryFields, ['id']);

		foreach (array_chunk($this->countries['data'], 20) as $countryChunks) {

			foreach ($countryChunks as $countryArray) {

				$countryArray = array_map(fn($field) => gettype($field) === 'string' ? trim($field) : $field, $countryArray);

				$countryClass = config('world.models.countries');
				$country = $countryClass::create(Arr::only($countryArray, $countryFields));
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
	 * @param string $module
	 * @return void
	 */
	private function initModule(string $module)
	{
		if (array_key_exists($module, $this->modules)) {
			// truncate module database table.
			$this->schema->disableForeignKeyConstraints();
			app($this->modules[$module]['class'])->truncate();
			$this->schema->enableForeignKeyConstraints();
			// import json data.
			$moduleSourcePath = __DIR__ . '/../../resources/json/' . $module . '.json';

			if (File::exists($moduleSourcePath)) {
				$this->modules[$module]['data'] = json_decode(File::get($moduleSourcePath), true);
			}
		}
	}

	/**
	 * @param string $module
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
		$this->schema->disableForeignKeyConstraints();
		$countryClass = config('world.models.countries');
		app($countryClass)->truncate();
		$this->schema->enableForeignKeyConstraints();

		$this->countries['data'] = json_decode(File::get(__DIR__ . '/../../resources/json/countries.json'), true);

		if (!empty(config('world.allowed_countries')))
			$this->countries['data'] = Arr::where($this->countries['data'], function ($value, $key) {
				return in_array($value['iso2'], config('world.allowed_countries'));
			});

		if (!empty(config('world.disallowed_countries')))
			$this->countries['data'] = Arr::where($this->countries['data'], function ($value, $key) {
				return !in_array($value['iso2'], config('world.disallowed_countries'));
			});
	}

	/**
	 * @param Models\Country $country
	 * @param array $countryArray
	 *
	 * @throws Exception
	 */
	private function seedStates(Models\Country $country, array $countryArray): void
	{
		// country states and cities
		$countryStates = Arr::where($this->modules['states']['data'], fn($state) => $state['country_id'] === $countryArray['id']);
		// state schema
		$stateFields = $this->schema->getColumnListing(config('world.migrations.states.table_name'));

		$this->forgetFields($stateFields, ['id', 'country_id']);

		$bulk_states = [];

		foreach ($countryStates as $stateArray) {

			$stateArray = array_map(fn($field) => gettype($field) === 'string' ? trim($field) : $field, $stateArray);

			$bulk_states[] = Arr::add(
				Arr::only($stateArray, $stateFields),
				'country_id',
				$country->id
			);
		}

		DB::beginTransaction();

		try {
			$last_state_id_before_insert = $this->findLastStateIdBeforeInsert();

			$stateClass = config('world.models.states');
			$stateClass::query()
				->insert($bulk_states);

			$bulk_states = $this->addStateIdAfterInsert($bulk_states, $last_state_id_before_insert);

			//state cities
			if ($this->isModuleEnabled('cities')) {
				$stateNames = array_column($bulk_states, 'name');

				$stateCities = Arr::where(
					$this->modules['cities']['data'],
					fn($city) => $city['country_id'] === $countryArray['id'] && in_array($city['state_name'], $stateNames, true)
				);

				$this->seedCities($country, $bulk_states, $stateCities);
			}
		} catch (Exception $exception) {
			throw $exception;
		} finally {
			DB::commit();
		}
	}

	/**
	 * @param Models\Country $country
	 * @param array $states
	 * @param array $cities
	 */
	private function seedCities(Models\Country $country, array $states, array $cities): void
	{
		// city schema
		$cityFields = $this->schema->getColumnListing(config('world.migrations.cities.table_name'));

		$this->forgetFields($cityFields, ['id', 'country_id', 'state_id']);

		//using array_chunk to prevent mySQL too many placeholders error
		foreach (array_chunk($cities, 500) as $cityChunks) {
			$cities_bulk = [];
			foreach ($cityChunks as $cityArray) {
				$cityArray = array_map(fn($field) => gettype($field) === 'string' ? trim($field) : $field, $cityArray);

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

			$cityClass = config('world.models.cities');
			$cityClass::query()
				->insert($cities_bulk);
		}
	}

	/**
	 * @param Models\Country $country
	 * @param $countryArray
	 * @return void
	 */
	private function seedTimezones(Models\Country $country, $countryArray): void
	{
		$bulk_timezones = [];

		foreach ($countryArray['timezones'] as $timezone) {
			$bulk_timezones[] = [
				'country_id' => $country->id,
				'name' => (string)$timezone['zoneName']
			];
		}

		$timezoneClass = config('world.models.timezones');
		$timezoneClass::query()
			->insert($bulk_timezones);
	}

	private function seedCurrencies(Models\Country $country, array $countryArray): void
	{
		// currencies
		$exists = in_array($countryArray['currency'], array_keys($this->modules['currencies']['data']), true);
		$currency = $exists
			? $this->modules['currencies']['data'][$countryArray['currency']]
			: [
				'name' => (string)$countryArray['currency'],
				'code' => (string)$countryArray['currency'],
				'symbol' => (string)$countryArray['currency_symbol'],
				'symbol_native' => (string)$countryArray['currency_symbol'],
				'decimal_digits' => 2,
			];
		$country
			->currency()
			->create([
				'name' => (string)$currency['name'],
				'code' => (string)$currency['code'],
				'symbol' => (string)$currency['symbol'],
				'symbol_native' => (string)$currency['symbol_native'],
				'precision' => (int)$currency['decimal_digits'],
			]);
	}

	/**
	 * @return void
	 */
	private function seedLanguages(): void
	{
		// languages
		$languageClass = config('world.models.languages');
		$languageClass::query()
			->insert($this->modules['languages']['data']);
	}

	/**
	 * @param array $array
	 * @param array $values
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
		$stateClass = config('world.models.states');
		$state = $stateClass::query()->orderByDesc('id')->first();

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
