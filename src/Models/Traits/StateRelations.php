<?php

namespace Nnjeim\World\Models\Traits;

use Nnjeim\World\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait StateRelations
{
	/**
	 * @return BelongsTo
	 */
	public function country(): BelongsTo
	{
		return $this->belongsTo(Models\Country::class);
	}

	public function cities(): HasMany
	{
		return $this->hasMany(Models\City::class);
	}
}
