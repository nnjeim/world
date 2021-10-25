<?php

namespace Nnjeim\World\Actions\Timezone\Transformers;

use Illuminate\Database\Eloquent\Collection;

trait IndexTransformer
{
	/**
	 * @param  Collection  $timezones
	 * @param  array  $fields
	 * @return \Illuminate\Support\Collection
	 */
	protected function transform(Collection $timezones, array $fields): \Illuminate\Support\Collection
	{
		return $timezones
			->map(
				function($timezone) use ($fields) {

					$return = $timezone->only($fields);

					if(in_array('country', $fields)) {
						$return = array_merge(
							$return,
							['country' => $timezone->country->only('id', 'name')]
						);
					}

					return $return;
				}
			);
	}
}
