<?php

namespace Nnjeim\World;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Nnjeim\World\Geolocate\GeolocateService;
use Nnjeim\World\Http\Middleware\Geolocate;

class WorldServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 */
	public function register(): void
	{
		// Register the main class to use with the facade
		$this->app->singleton('world', fn () => new WorldHelper());

		// Register the GeolocateService
		$this->app->singleton(GeolocateService::class, fn () => new GeolocateService());
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

		// Register geolocate middleware alias if module is enabled
		if (config('world.modules.geolocate', true)) {
			$this->registerGeolocateMiddleware();
		}

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
	 * Register the geolocate middleware alias.
	 */
	private function registerGeolocateMiddleware(): void
	{
		$router = $this->app->make(Router::class);
		$router->aliasMiddleware('geolocate', Geolocate::class);
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
			__DIR__ . '/../resources/lang' => resource_path('lang/vendor/world'),
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
			Commands\UpdateGeoipDatabase::class,
		]);
	}
}
