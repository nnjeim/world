# Upgrading from World 1.x to 2.0.0

This guide covers upgrading to **World 2.0.0**. Published 1.1 tags retain their
original code and requirements. Staying on `^1.1` or `^1.1.39` excludes 2.0; exact pins
and lock files continue to identify the installed 1.x release.

## Compatibility

World 2.0 requires PHP 8.1+ and targets Laravel 10–13, as does 1.1.39. Users of earlier
1.1 releases must check these requirements before upgrading. In particular, 1.1.38 has
a PHP >=7.4 requirement and uses GeoIP2 v2; 2.0 uses GeoIP2 v3.

Existing facade methods, routes and default response formats remain available. The
computed country `flag` is opt-in. Existing installed IDs are retained during imports;
do not assume source JSON IDs equal application database IDs.

## Changes to maintenance behavior

| Operation | World 1.x | World 2.0 |
| --- | --- | --- |
| `world:install` | Force-publishes resources, clears config cache and runs application migrations | Publishes missing resources, retains config cache and runs only World migrations on `world.connection` |
| `world:refresh` | Drops and rebuilds World tables; unavailable in production | Synchronizes data, retaining IDs and tables; production confirmation or `--force` |
| Manual World seeding | Truncates and repopulates tables | Requires World migrations, then uses the preserving synchronizer |
| Removed records, excluded countries and disabled modules | Refresh/seeding may remove existing rows | Existing rows remain; exclusion settings control import selection |
| Optional field configuration | Refresh rebuilds the schema | Existing schema remains; add columns through application migrations |
| Model hooks | Country and currency creation can invoke model events | Imports use query builder writes; model events, observers and mutators are bypassed |
| World migration rollback | Drops World tables | Retains reference tables and source tracking, including fresh installations |
| Complete city import | Earlier memory checks required at least 512M | Loader requires `memory_limit=1G` or unlimited memory |

Adjust scripts that depended on resetting data, rebuilding schemas or invoking observers.
Applications that need exclusions to hide existing rows must implement that query behavior;
an exclusion setting does not delete or hide previously installed records in 2.0.

World commands and the seeder require an available cache store for import locks and cache
generation updates. Use a shared store across deployment workers. Disabling query caching
does not disable these import locks. Uncached queries themselves do not access that store.
Prepare application migrations for a database cache store before running World commands;
`world:install` runs only the package migrations.

## Upgrade procedure

1. Back up the World database and customized configuration. Keep the installed package
   version and lock file so the original code can be restored.
2. Review the runtime and maintenance changes above. Test the upgrade on a database copy.
3. Explicitly change the Composer constraint:

   ```bash
   composer require nnjeim/world:^2.0
   ```

4. Merge new settings into your customized configuration. Do not force-publish it.
   The tracking table defaults to `world_data_records`; its configurable name must be
   chosen before migration. Set `world.cache.ttl` directly or add the `WORLD_CACHE_TTL`
   setting if desired. If configuration is cached, rebuild that cache through your
   usual deployment process after changing settings.
5. Preview the import, then apply it with sufficient memory:

   ```bash
   php -d memory_limit=1G artisan world:refresh --dry-run
   php -d memory_limit=1G artisan world:refresh
   # Production deployments:
   php -d memory_limit=1G artisan world:refresh --force
   ```

   Refresh applies the World migrations, including the source tracking table. A dry run
   applies no migrations and writes no files or data. Disable the cities module if a
   smaller import is needed; importing cities requires the states module.
6. Verify application foreign keys, local records and important query results. Include
   `world_data_records` in future backups.

## First import and later updates

The first 2.0 import matches legacy countries by ISO2, states by country/name, and cities
by country/state/name. Other module identities are described in the
[data update guide](DATA_UPDATES.md#existing-installations). It retains matched IDs and
updates package fields because 1.x did not store a source baseline. Local edits made
before this first import cannot reliably be distinguished from old bundled values.
Review the preview, especially when names or other package fields were customized.

Unmatched renamed legacy places are retained and a new source row may be created. Removed
source records and custom rows remain. After adoption, tracking preserves subsequent local
edits and keeps application IDs through source renames and moves. Custom columns and country
status remain untouched.

## Reverting an upgrade

World migration rollback retains reference and tracking tables, and does not undo imported
data. Restore the database backup to revert a data update. Returning package code to 1.x
also returns its destructive installer, refresh, seeder and migration behavior. Do not run
those commands against upgraded data expecting 2.0 preservation guarantees.

See [rollback behavior](DATA_UPDATES.md#rolling-back-package-migrations) and the
[World 1.x documentation](../1.x/README.md) for the installed version's commands.
