<?php

namespace Nnjeim\World;

use Nnjeim\World\WorldHelper;

use Illuminate\Support\ServiceProvider;

class WorldServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register()
	{
		// Register the main class to use with the facade
		$this->app->singleton('world', fn () => new WorldHelper());
	}

	/**
	 * @param  Router  $router
	 */
	public function boot()
	{
		// Helpers
		require __DIR__ . '/Helpers/Helpers.php';
		// Load routes
		$this->loadRoutesFrom(__DIR__ . '/Routes/index.php');
		// Load translations
		$this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'world');

		if ($this->app->runningInConsole()) {
			// Load the database migrations.
			$this->loadMigrations();
			// Publis the resources.
			$this->publishResources();
		}
	}

	private function loadMigrations()
	{
		$this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
	}


	private function publishResources()
	{
		$this->publishes([
			__DIR__ . '/../config/world.php' => config_path('world.php'),
		], 'world');

		$this->publishes([
			__DIR__ . '/Database/Seeders/WorldSeeder.php' => database_path('seeders/WorldSeeder.php'),
		], 'world');

		$this->publishes([
			__DIR__ . '/../resources/lang' => resource_path('lang/vendor/world'),
		], 'world');
	}
}
