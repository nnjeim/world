<?php

namespace Nnjeim\World\Models\Traits;

use Nnjeim\World\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait CurrencyRelations
{
	/**
	 * @return BelongsTo
	 */
	public function country(): BelongsTo
	{
		return $this->belongsTo(Models\Country::class);
	}
}
