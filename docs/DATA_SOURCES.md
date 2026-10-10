# World data sources and review policy

The PHP package is MIT licensed. Third party data retains its source licensing and attribution requirements.

## Existing dataset

The inherited countries, states, cities, currencies and languages are the dataset tracked in
[nnjeim/world at 5d069b7](https://github.com/nnjeim/world/tree/5d069b7).
The repository did not include complete provenance for every inherited record. That history
still needs an audit; this document does not claim that all historical records have been
independently verified. Keep their source IDs stable when making corrections.

## Cayman Islands addition — reviewed 9 October 2026

- District names and grouping: Cayman Islands Economics and Statistics Office,
  [2021 Census, section 1.1, table 1.1D](https://www.eso.ky/user_images/census_2021/Section_1.1.pdf)
  and [section 1.2, table 1.2A](https://www.eso.ky/user_images/census_2021/Section_1.2.pdf).
  There are six districts: George Town, West Bay, Bodden Town, North Side, East End,
  and Sister Islands. Cayman Brac and Little Cayman belong to the Sister Islands group.
- Coordinates, populated place names and district associations: [GeoNames Cayman extract](https://download.geonames.org/export/dump/KY.zip),
  downloaded 9 October 2026. The reviewed original extract is retained in
  [data/geonames-KY-2026-10-09.tsv](data/geonames-KY-2026-10-09.tsv).
- GeoNames data is licensed under [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/).
  Attribution: geographic data supplied by [GeoNames](https://www.geonames.org/).
  See the publisher's [format and licence](https://download.geonames.org/export/dump/readme.txt).
- District feature IDs: 10346796, 10375968, 10375969, 10375970, 10375971, 10375972.
  Each added record retains its `geonameId` for traceability. The district name
  “Sister Island” in GeoNames is normalized to the census name “Sister Islands”.
- The short `state_code` values GT, WB, BT, NS, EE and SI are package identifiers,
  not ISO subdivision codes. They fit existing optional database columns.
- The `cities` dataset includes towns and villages. The addition includes the 15
  GeoNames PPL, PPLA and PPLC features whose admin1 value explicitly identifies
  one of the six districts. Places without a verified district association are excluded;
  this is partial locality coverage, not a claim of a complete Cayman gazetteer.

To reproduce this addition, run `scripts/import-cayman.py` against the retained TSV
in a checkout of the baseline commit that has no Cayman districts. The importer appends
new package IDs; updating existing records requires reviewing their stable IDs first.

## Internal metadata corrections

- Tai Po District (source state ID 4885) used `KH`; its parent country ID 98 is Hong Kong
  (`HK`). The country ID and name already agreed; only the copied ISO2 label was corrected.
- 193 city records had copied `state_code` values different from the state referenced
  by their `state_id`. Those copied values now match the existing parent record.
  This correction does not redefine subdivision codes or change source relationships.

## Reviewing future data changes

1. Record a primary source URL, retrieval date, licence and the exact records affected.
   Retain small source extracts when redistribution is permitted.
2. Preserve source IDs. Append new IDs; never renumber existing records. Record renamed,
   moved or retired places explicitly, with evidence for the relationship change.
3. Run `composer test`. It checks source ID uniqueness, country and state relationships,
   copied codes and names, coordinate ranges, country flags and localization parity.
4. Regenerate `resources/data-manifest.json` with
   `python3 scripts/update-data-manifest.py`. Review the checksum, count and per-country
   coverage changes in the same pull request. A zero count records dataset coverage;
   it does not prove that a country has no subdivisions or localities.
5. Test installation and an update from existing data. Confirm stable application IDs,
   retained local records, preserved custom fields and correct cache invalidation.

Data corrections are reviewed and shipped in package releases. Applications update from
the bundled release data; installation never fetches an unreviewed worldwide dataset.
