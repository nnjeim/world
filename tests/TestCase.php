<?php

namespace Nnjeim\World\Tests;

use Illuminate\Support\Facades\DB;
use Nnjeim\World\WorldServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
	protected function getPackageProviders($app): array
	{
		return [WorldServiceProvider::class];
	}

	protected function defineEnvironment($app): void
	{
		$app['config']->set('app.locale', 'en');
		$app['config']->set('app.fallback_locale', 'en');
		$app['config']->set('cache.default', 'array');
		$app['config']->set('cache.stores.array', [
			'driver' => 'array',
			'serialize' => false,
		]);
		$app['config']->set('database.default', 'sqlite');
		$app['config']->set('database.connections.sqlite', [
			'driver' => 'sqlite',
			'database' => ':memory:',
			'prefix' => '',
			'foreign_key_constraints' => true,
		]);
		$app['config']->set('world.connection', 'sqlite');
		$app['config']->set('world.routes', false);
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->artisan('migrate', [
			'--database' => 'sqlite',
			'--force' => true,
		])->run();

		$this->seedFixtures();
	}

	private function seedFixtures(): void
	{
		DB::table(config('world.migrations.countries.table_name'))->insert([
			[
				'id' => 182,
				'iso2' => 'FR',
				'iso3' => 'FRA',
				'name' => 'France',
				'phone_code' => '33',
				'region' => 'Europe',
				'subregion' => 'Western Europe',
				'status' => 1,
			],
			[
				'id' => 183,
				'iso2' => 'RO',
				'iso3' => 'ROU',
				'name' => 'Romania',
				'phone_code' => '40',
				'region' => 'Europe',
				'subregion' => 'Eastern Europe',
				'status' => 1,
			],
		]);

		DB::table(config('world.migrations.states.table_name'))->insert([
			'id' => 1,
			'country_id' => 182,
			'country_code' => 'FR',
			'name' => 'Île-de-France',
		]);

		DB::table(config('world.migrations.cities.table_name'))->insert([
			'id' => 1,
			'country_id' => 182,
			'state_id' => 1,
			'country_code' => 'FR',
			'name' => 'Paris',
		]);

		DB::table(config('world.migrations.timezones.table_name'))->insert([
			'id' => 1,
			'country_id' => 182,
			'name' => 'Europe/Paris',
		]);

		DB::table(config('world.migrations.currencies.table_name'))->insert([
			'id' => 1,
			'country_id' => 182,
			'name' => 'Euro',
			'code' => 'EUR',
			'precision' => 2,
			'symbol' => '€',
			'symbol_native' => '€',
			'symbol_first' => 1,
			'decimal_mark' => '.',
			'thousands_separator' => ',',
		]);

		DB::table(config('world.migrations.languages.table_name'))->insert([
			'id' => 1,
			'code' => 'fr',
			'name' => 'French',
			'name_native' => 'Français',
			'dir' => 'ltr',
		]);
	}
}
