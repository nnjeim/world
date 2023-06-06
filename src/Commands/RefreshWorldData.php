<?php

namespace Nnjeim\World\Commands;

use DirectoryIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Nnjeim\World\Actions\SeedAction;

class RefreshWorldData extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'world:refresh';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Refresh the world data';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		// check if we are in production mode
		if (app()->environment() === 'production') {
			$this->error('You are in production mode. This command is not allowed in production mode.');
			return;
		}
		// drop the world tables
		$worldTables = [
			'world.migrations.countries.table_name',
			'world.migrations.states.table_name',
			'world.migrations.cities.table_name',
			'world.migrations.timezones.table_name',
			'world.migrations.currencies.table_name',
			'world.migrations.languages.table_name',
		];
		// drop a table if it exists
		foreach ($worldTables as $worldTable) {
			Schema::dropIfExists(config($worldTable));
		}
		// delete the world entries in the migrations table
		// get a list of the world migration files
		$migrationsPath = __DIR__ . '/../Database/Migrations';
		$migrationsFiles = new DirectoryIterator($migrationsPath);

		foreach ($migrationsFiles as $migrationsFile) {
			if ($migrationsFile->getExtension() !== 'php') {
				continue;
			}
			$migrationFileName = $migrationsFile->getFilename();
			$migration = Str::before($migrationFileName, '.php');
			DB::table('migrations')
				->where('migration', $migration)
				->delete();
		}
		// migrate new tables
		Artisan::call('migrate');
		// re-seed the world data
		Artisan::call('db:seed --class=WorldSeeder', array(), $this->getOutput());
	}
}
