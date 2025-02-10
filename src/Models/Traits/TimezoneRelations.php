<?php

namespace Nnjeim\World\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait TimezoneRelations
{
	/**
	 * @return BelongsTo
	 */
	public function country(): BelongsTo
	{
		$countryClass = config('world.models.countries');

		return $this->belongsTo($countryClass);
	}
}
