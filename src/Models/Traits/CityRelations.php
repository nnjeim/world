<?php

namespace Nnjeim\World\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait CityRelations
{
	/**
	 * @return BelongsTo
	 */
	public function country(): BelongsTo
	{
		$countryClass = config('world.models.countries');

		return $this->belongsTo($countryClass);
	}

	/**
	 * @return BelongsTo
	 */
	public function state(): BelongsTo
	{
		$stateClass = config('world.models.states');

		return $this->belongsTo($stateClass);
	}
}
