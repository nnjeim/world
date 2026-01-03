<?php

namespace Nnjeim\World\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait ExchangeRateRelations
{
	public function currency(): BelongsTo
	{
		$currencyClass = config('world.models.currencies');

		return $this->belongsTo($currencyClass);
	}
}

