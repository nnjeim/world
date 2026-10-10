# Installing and updating World 2.0 data

`world:install` and `world:refresh` synchronize the bundled data. They do not truncate
World tables, drop tables, delete application records, or renumber existing database IDs.
Both commands return a nonzero exit status on failure. A complete cities import requires
`memory_limit=1G`; the loader reports this before changing data. Use
`php -d memory_limit=1G artisan world:refresh` (plus `--force` in production), or disable
the cities module for a smaller import. Disabled cities are not loaded into memory.

```bash
php artisan world:install --dry-run
php artisan world:install
php artisan world:refresh --dry-run
php artisan world:refresh
# In a production deployment:
php artisan world:refresh --force
```

Installation publishes missing configuration, translation and seeder files without
`--force`, then runs only the package migrations on `world.connection`. It retains
existing files and the active configuration cache. Refresh runs the same package migrations
and updates data without publishing resources. A dry run publishes no files, runs no
migrations, changes no data and does not invalidate caches. It reports created, updated,
unchanged records and preserved local fields. The production confirmation follows Laravel's
usual command behavior; `--force` bypasses the confirmation, not data preservation.

## Existing installations

Back up the database and preview the first update. Country rows are matched by ISO2;
states by country and name; cities by country, state and name; timezones by country and name;
currencies by country; languages by code. Legacy duplicate names are adopted in ascending
application ID order and source order. Existing IDs and custom columns, including country
`status`, are retained. The first adoption updates package supplied fields because earlier
versions did not track their original values. Review the preview if those fields were
customized before this upgrade. A renamed legacy place that cannot be matched is added as a
new row; its old row is retained for application review.

A new `world_data_records` table tracks package source IDs, application row IDs and the last
bundled values. Its name can be configured through `world.migrations.data_records.table_name`
**before the first migration**. Retain this table along with the World tables in backups.
Do not delete it to reset updates.

After adoption, source renames and moves keep the same application ID. If an application
changes a tracked field from its last bundled value, later updates preserve that local edit
and report it. Package supplied fields that still match the previous bundled value receive
the new value. Custom columns are never updated by the synchronizer. Retired source records,
custom rows, excluded countries and disabled modules remain in the database; exclusions
control which countries are imported, not which existing rows are deleted.

Updates use a transaction on `world.connection`. A data failure rolls back every module,
including tracking records. Migrations and resource publishing precede that transaction.
Imports use query builder writes, so custom model observers are not invoked. The configured
model classes determine table names. Do not run application writes to World reference rows
concurrently with an import. The package commands and seeder share a cache lock to serialize
imports; use a shared cache store across deployment workers.

World query caches include a dataset generation. Successful updates change that generation,
including geolocation caches that contain World IDs. Application cache entries are retained.
Query caches expire after `world.cache.ttl` seconds (default 86400), so previous generations
are eventually reclaimed. Existing published configurations can use `WORLD_CACHE_TTL` after
adding that setting, or set `world.cache.ttl` directly. Refresh restarts the normal cache TTL.

Manual `db:seed --class=WorldSeeder` now uses the same preserving synchronizer after the
package migrations. The cities module requires the states module.

## Schema and configuration changes

Updates do not rebuild schemas when optional fields are toggled. Add columns with an
application migration, then enable the corresponding setting. Existing package defaults
are merged at runtime; add new settings deliberately to customized configuration. For
example, `world.cache.ttl` and the tracking table name have defaults even with an older
published configuration. Avoid force-publishing configuration during upgrades.

## Rolling back package migrations

World 2.0 migration `down()` methods intentionally retain all six reference tables and
`world_data_records`, including tables created by a fresh installation. An adoption batch
may contain migrations whose tables already existed; rolling it back must not delete those
rows. Keeping source tracking also preserves the baseline used to detect local edits when
migrations are rerun. A rollback removes migration history entries, so the next migrate or
World command records them again while retaining the existing tables.

Rollback does not undo imported data. Restore a database backup if an import must be
reverted. Removing World tables requires an explicit application migration after reviewing
application foreign keys. Laravel's `migrate:fresh` still drops database tables; the World
rollback policy does not change that Laravel command. Downgrading package code to 1.x
restores the 1.x migration and destructive maintenance behavior.
