<?php

namespace Nnjeim\World\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait StateRelations
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
     * @return HasMany
     */
	public function cities(): HasMany
	{
		$cityClass = config('world.models.cities');

		return $this->hasMany($cityClass);
	}
}
