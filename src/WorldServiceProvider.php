<?php

namespace Nnjeim\World;

use Nnjeim\World\Http\Middleware;
use Nnjeim\World\WorldHelper;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

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
	public function boot(Router $router)
	{
		//Helpers
		require __DIR__ . '/Helpers/Helpers.php';
		// Load Middleware
		$router->aliasMiddleware('locale.set', Middleware\Localization::class);
		//Load migrations
		$this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
		// Load routes
		$this->loadRoutesFrom(__DIR__ . '/Routes/index.php');
		// Load translations
		$this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'world');
		// Load the configuration
		$this->mergeConfigFrom(__DIR__ . '/../config/world.php', 'world');

		if ($this->app->runningInConsole()) {

			$this->publishResources();
		}
	}

	protected function publishResources()
	{
		$this->publishes([
			__DIR__ . '/../config/world.php' => config_path('world.php'),
		], 'world');

		$this->publishes([
			__DIR__ . '/Database/Seeders/WorldSeeder.php' => database_path('seeders/WorldSeeder.php'),
		], 'world');
	}
}
