<p align="center">
  <a href="https://world.bmbc.cloud">
    <img src="./world-ui-banner.png" width="100%" alt="World for Laravel — geographic data, ready for your UI"/>
  </a>
</p>

# World for Laravel

Countries, states, cities, timezones, currencies, languages and IP geolocation through
Laravel facades and API routes.

## Choose your documentation

| Version | Status | Documentation |
| --- | --- | --- |
| **1.1.39** | Previous stable release; PHP 8.1+, Laravel 10–13 | [World 1.x guide](docs/1.x/README.md) |
| **1.1.38** | Published legacy release; PHP >=7.4 requirement, GeoIP2 v2 | [Original 1.1.38 guide](https://github.com/nnjeim/world/blob/1.1.38/README.md) |
| **2.0.1** | Current stable release; changes to data maintenance from 1.x | [World 2.0 guide](docs/2.0/README.md) · [Upgrade from 1.x](docs/2.0/UPGRADE.md) |

Published versions with retained Git tags keep their original code. Removing an old
GitHub release page does not remove its Git tag. Use the documentation at your installed
tag for its exact behavior; retain published tags to keep those versions available.

## Install World 2.0

```bash
composer require nnjeim/world:^2.0
```

For an existing installation, review [the upgrade guide](docs/2.0/UPGRADE.md) and run
the preserving refresh command with a 1G memory limit.

## Stay on World 1.x

```bash
composer require nnjeim/world:^1.1.39
```

For an exact 1.1.38 installation:

```bash
composer require nnjeim/world:1.1.38
```

`^1.1` and `^1.1.39` stay below 2.0. Existing lock files retain their selected versions
when running `composer install`. Constraints such as `*`, `>=1.1` and `dev-master` can
select a different major version; review your constraint before updating.
See [Composer version constraints](https://getcomposer.org/doc/articles/versions.md).

## World 2.0

World 2.0 adds preserving data synchronization, source tracking, dry runs, optional country
flags and reviewed Cayman data. Refresh and seeding no longer rebuild tables or remove rows.
Schema changes require application migrations, and imports bypass model observers. Review
[the complete upgrade guide](docs/2.0/UPGRADE.md) before adopting it.

## Links

- [Interactive documentation and UI examples](https://world.bmbc.cloud)
- [Packagist](https://packagist.org/packages/nnjeim/world)
- [Changelog](CHANGELOG.md)
- [Upgrade guides](UPGRADE.md)
- [Contributing](CONTRIBUTING.md)
- [Dataset sources and attribution](docs/DATA_SOURCES.md)
