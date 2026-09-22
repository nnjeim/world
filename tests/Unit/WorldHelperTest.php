<?php

namespace Nnjeim\World\Tests\Unit;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Nnjeim\World\Tests\TestCase;
use Nnjeim\World\WorldHelper;

class WorldHelperTest extends TestCase
{
	public function test_set_locale_changes_the_runtime_locale(): void
	{
		Cache::flush();

		$action = $this->world()
			->setLocale('hu')
			->countries();

		self::assertSame('hu', app()->getLocale());
		self::assertSame(trans('world::country.FR'), $action->data->firstWhere('id', 182)['name']);
		self::assertTrue(Cache::has('countries_id_name_hu'));
		self::assertFalse(Cache::has('countries_id_name_en'));
	}

	public function test_set_locale_uses_the_fallback_for_an_unsupported_locale(): void
	{
		app()->setLocale('hu');

		$this->world()->setLocale('unsupported');

		self::assertSame('en', app()->getLocale());
	}

	public function test_cached_results_are_stored_as_arrays_and_returned_as_collections(): void
	{
		Cache::flush();

		$action = $this->world()->countries();

		self::assertInstanceOf(Collection::class, $action->data);
		self::assertIsArray(Cache::get('countries_id_name_en'));
	}

	public function test_without_caching_only_overrides_the_next_action(): void
	{
		Cache::flush();
		self::assertCount(2, $this->world()->countries()->data);

		$this->insertCountry(184, 'DE', 'DEU', 'Germany');

		self::assertCount(2, $this->world()->countries()->data);
		self::assertCount(3, $this->world()->withoutCaching()->countries()->data);
		self::assertCount(2, $this->world()->countries()->data);
	}

	public function test_global_cache_disable_applies_to_every_action_call(): void
	{
		config()->set('world.cache.enabled', false);
		Cache::flush();

		self::assertCount(2, $this->world()->countries()->data);
		$this->insertCountry(184, 'DE', 'DEU', 'Germany');

		self::assertCount(3, $this->world()->countries()->data);
		self::assertFalse(Cache::has('countries_id_name_en'));
	}

	public function test_with_caching_only_overrides_the_next_action(): void
	{
		config()->set('world.cache.enabled', false);
		Cache::flush();

		self::assertCount(2, $this->world()->withCaching()->countries()->data);
		self::assertTrue(Cache::has('countries_id_name_en'));

		$this->insertCountry(184, 'DE', 'DEU', 'Germany');

		self::assertCount(3, $this->world()->countries()->data);
	}

	private function world(): WorldHelper
	{
		return app('world');
	}

	private function insertCountry(int $id, string $iso2, string $iso3, string $name): void
	{
		DB::table(config('world.migrations.countries.table_name'))->insert([
			'id' => $id,
			'iso2' => $iso2,
			'iso3' => $iso3,
			'name' => $name,
			'phone_code' => '00',
			'region' => 'Europe',
			'subregion' => 'Western Europe',
			'status' => 1,
		]);
	}
}
