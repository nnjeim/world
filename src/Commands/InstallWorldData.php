<?php

namespace Nnjeim\World\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallWorldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'world:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the world data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Installing nnjeim/world...');

        // publish migrations
        Artisan::call('vendor:publish --tag=world --force');
		// Clear config cache so the published config is loaded
		Artisan::call('config:clear');
		// Optionally re-register the config if needed
		if (config('world.migrations.countries.optional_fields') === null) {
			$worldConfig = include config_path('world.php');
			config()->set('world', $worldConfig);
		}
        // migrate new tables
        Artisan::call('migrate');
        // re-seed the world data
        Artisan::call('db:seed --class=WorldSeeder --database=' . config('world.connection'), array(), $this->getOutput());
    }
}
