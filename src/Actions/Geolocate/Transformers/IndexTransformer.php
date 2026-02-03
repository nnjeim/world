<?php

namespace Nnjeim\World\Actions\Geolocate\Transformers;

use Illuminate\Support\Collection;

trait IndexTransformer
{
    /**
     * Transform geolocation data and match with existing World models.
     *
     * @param array $geoData
     * @return Collection
     */
    protected function transform(array $geoData): Collection
    {
        $countryModel = config('world.models.countries');
        $stateModel = config('world.models.states');
        $cityModel = config('world.models.cities');
        $timezoneModel = config('world.models.timezones');

        // Find matching country
        $country = null;
        if (!empty($geoData['country_code'])) {
            $country = $countryModel::where('iso2', $geoData['country_code'])->first();
        }

        // Find matching state
        $state = null;
        if ($country && (!empty($geoData['state_code']) || !empty($geoData['state_name']))) {
            $query = $stateModel::where('country_id', $country->id);

            // Check if state_code column exists (it's optional in World migrations)
            $hasStateCode = config('world.migrations.states.optional_fields.state_code.required', false)
                || \Schema::hasColumn(config('world.migrations.states.table_name', 'states'), 'state_code');

            if ($hasStateCode && !empty($geoData['state_code'])) {
                $query->where(function ($q) use ($geoData) {
                    $q->where('state_code', $geoData['state_code'])
                        ->orWhere('name', $geoData['state_name']);
                });
            } else {
                // Fallback to name-only matching
                $query->where('name', $geoData['state_name']);
            }

            $state = $query->first();
        }

        // Find matching city
        $city = null;
        if (!empty($geoData['city_name']) && $country) {
            $query = $cityModel::where('country_id', $country->id)
                ->where('name', 'LIKE', $geoData['city_name'] . '%');

            if ($state) {
                $query->where('state_id', $state->id);
            }

            $city = $query->first();
        }

        // Find matching timezone
        $timezone = null;
        if (!empty($geoData['timezone']) && $country) {
            $timezone = $timezoneModel::where('country_id', $country->id)
                ->where('name', $geoData['timezone'])
                ->first();
        }

        return collect([
            'ip' => $geoData['ip'],
            'country' => $country ? [
                'id' => $country->id,
                'iso2' => $country->iso2,
                'iso3' => $country->iso3 ?? null,
                'name' => trans('world::country.' . $country->iso2) !== 'world::country.' . $country->iso2
                    ? trans('world::country.' . $country->iso2)
                    : $country->name,
                'phone_code' => $country->phone_code ?? null,
                'region' => $country->region ?? null,
                'subregion' => $country->subregion ?? null,
            ] : null,
            'state' => $state ? array_filter([
                'id' => $state->id,
                'name' => $state->name,
                'state_code' => $state->state_code ?? $geoData['state_code'] ?? null,
            ], fn ($v) => $v !== null) : [
                'name' => $geoData['state_name'] ?? null,
                'state_code' => $geoData['state_code'] ?? null,
            ],
            'city' => $city ? [
                'id' => $city->id,
                'name' => $city->name,
            ] : [
                'name' => $geoData['city_name'] ?? null,
            ],
            'coordinates' => [
                'latitude' => $geoData['latitude'] ?? null,
                'longitude' => $geoData['longitude'] ?? null,
                'accuracy_radius' => $geoData['accuracy_radius'] ?? null,
            ],
            'timezone' => $timezone ? [
                'id' => $timezone->id,
                'name' => $timezone->name,
            ] : [
                'name' => $geoData['timezone'] ?? null,
            ],
            'postal_code' => $geoData['postal_code'] ?? null,
        ]);
    }
}
