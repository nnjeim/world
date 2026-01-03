<?php

namespace Nnjeim\World\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait CurrencyRelations
{
	public function country(): BelongsTo
	{
		$countryClass = config('world.models.countries');

		return $this->belongsTo($countryClass);
	}

	public function exchangeRates(): HasMany
	{
		$exchangeRateClass = config('world.models.exchange_rates');

		return $this->hasMany($exchangeRateClass, 'currency_id', 'id');
	}
}
