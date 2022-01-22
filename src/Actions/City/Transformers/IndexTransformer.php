<?php

namespace Nnjeim\World\Actions\City\Transformers;

use Illuminate\Database\Eloquent\Collection;

trait IndexTransformer
{
	/**
	 * @param  Collection  $cities
	 * @param  array  $fields
	 * @return \Illuminate\Support\Collection
	 */
	protected function transform(Collection $cities, array $fields): \Illuminate\Support\Collection
	{
		return $cities
			->map(
				function ($city) use ($fields) {
					$return = $city->only($fields);

					if (in_array('country', $fields)) {
						$return = array_merge(
							$return,
							['country' => $city->country->only('id', 'name')]
						);
					}

					if (in_array('state', $fields)) {
						$return = array_merge(
							$return,
							['state' => $city->state->only('id', 'name')]
						);
					}

					return $return;
				}
			);
	}
}
