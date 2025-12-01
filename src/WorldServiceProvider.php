<?php

namespace Nnjeim\World;

use Illuminate\Support\ServiceProvider;

class WorldServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 */
	public function register(): void
	{
		// Register the main class to use with the facade
		$this->app->singleton('world', fn () => new WorldHelper());
	}

	/**
	 * Boot services.
	 */
	public function boot(): void
	{
		// Load routes
		$this->loadRoutesFrom(__DIR__ . '/Routes/index.php');
		// Load translations
		$this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'world');

		if ($this->app->runningInConsole()) {
			// Load the database migrations.
			$this->loadMigrations();
			// Publish the resources.
			$this->publishResources();
			// Load commands
			$this->loadCommands();
		}
	}

	/**
	 * method to load the migrations when php migrate is run in the console.
	 */
	private function loadMigrations(): void
	{
		$this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
	}

	/**
	 * Method to publish the resource to the app resources folder
	 */
	private function publishResources(): void
	{
		$this->publishes([
			__DIR__ . '/../config/world.php' => config_path('world.php'),
		], 'world');

		$this->publishes([
			__DIR__ . '/Database/Seeders/WorldSeeder.php' => database_path('seeders/WorldSeeder.php'),
		], 'world');

		$this->publishes([
			__DIR__ . '/../resources/lang' => base_path('lang/vendor/world'),
		], 'world');
	}

	/**
	 * Method to publish the resource to the app resources folder
	 */
	private function loadCommands(): void
	{
		$this->commands([
            Commands\InstallWorldData::class,
			Commands\RefreshWorldData::class,
		]);
	}
}
