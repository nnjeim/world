# Upgrade

## 1.1.38 to 1.1.39

Version 1.1.39 requires PHP 8.1 or newer and supports Laravel 10 through Laravel 13. The MaxMind client has been upgraded to `geoip2/geoip2` v3.

Update the package and republish its configuration:

```bash
composer require nnjeim/world:^1.1.39
php artisan vendor:publish --tag=world --force
php artisan config:clear
```

The republished configuration adds `world.cache.enabled`, controlled by `WORLD_CACHE_ENABLED`. Caching remains enabled by default.

Because this release updates Algeria's administrative divisions and Bulgaria's currency, refresh the installed world data after reviewing any local customizations:

```bash
php artisan world:refresh
```

Republishing with `--force` overwrites local changes in `config/world.php`; merge custom settings back into the new file if necessary.

## 1.1.29 to 1.1.30

### Update Configuration

To ensure compatibility with the latest version, add a 'connection' entry in the `config/world.php` file. You have two options to do this:

#### Option 1: Manual Configuration

Open the `config/world.php` file and insert the following code snippet:

```php
/*
|--------------------------------------------------------------------------
 Connection
|--------------------------------------------------------------------------
*/
'connection' => env('WORLD_DB_CONNECTION', env('DB_CONNECTION')),
```

#### Option 2: Republish Configuration

Alternatively, you can republish the configuration file using the following Artisan command:

```bash
php artisan vendor:publish --tag=world --force
```

However, be aware that this command will overwrite any previous changes made to the configuration file.
