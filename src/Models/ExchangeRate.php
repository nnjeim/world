<?php

namespace Nnjeim\World\Models;

use Nnjeim\World\Models\Traits\WorldConnection;
use Nnjeim\World\Models\Traits\ExchangeRateRelations;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ExchangeRate
 *
 * @property int $id
 * @property int $currency_id
 * @property float $exchange_rate
 * @property string $base_currency
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read Model|Currency|null $currency
 */
class ExchangeRate extends Model
{
	use ExchangeRateRelations;
    use WorldConnection;

	protected $fillable = [
		'currency_id',
		'exchange_rate',
		'base_currency',
	];

    protected function casts(): array
    {
        return [
            'currency_id' => 'int',
            'exchange_rate' => 'float',
        ];
    }

	/**
	 * Get the table associated with the model.
	 *
	 * @return string
	 */
	public function getTable(): string
	{
		return config('world.migrations.exchange_rates.table_name', parent::getTable());
	}
}

