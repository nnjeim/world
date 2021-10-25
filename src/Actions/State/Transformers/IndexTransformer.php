<?php

namespace Nnjeim\World\Actions\State\Transformers;

use Illuminate\Database\Eloquent\Collection;

trait IndexTransformer
{
	/**
	 * @param  Collection  $states
	 * @param  array  $fields
	 * @return \Illuminate\Support\Collection
	 */
	protected function transform(Collection $states, array $fields): \Illuminate\Support\Collection
	{
		return $states
			->map(
				function($state) use ($fields) {

					$return = $state->only($fields);

					if(in_array('country', $fields)) {
						$return = array_merge(
							$return,
							['country' => $state->country->only('id', 'name')]
						);
					}

					if(in_array('cities', $fields)) {
						$return = array_merge(
							$return,
							['cities' => $state->cities->map(fn($city) => $city->only('id', 'name'))]
						);
					}

					return $return;
				}
			);
	}
}
