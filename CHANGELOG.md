# Changelog

All notable changes to `country` will be documented in this file

## 1.1.36 - 2025-08-21
- added Armenian locale support by @vahan
- added Nepali locale support by @sagautam5
- added Sawahili locale support by @ludanadeodatus
- Available php memory check by @sorrowflufloyd
- update documentation

## 1.1.35 - 2025-05-15
- added Azerbaijani locale @elnurvl
- update documentation

## 1.1.34 - 2025-03-03
- Tested with Laravel 11.x && php 8.3/8.4
- Tested with Laravel 12.x && php 8.3/8.4
- Fix to the world install console command

## 1.1.32 - 2024-12-20
- Tested with Laravel 11.x
- Tested with PHP 8.4
- Minor code rewrite for better performance

## 1.1.31 - 2024-07-15
- Addition of the  fa locale @DevNull-IR
- Addition of cities from Malaysia @hirenkeraliya

### 1.1.30 - 2024-02-08
- Addition of the  br, fr, hr, kr, pt locales @rinodrummer
- Addition of the install helper @rinodrummer
- Ability to define a custom database connection @rinodrummer

See UPGRADE.md file.

### 1.1.29 - 2023-12-18
- Addition of the croatian localisation by @mbanusic

### 1.1.28 - 2023-10-22
- Increase of the state_code column length in the world config file. @mefenlon

### 1.1.27 - 2023-06-15
- Disallow world:refresh in production.
- Addition of the arabic locale (ar) in countries.json translations. @waadmawlood

### 1.1.26 - 2023-06-05
- Added missing Côte d'Ivoire native name

### 1.1.25 - 2023-05-30
- Improved seeder performance @mrmmg

### 1.1.24 - 2023-04-07
- As of January 2023, the currency of Croatia changed to Euro @manuelfrans

### 1.1.23 - 2023-03-20
- As of January 2023, the currency of Croatia changed to Euro @manuelfrans

### 1.1.22 - 2023-03-20
- Correction of the Croatian and Czech language codes @svenraudkivi

### 1.1.21 - 2023-03-08
- Removed Singapore cities and states. @pengkong

### 1.1.20 - 2023-01-08
- Updated source data: countries, states and cities.

### 1.1.18 - 2022-11-29
- Corrected message translation in the response.

### 1.1.17 - 2022-11-29
- Corrected path of the response builder class.

### 1.1.16 - 2022-11-29
- Simplification of the response trait.
- Rework of the actions responses.

### 1.1.15 - 2022-06-06
- Italian locale support (it)

### 1.1.14 - 2022-06-06
- Turkisk locale support (tr)

### 1.1.13 - 2022-06-06
- seeder action enhancement. credit to @dgironella and @Mello21century

### 1.1.12 - 2022-05-24
- addition of the allowed and disallowed country lists in the world config file.  

### 1.1.11 - 2022-05-18
- addition of the search field in all the request queries.

### 1.1.10 - 2022-04-22
- addition of the posibility to filter with the optional fields.

### 1.1.9 - 2022-04-08
- renaming of the sub_region to subregion in the countries.json file.  

### 1.1.8 - 2022-04-08
- bug fix in the countries subregion field.  

### 1.1.7 - 2022-04-08
- @emiliopedrollo. Conditional route generation per module and overall routes enablement/disablement.
- @emiliopedrollo. php 7.4 compatibility fix.

### 1.1.6 - 2022-04-05
- reworked action classes.

### 1.1.5 - 2022-03-27
- add Dutch localization by @gdevlugt.

### 1.1.4 - 2022-03-07
- custom table names.
- optional database fields.
- Enhanced seeder (lower memory footprint).

### 1.1.3 - 2022-02-10
- Obsoleted phone formatting helpers. The phone formatting will be re-introduced in a future version.

### 1.1.2 - 2022-01-27
- Route bindings removed.
- Creation of the demonstration domain laravel-world.com

### 1.1.1 - 2022-01-24
- Resources publishing.
- Fixing of the states translation keys.

### 1.1.0 - 2022-01-22
- Addition of the languages module.
- Addition of the country language in the countries json source file.
- Creation of the languages route, controller and action.
- General code style fix.
- New configuration file (world.php), allowing the choosing of which modules to install.

### 1.0.8 - 2021-11-19
- @ivanshamir: Bangla Localization.
- 
### 1.0.7 - 2021-11-19
- @ivanshamir: Addition of the setLocale helper method.

### 1.0.6 - 2021-11-10
- @parth391: Correction for found duplicates in  indian cities.

### 1.0.5 - 2021-11-09
- @nnjeim: Fixing of the trimmed states and cities names

### 1.0.4 - 2021-11-06
- @nnjeim: Addition of the unit tests

### 1.0.3 - 2021-11-05
- @cloudchristoph: Correction of the German response translations.

### 1.0.0 - 2021-10-24
- @nnjeim: initial release.
