<?php

namespace Nnjeim\World\Actions\Country\Transformers;

use Illuminate\Database\Eloquent\Collection;
use Nnjeim\World\CountryFlag;

trait IndexTransformer
{
	/**
	 * @param  Collection  $countries
	 * @param  array  $fields
	 * @return \Illuminate\Support\Collection
	 */
	protected function transform(Collection $countries, array $fields): \Illuminate\Support\Collection
	{
		return $countries
			->map(
				function ($country) use ($fields) {
					$return = $country->only($fields);

					if (in_array('flag', $fields, true)) {
						$return['flag'] = CountryFlag::emoji($country->iso2);
					}

					$return = array_merge(
						$return,
						['name' => trans('world::country.' . $country->iso2)]
					);

					if (in_array('states', $fields)) {
						$return = array_merge(
							$return,
							['states' => $country->states->map(fn ($state) => $state->only('id', 'name'))]
						);
					}

					if (in_array('cities', $fields)) {
						$return = array_merge(
							$return,
							['cities' => $country->cities->map(fn ($city) => $city->only('id', 'name'))]
						);
					}

					if (in_array('timezones', $fields)) {
						$return = array_merge(
							$return,
							['timezones' => $country->timezones->map(fn ($timezone) => $timezone->only('id', 'name'))]
						);
					}

					return $return;
				}
			);
	}
}
