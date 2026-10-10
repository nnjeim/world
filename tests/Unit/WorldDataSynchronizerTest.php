<?php

namespace Nnjeim\World\Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Nnjeim\World\Data\WorldDataCache;
use Nnjeim\World\Data\WorldDataset;
use Nnjeim\World\Data\WorldDataSynchronizer;
use Nnjeim\World\Tests\TestCase;
use Nnjeim\World\World;
use RuntimeException;

class WorldDataSynchronizerTest extends TestCase
{
    private function dataset(): WorldDataset
    {
        return new WorldDataset(dirname(__DIR__) . '/Fixtures/world');
    }

    public function test_updates_are_repeatable_and_preserve_ids_foreign_keys_and_custom_columns(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('custom_note')->nullable();
            $table->string('native')->nullable();
        });
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('city_id')->constrained('cities');
        });
        DB::table('countries')->where('id', 182)->update(['status' => 0, 'custom_note' => 'Keep this', 'native' => 'My native label']);
        DB::table('addresses')->insert(['country_id' => 182, 'city_id' => 1]);
        $sync = app(WorldDataSynchronizer::class);
        $dataset = $this->dataset();
        $dataset->countries[0]['native'] = 'Dataset native label';
        $first = $sync->sync($dataset);
        $second = $sync->sync($this->dataset());
        self::assertSame(0, $first['countries']['created']);
        self::assertSame(1, $first['cities']['created']);
        foreach ($second as $counts) {
            self::assertSame(0, $counts['created']);
            self::assertSame(0, $counts['updated']);
        }
        self::assertSame(182, DB::table('countries')->where('iso2', 'FR')->value('id'));
        self::assertSame(1, DB::table('cities')->where('name', 'Paris')->value('id'));
        self::assertSame(1, DB::table('addresses')->count());
        self::assertSame('Keep this', DB::table('countries')->where('id', 182)->value('custom_note'));
        self::assertSame(0, DB::table('countries')->where('id', 182)->value('status'));
        self::assertSame('My native label', DB::table('countries')->where('id', 182)->value('native'));
        self::assertSame(2, DB::table('countries')->count());
    }

    public function test_tracked_source_renames_and_city_moves_keep_application_ids(): void
    {
        $dataset = $this->dataset();
        $sync = app(WorldDataSynchronizer::class);
        $sync->sync($dataset);
        $dataset->states[] = array_replace($dataset->states[0], ['id' => 503, 'name' => 'New District', 'state_code' => 'NEW']);
        $dataset->cities[0] = array_replace($dataset->cities[0], ['name' => 'Paris Updated', 'state_id' => 503, 'state_name' => 'New District', 'state_code' => 'NEW']);
        $sync->sync($dataset);
        $city = DB::table('cities')->where('id', 1)->first();
        self::assertSame('Paris Updated', $city->name);
        self::assertSame(DB::table('states')->where('name', 'New District')->value('id'), $city->state_id);
        self::assertSame(2, DB::table('cities')->count());
    }

    public function test_local_edits_are_preserved_after_the_first_adoption(): void
    {
        $dataset = $this->dataset();
        $sync = app(WorldDataSynchronizer::class);
        $sync->sync($dataset);
        DB::table('cities')->where('id', 1)->update(['name' => 'My Paris']);
        $dataset->cities[0]['name'] = 'Paris Updated';
        $summary = $sync->sync($dataset);
        self::assertSame('My Paris', DB::table('cities')->where('id', 1)->value('name'));
        self::assertSame(1, $summary['cities']['preserved']);
    }

    public function test_removed_source_rows_disabled_modules_and_excluded_countries_are_retained(): void
    {
        $dataset = $this->dataset();
        app(WorldDataSynchronizer::class)->sync($dataset);
        $dataset->cities = [];
        config(['world.allowed_countries' => ['FR'], 'world.modules.languages' => false]);
        app(WorldDataSynchronizer::class)->sync($dataset);
        self::assertSame(2, DB::table('cities')->count());
        self::assertSame(2, DB::table('countries')->count());
        self::assertSame(2, DB::table('languages')->count());
    }

    public function test_dry_run_does_not_write_tracking_or_change_cache(): void
    {
        $prefix = WorldDataCache::prefix();
        $summary = app(WorldDataSynchronizer::class)->sync($this->dataset(), true);
        self::assertSame(1, $summary['cities']['created']);
        self::assertSame(1, DB::table('cities')->count());
        self::assertSame(0, DB::table('world_data_records')->count());
        self::assertSame($prefix, WorldDataCache::prefix());
    }

    public function test_country_only_import_does_not_load_the_large_city_dataset(): void
    {
        config(['world.modules.cities' => false]);
        $dataset = app(WorldDataset::class);
        self::assertSame([], $dataset->cities);
        self::assertNotEmpty($dataset->countries);
    }

    public function test_preview_on_a_fresh_database_does_not_create_world_tables(): void
    {
        foreach (['cities', 'states', 'timezones', 'currencies', 'languages', 'countries', 'world_data_records'] as $table) {
            Schema::drop($table);
        }
        $summary = app(WorldDataSynchronizer::class)->sync($this->dataset(), true);
        self::assertSame(2, $summary['countries']['created']);
        self::assertSame(2, $summary['cities']['created']);
        self::assertFalse(Schema::hasTable('countries'));
        self::assertFalse(Schema::hasTable('world_data_records'));
    }

    public function test_local_relationship_edits_keep_the_associated_codes_together(): void
    {
        $dataset = $this->dataset();
        $sync = app(WorldDataSynchronizer::class);
        $sync->sync($dataset);
        $romanianState = DB::table('states')->where('name', 'Bucharest')->value('id');
        DB::table('cities')->where('id', 1)->update(['country_id' => 183, 'state_id' => $romanianState, 'country_code' => 'RO']);
        $dataset->states[] = array_replace($dataset->states[0], ['id' => 503, 'name' => 'New District', 'state_code' => 'NEW']);
        $dataset->cities[0] = array_replace($dataset->cities[0], ['state_id' => 503, 'state_name' => 'New District', 'state_code' => 'NEW']);
        $sync->sync($dataset);
        $city = DB::table('cities')->where('id', 1)->first();
        self::assertSame(183, $city->country_id);
        self::assertSame($romanianState, $city->state_id);
        self::assertSame('RO', $city->country_code);
    }

    public function test_failure_rolls_back_all_modules_and_releases_the_update_lock(): void
    {
        $dataset = $this->dataset();
        $dataset->countries[0]['phone_code'] = '123';
        DB::unprepared("CREATE TRIGGER fail_new_city BEFORE INSERT ON cities BEGIN SELECT RAISE(ABORT, 'test failure'); END");
        try {
            app(WorldDataSynchronizer::class)->sync($dataset);
            self::fail('Expected the city insert to fail.');
        } catch (\Illuminate\Database\QueryException $exception) {
            self::assertStringContainsString('test failure', $exception->getMessage());
        }
        self::assertSame('33', DB::table('countries')->where('id', 182)->value('phone_code'));
        self::assertSame(1, DB::table('cities')->count());
        self::assertSame(0, DB::table('world_data_records')->count());
        DB::unprepared('DROP TRIGGER fail_new_city');
        app(WorldDataSynchronizer::class)->sync($dataset);
        self::assertSame(2, DB::table('cities')->count());
    }

    public function test_updates_invalidate_world_cache_without_flushing_application_cache(): void
    {
        Cache::put('application-data', 'keep');
        self::assertCount(2, World::countries()->data);
        DB::table('countries')->where('iso2', 'RO')->delete();
        app(WorldDataSynchronizer::class)->sync($this->dataset());
        $romania = World::countries(['fields' => 'phone_code', 'filters' => ['iso2' => 'RO']])->data->first();
        self::assertSame('40', $romania['phone_code']);
        self::assertSame('keep', Cache::get('application-data'));
    }

    public function test_invalid_dataset_is_rejected_before_any_writes(): void
    {
        $dataset = $this->dataset();
        $dataset->cities[0]['state_id'] = 999;
        $this->expectException(RuntimeException::class);
        try {
            app(WorldDataSynchronizer::class)->sync($dataset);
        } finally {
            self::assertSame(0, DB::table('world_data_records')->count());
        }
    }

    public function test_a_city_can_move_across_countries_without_changing_its_id(): void
    {
        $dataset = $this->dataset();
        $sync = app(WorldDataSynchronizer::class);
        $sync->sync($dataset);
        $dataset->cities[0] = array_replace($dataset->cities[0], [
            'country_id' => 181, 'country_name' => 'Romania', 'country_code' => 'RO',
            'state_id' => 502, 'state_name' => 'Bucharest', 'state_code' => 'B',
        ]);
        $sync->sync($dataset);
        self::assertSame(183, DB::table('cities')->where('id', 1)->value('country_id'));
        self::assertSame(2, DB::table('cities')->count());
    }

    public function test_full_bundled_data_can_be_installed_and_updated_twice(): void
    {
        // Start with empty World tables, as on a fresh installation.
        foreach (['cities', 'states', 'timezones', 'currencies', 'languages', 'countries'] as $table) {
            DB::table($table)->delete();
        }
        $dataset = new WorldDataset();
        $sync = app(WorldDataSynchronizer::class);
        $sync->sync($dataset);
        $parisId = DB::table('cities')->where('name', 'Paris')->orderBy('id')->value('id');
        $summary = $sync->sync($dataset);
        foreach ($summary as $counts) {
            self::assertSame(0, $counts['created']);
            self::assertSame(0, $counts['updated']);
        }
        self::assertSame(count($dataset->countries), DB::table('countries')->count());
        self::assertSame(count($dataset->states), DB::table('states')->count());
        self::assertSame(count($dataset->cities), DB::table('cities')->count());
        self::assertSame($parisId, DB::table('cities')->where('name', 'Paris')->orderBy('id')->value('id'));
        $cayman = DB::table('countries')->where('iso2', 'KY')->value('id');
        self::assertSame(6, DB::table('states')->where('country_id', $cayman)->count());
        self::assertSame(15, DB::table('cities')->where('country_id', $cayman)->count());
    }

    public function test_sync_uses_the_world_connection_when_the_application_default_differs(): void
    {
        config(['database.connections.application' => config('database.connections.sqlite')]);
        DB::connection('application')->getSchemaBuilder()->create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        config(['database.default' => 'application']);
        app(WorldDataSynchronizer::class)->sync($this->dataset());
        self::assertSame(0, DB::connection('application')->table('countries')->count());
        self::assertSame(2, DB::connection('sqlite')->table('cities')->count());
    }
}
