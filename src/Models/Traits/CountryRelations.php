<?php

namespace Nnjeim\World\Models\Traits;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait CountryRelations
{
	/**
	 * @return HasMany
	 */
	public function states(): HasMany
	{
		$stateClass = config('world.models.states');

		return $this->hasMany($stateClass, 'country_id', 'id');
	}

	/**
	 * @return HasMany
	 */
	public function cities(): HasMany
	{
		$cityClass = config('world.models.cities');

		return $this->hasMany($cityClass, 'country_id', 'id');
	}

	/**
	 * @return HasMany
	 */
	public function timezones(): HasMany
	{
		$timezoneClass = config('world.models.timezones');

		return $this->hasMany($timezoneClass, 'country_id', 'id');
	}

	/**
	 * @return HasOne
	 */
	public function currency(): HasOne
	{
		$currencyClass = config('world.models.currencies');

		return $this->hasOne($currencyClass, 'country_id', 'id');
	}
}
