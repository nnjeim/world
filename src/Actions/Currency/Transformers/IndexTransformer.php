<?php

namespace Nnjeim\World\Actions\Currency\Transformers;

use Illuminate\Database\Eloquent\Collection;

trait IndexTransformer
{
	/**
	 * @param  Collection  $currencies
	 * @param  array  $fields
	 * @return \Illuminate\Support\Collection
	 */
	protected function transform(Collection $currencies, array $fields): \Illuminate\Support\Collection
	{
		return $currencies
			->map(
				function ($currency) use ($fields) {
					$return = $currency->only($fields);

					if (in_array('country', $fields)) {
						$return = array_merge(
							$return,
							['country' => $currency->country->only('id', 'name')]
						);
					}

					return $return;
				}
			);
	}
}
