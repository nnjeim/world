<?php

namespace Nnjeim\World\Data;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WorldDataSynchronizer
{
    private Connection $connection;
    private string $trackingTable;
    private bool $dryRun;
    private int $previewId = -1;
    private array $summary = [];

    public function sync(WorldDataset $dataset, bool $dryRun = false): array
    {
        $lock = Cache::lock('world:data-sync:' . hash('sha256', json_encode([
            config('world.connection'), config('world.migrations'),
        ])), 3600);
        if (! $lock->get()) {
            throw new RuntimeException('Another World data update is running. Retry after it finishes.');
        }
        try {
            $dataset->validate();
            return $this->synchronize($dataset, $dryRun);
        } finally {
            $lock->release();
        }
    }

    private function synchronize(WorldDataset $dataset, bool $dryRun): array
    {
        if (config('world.modules.cities') && ! config('world.modules.states')) {
            throw new RuntimeException('The cities module requires the states module.');
        }
        $this->connection = DB::connection(config('world.connection'));
        $this->trackingTable = config('world.migrations.data_records.table_name', 'world_data_records');
        $this->dryRun = $dryRun;
        $this->previewId = -1;
        $this->summary = [];
        if (! $dryRun && ! $this->connection->getSchemaBuilder()->hasTable($this->trackingTable)) {
            throw new RuntimeException('Run the World package migrations before updating data.');
        }

        // Group source records once; do not scan all cities for each country.
        $states = $cities = [];
        foreach ($dataset->states as $state) {
            $states[$state['country_id']][] = $state;
        }
        if (config('world.modules.cities')) {
            foreach ($dataset->cities as $city) {
                $cities[$city['country_id']][] = $city;
            }
        }

        $this->connection->transaction(function () use ($dataset, $states, $cities) {
            $countryRows = [];
            foreach ($dataset->countries as $country) {
                if ($this->included($country['iso2'])) {
                    $countryRows[$country['iso2']] = $country;
                }
            }
            $countryIds = $this->syncRows('countries', '', $countryRows, ['iso2']);
            foreach ($countryRows as $code => $country) {
                $countryId = $countryIds[$code];
                $stateIds = [];
                if (config('world.modules.states')) {
                    $rows = [];
                    foreach ($states[$country['id']] ?? [] as $state) {
                        $rows[(string) $state['id']] = array_replace($state, ['country_id' => $countryId]);
                    }
                    $stateIds = $this->syncRows('states', $code, $rows, ['name'], $countryId);
                }
                if (config('world.modules.cities')) {
                    $rows = [];
                    foreach ($cities[$country['id']] ?? [] as $city) {
                        $rows[(string) $city['id']] = array_replace($city, [
                            'country_id' => $countryId, 'state_id' => $stateIds[(string) $city['state_id']],
                        ]);
                    }
                    $this->syncRows('cities', $code, $rows, ['name', 'state_id'], $countryId);
                }
                if (config('world.modules.timezones')) {
                    $rows = [];
                    foreach ($country['timezones'] as $timezone) {
                        $rows[$code . ':' . $timezone['zoneName']] = ['country_id' => $countryId, 'name' => $timezone['zoneName']];
                    }
                    $this->syncRows('timezones', $code, $rows, ['name'], $countryId);
                }
                if (config('world.modules.currencies')) {
                    $currency = $dataset->currencies[$country['currency']] ?? [
                        'name' => $country['currency_name'] ?: $country['currency'],
                        'code' => $country['currency'], 'symbol' => $country['currency_symbol'],
                        'symbol_native' => $country['currency_symbol'], 'decimal_digits' => 2,
                    ];
                    $this->syncRows('currencies', $code, [$code => [
                        'country_id' => $countryId, 'name' => (string) $currency['name'],
                        'code' => (string) $currency['code'], 'symbol' => (string) $currency['symbol'],
                        'symbol_native' => (string) $currency['symbol_native'], 'precision' => (int) $currency['decimal_digits'],
                    ]], ['country_id'], $countryId);
                }
            }
            if (config('world.modules.languages')) {
                $rows = [];
                foreach ($dataset->languages as $language) {
                    $rows[$language['code']] = $language;
                }
                $this->syncRows('languages', '', $rows, ['code']);
            }
        });
        if (! $dryRun) {
            WorldDataCache::invalidate();
        }
        return $this->summary;
    }

    private function included(string $code): bool
    {
        $allowed = config('world.allowed_countries', []);
        return ($allowed === [] || in_array($code, $allowed, true))
            && ! in_array($code, config('world.disallowed_countries', []), true);
    }

    /**
     * Adopt legacy rows by their natural keys, then track source IDs independently
     * of application IDs. Neither removed source rows nor application rows are deleted.
     */
    private function syncRows(string $module, string $scope, array $rows, array $identity, ?int $countryId = null): array
    {
        $this->summary[$module] ??= ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'preserved' => 0];
        $modelClass = config("world.models.{$module}");
        $table = (new $modelClass())->getTable();
        $schema = $this->connection->getSchemaBuilder();
        $exists = $schema->hasTable($table);
        if (! $exists && ! $this->dryRun) {
            throw new RuntimeException("Missing World table: {$table}");
        }
        $columns = $exists ? $schema->getColumnListing($table) : $this->previewColumns($module);
        $columns = array_intersect($columns, $this->previewColumns($module));
        $existing = [];
        if ($exists && ($countryId === null || $countryId > 0)) {
            $query = $this->connection->table($table)->orderBy('id');
            if ($countryId !== null) {
                $query->where('country_id', $countryId);
            }
            foreach ($query->get() as $record) {
                $existing[$record->id] = (array) $record;
            }
        }
        $tracking = [];
        if ($schema->hasTable($this->trackingTable)) {
            foreach ($this->connection->table($this->trackingTable)->where('module', $module)->where('scope', $scope)->get() as $record) {
                $tracking[$record->source_key] = (array) $record;
            }
            // Source IDs remain stable even when a place moves to another country.
            foreach (array_chunk(array_keys($rows), 1000) as $keys) {
                foreach ($this->connection->table($this->trackingTable)->where('module', $module)
                    ->where('scope', '<>', $scope)->whereIn('source_key', $keys)->get() as $record) {
                    $tracking[$record->source_key] = (array) $record;
                }
            }
            $missingIds = array_diff(array_column($tracking, 'record_id'), array_keys($existing));
            foreach (array_chunk($missingIds, 1000) as $ids) {
                foreach ($this->connection->table($table)->whereIn('id', $ids)->get() as $record) {
                    $existing[$record->id] = (array) $record;
                }
            }
        }
        $reserved = array_fill_keys(array_column($tracking, 'record_id'), true);
        $candidates = [];
        foreach ($existing as $id => $record) {
            if (! isset($reserved[$id])) {
                $candidates[$this->identity($record, $identity)][] = $id;
            }
        }
        $result = $pendingTracking = [];
        foreach ($rows as $sourceKey => $row) {
            $attributes = array_intersect_key($row, array_flip($columns));
            $attributes = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $attributes);
            $tracked = $tracking[$sourceKey] ?? null;
            $baseline = $tracked ? json_decode($tracked['attributes'], true, 512, JSON_THROW_ON_ERROR) : null;
            $id = $tracked['record_id'] ?? null;
            if ($id !== null && ! isset($existing[$id])) {
                throw new RuntimeException("Tracked {$module} record {$id} is missing or moved; review the World data mapping before retrying.");
            }
            if ($id === null) {
                $key = $this->identity($attributes, $identity);
                if (! empty($candidates[$key])) {
                    $id = array_shift($candidates[$key]);
                }
            }
            if ($id === null) {
                $id = $this->dryRun ? $this->previewId-- : $this->connection->table($table)->insertGetId($attributes);
                $this->summary[$module]['created']++;
            } else {
                $changes = [];
                $relationships = $module === 'cities' ? ['country_id', 'state_id', 'country_code', 'state_code']
                    : ($module === 'states' ? ['country_id', 'country_code'] : []);
                $localRelationship = false;
                foreach (array_intersect($relationships, ['country_id', 'state_id']) as $field) {
                    if ($baseline !== null && array_key_exists($field, $baseline) && ! $this->equal($existing[$id][$field], $baseline[$field])) {
                        $localRelationship = true;
                    }
                }
                foreach ($attributes as $field => $value) {
                    $current = $existing[$id][$field];
                    if ($this->equal($current, $value)) {
                        continue;
                    }
                    if (($localRelationship && in_array($field, $relationships, true))
                        || ($baseline !== null && array_key_exists($field, $baseline) && ! $this->equal($current, $baseline[$field]))) {
                        $this->summary[$module]['preserved']++;
                        continue;
                    }
                    $changes[$field] = $value;
                }
                $this->summary[$module][$changes === [] ? 'unchanged' : 'updated']++;
                if (! $this->dryRun && $changes !== []) {
                    $this->connection->table($table)->where('id', $id)->update($changes);
                }
            }
            $result[$sourceKey] = (int) $id;
            if (! $this->dryRun && ($baseline !== $attributes || $tracked === null || $tracked['scope'] !== $scope)) {
                $pendingTracking[] = [
                    'module' => $module, 'source_key' => (string) $sourceKey, 'scope' => $scope,
                    'record_id' => $id, 'attributes' => json_encode($attributes, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                ];
            }
        }
        foreach (array_chunk($pendingTracking, 100) as $chunk) {
            $this->connection->table($this->trackingTable)->upsert($chunk, ['module', 'source_key'], ['record_id', 'scope', 'attributes']);
        }
        return $result;
    }

    private function previewColumns(string $module): array
    {
        $base = [
            'countries' => ['iso2', 'name'], 'states' => ['country_id', 'name'],
            'cities' => ['country_id', 'state_id', 'name'], 'timezones' => ['country_id', 'name'],
            'currencies' => ['country_id', 'name', 'code', 'symbol', 'symbol_native', 'precision'],
            'languages' => ['code', 'name', 'name_native', 'dir'],
        ];
        foreach (config("world.migrations.{$module}.optional_fields", []) as $name => $field) {
            if ($field['required']) {
                $base[$module][] = $name;
            }
        }
        return $base[$module];
    }

    private function identity(array $row, array $fields): string
    {
        return json_encode(array_map(fn ($field) => (string) ($row[$field] ?? ''), $fields), JSON_THROW_ON_ERROR);
    }

    private function equal($left, $right): bool
    {
        return $left === null || $right === null ? $left === $right : (string) $left === (string) $right;
    }
}
