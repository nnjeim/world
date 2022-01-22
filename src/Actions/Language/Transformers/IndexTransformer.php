<?php

namespace Nnjeim\World\Actions\Language\Transformers;

use Illuminate\Database\Eloquent\Collection;

trait IndexTransformer
{
	/**
	 * @param  Collection  $languages
	 * @param  array  $fields
	 * @return \Illuminate\Support\Collection
	 */
	protected function transform(Collection $languages, array $fields): \Illuminate\Support\Collection
	{
		return $languages->map(fn ($language) => $language->only($fields));
	}
}
