<?php

namespace Nnjeim\World\Data;

use RuntimeException;

class WorldDataset
{
    public array $countries;
    public array $states;
    public array $cities;
    public array $currencies;
    public array $languages;

    public function __construct(?string $path = null, bool $loadCities = true)
    {
        $path ??= dirname(__DIR__, 2) . '/resources/json';
        if ($loadCities && filesize($path . '/cities.json') > 8 * 1024 * 1024) {
            $this->checkMemoryLimit();
        }
        foreach (['countries', 'states', 'cities', 'currencies', 'languages'] as $module) {
            if ($module === 'cities' && ! $loadCities) {
                $this->cities = [];
                continue;
            }
            $this->{$module} = json_decode(file_get_contents("{$path}/{$module}.json"), true, 512, JSON_THROW_ON_ERROR);
        }
        $this->validate();
    }

    private function checkMemoryLimit(): void
    {
        $limit = trim(ini_get('memory_limit'));
        if ($limit === '-1') {
            return;
        }
        $unit = strtolower(substr($limit, -1));
        $multiplier = ['g' => 1024 ** 3, 'm' => 1024 ** 2, 'k' => 1024][$unit] ?? 1;
        if ((float) $limit * $multiplier < 1024 ** 3) {
            throw new RuntimeException('The full cities import needs a PHP memory limit of 1G. Run php -d memory_limit=1G artisan world:install (or world:refresh), or disable the cities module.');
        }
    }

    public function validate(): void
    {
        $countries = $this->index($this->countries, 'countries');
        $states = $this->index($this->states, 'states');
        $this->index($this->cities, 'cities');
        $codes = [];
        foreach ($countries as $country) {
            $code = $country['iso2'];
            if (! preg_match('/^[A-Z]{2}$/D', $code) || isset($codes[$code])) {
                throw new RuntimeException("Invalid or duplicate country code: {$code}");
            }
            $codes[$code] = true;
            $this->coordinates($country);
        }
        foreach ($states as $state) {
            $this->countryReference($state, $countries);
            $this->coordinates($state);
        }
        foreach ($this->cities as $city) {
            $this->countryReference($city, $countries);
            $state = $states[$city['state_id']] ?? null;
            if ($state === null || $state['country_id'] !== $city['country_id']
                || $state['name'] !== $city['state_name'] || $state['state_code'] !== $city['state_code']) {
                throw new RuntimeException("Invalid state reference for city {$city['id']}");
            }
            $this->coordinates($city);
        }
        $languageCodes = [];
        foreach ($this->languages as $language) {
            if (isset($languageCodes[$language['code']])) {
                throw new RuntimeException("Duplicate language code: {$language['code']}");
            }
            $languageCodes[$language['code']] = true;
        }
    }

    private function index(array $records, string $module): array
    {
        $index = [];
        foreach ($records as $record) {
            if (! isset($record['id'], $record['name']) || isset($index[$record['id']])) {
                throw new RuntimeException("Missing fields or duplicate source ID in {$module}");
            }
            $index[$record['id']] = $record;
        }
        return $index;
    }

    private function countryReference(array $record, array $countries): void
    {
        $country = $countries[$record['country_id']] ?? null;
        if ($country === null || $country['iso2'] !== $record['country_code'] || $country['name'] !== $record['country_name']) {
            throw new RuntimeException("Invalid country reference for record {$record['id']}");
        }
    }

    private function coordinates(array $record): void
    {
        foreach (['latitude' => 90, 'longitude' => 180] as $field => $limit) {
            $value = $record[$field] ?? null;
            if ($value !== null && $value !== '' && (! is_numeric($value) || abs((float) $value) > $limit)) {
                throw new RuntimeException("Invalid {$field} for record {$record['id']}");
            }
        }
    }
}
