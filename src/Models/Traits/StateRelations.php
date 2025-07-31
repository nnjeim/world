<?php

namespace Nnjeim\World\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait StateRelations
{
	public function country(): BelongsTo
	{
		$countryClass = config('world.models.countries');

		return $this->belongsTo($countryClass);
	}

	public function cities(): HasMany
	{
		$cityClass = config('world.models.cities');

		return $this->hasMany($cityClass);
	}
}
