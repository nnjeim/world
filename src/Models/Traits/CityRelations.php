<?php

namespace Nnjeim\World\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait CityRelations
{
	public function country(): BelongsTo
	{
		$countryClass = config('world.models.countries');

		return $this->belongsTo($countryClass);
	}

	public function state(): BelongsTo
	{
		$stateClass = config('world.models.states');

		return $this->belongsTo($stateClass);
	}
}
