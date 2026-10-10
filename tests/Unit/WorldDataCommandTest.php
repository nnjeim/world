<?php

namespace Nnjeim\World\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Nnjeim\World\Data\WorldDataset;
use Nnjeim\World\Tests\TestCase;
use Nnjeim\World\WorldServiceProvider;

class WorldDataCommandTest extends TestCase
{
    public function test_install_retains_published_configuration_and_is_repeatable(): void
    {
        $directory = sys_get_temp_dir() . '/world-command-' . bin2hex(random_bytes(8));
        File::makeDirectory($directory . '/config', 0755, true);
        File::makeDirectory($directory . '/database/seeders', 0755, true);
        $original = "<?php return ['allowed_countries' => ['FR'], 'custom' => 'keep'];\n";
        File::put($directory . '/config/world.php', $original);
        app()->setBasePath($directory);
        (new WorldServiceProvider(app()))->boot();
        app()->instance(WorldDataset::class, new WorldDataset(dirname(__DIR__) . '/Fixtures/world'));
        config(['world.allowed_countries' => ['FR']]);
        try {
            $this->artisan('world:install')->assertExitCode(0);
            $this->artisan('world:install')->assertExitCode(0);
            self::assertSame($original, File::get($directory . '/config/world.php'));
            self::assertSame(['FR'], config('world.allowed_countries'));
            self::assertSame(1, DB::table('cities')->count());
            self::assertSame(182, DB::table('countries')->where('iso2', 'FR')->value('id'));
        } finally {
            File::deleteDirectory($directory);
        }
    }

    public function test_refresh_supports_production_with_force_and_keeps_existing_ids(): void
    {
        app()->instance('env', 'production');
        app()->instance(WorldDataset::class, new WorldDataset(dirname(__DIR__) . '/Fixtures/world'));
        $this->artisan('world:refresh', ['--force' => true])->assertExitCode(0);
        self::assertSame(1, DB::table('cities')->where('name', 'Paris')->value('id'));
        self::assertSame(182, DB::table('countries')->where('iso2', 'FR')->value('id'));
    }

    public function test_refresh_adopts_a_legacy_world_connection_with_migration_history_elsewhere(): void
    {
        config(['database.connections.application' => config('database.connections.sqlite')]);
        DB::connection('application')->getSchemaBuilder()->create('countries', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        // Legacy tables are on World, but their migrations were logged on the app connection.
        DB::connection('sqlite')->table('migrations')->delete();
        DB::connection('sqlite')->getSchemaBuilder()->drop('world_data_records');
        config(['database.default' => 'application']);
        app()->instance(WorldDataset::class, new WorldDataset(dirname(__DIR__) . '/Fixtures/world'));
        $this->artisan('world:refresh')->assertExitCode(0);
        self::assertSame(0, DB::connection('application')->table('countries')->count());
        self::assertSame(182, DB::connection('sqlite')->table('countries')->where('iso2', 'FR')->value('id'));
        self::assertSame(2, DB::connection('sqlite')->table('cities')->count());
        self::assertTrue(DB::connection('sqlite')->getSchemaBuilder()->hasTable('world_data_records'));

        $tracking = DB::connection('sqlite')->table('world_data_records')->orderBy('id')->get()->toArray();
        $this->artisan('migrate:rollback', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        $this->assertWorldTablesRetained('sqlite');
        self::assertSame(182, DB::connection('sqlite')->table('countries')->where('iso2', 'FR')->value('id'));
        self::assertSame(1, DB::connection('sqlite')->table('cities')->where('name', 'Paris')->value('id'));
        self::assertEquals($tracking, DB::connection('sqlite')->table('world_data_records')->orderBy('id')->get()->toArray());
        $this->artisan('world:refresh')->assertExitCode(0);
        self::assertSame(2, DB::connection('sqlite')->table('cities')->count());
    }

    public function test_command_dry_run_works_before_tracking_migration_and_does_not_publish_files(): void
    {
        DB::statement('DROP TABLE world_data_records');
        app()->instance(WorldDataset::class, new WorldDataset(dirname(__DIR__) . '/Fixtures/world'));
        $this->artisan('world:install', ['--dry-run' => true])->assertExitCode(0);
        self::assertFalse(DB::getSchemaBuilder()->hasTable('world_data_records'));
        self::assertSame(1, DB::table('cities')->count());
    }

    public function test_fresh_world_migration_rollback_retains_data_and_local_edit_tracking(): void
    {
        app()->instance(WorldDataset::class, new WorldDataset(dirname(__DIR__) . '/Fixtures/world'));
        $this->artisan('world:refresh')->assertExitCode(0);
        DB::table('cities')->where('id', 1)->update(['name' => 'My Paris']);
        $tracking = DB::table('world_data_records')->orderBy('id')->get()->toArray();

        $this->artisan('migrate:rollback', ['--database' => 'sqlite', '--force' => true])->assertExitCode(0);
        $this->assertWorldTablesRetained('sqlite');
        self::assertEquals($tracking, DB::table('world_data_records')->orderBy('id')->get()->toArray());
        self::assertSame('My Paris', DB::table('cities')->where('id', 1)->value('name'));

        $this->artisan('world:refresh')->assertExitCode(0);
        self::assertSame('My Paris', DB::table('cities')->where('id', 1)->value('name'));
        self::assertSame(2, DB::table('cities')->count());
    }

    private function assertWorldTablesRetained(string $connection): void
    {
        foreach (['countries', 'states', 'cities', 'timezones', 'currencies', 'languages', 'world_data_records'] as $table) {
            self::assertTrue(DB::connection($connection)->getSchemaBuilder()->hasTable($table), $table . ' must survive rollback');
        }
    }
}
