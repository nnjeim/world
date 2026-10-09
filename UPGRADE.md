# Upgrade

## From 1.1.39 to the next release

Back up the database and retain customized configuration. Update the package, then run:

```bash
php artisan world:refresh --dry-run
php artisan world:refresh
# Production deployments:
php artisan world:refresh --force
```

The command runs the new tracking migration and synchronizes reference data while retaining
existing application IDs, custom columns and local records. Do not force-publish configuration
or drop existing World tables. The first tracked import adopts legacy records by natural keys
and updates package supplied fields; later imports preserve local edits to tracked fields.
Review [DATA_UPDATES.md](docs/DATA_UPDATES.md) before the first import, particularly if legacy
names were customized or renamed source records are unmatched. Removed source records remain
available for application review.

The optional `flag` response field is computed from ISO2 and requires no migration. The
tracking table defaults to `world_data_records`; keep it in backups. World cache generations
are invalidated after a successful data update without clearing application cache entries.

The historical instructions below apply to those older releases, whose refresh command
rebuilds tables and cannot run in production. Use the preserving commands above after
installing the next release.

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
