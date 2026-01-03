<?php

namespace Nnjeim\World\Models;

use Nnjeim\World\Models\Traits\WorldConnection;
use Nnjeim\World\Models\Traits\CurrencyRelations;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Currency
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string $code
 * @property int $precision
 * @property string $symbol
 * @property string $symbol_native
 * @property bool $symbol_first
 * @property string $decimal_mark
 * @property string $thousands_separator
 *
 * @property-read Model|Country|null $country
 * @property-read Model[]|ExchangeRate[] $exchangeRates
 */
class Currency extends Model
{
	use CurrencyRelations;
    use WorldConnection;

	protected $fillable = [
		'country_id',
		'name',
		'code',
		'precision',
		'symbol',
		'symbol_native',
		'symbol_first',
		'decimal_mark',
		'thousands_separator',
	];

	public $timestamps = false;

    protected function casts(): array
    {
        return [
            'country_id' => 'int',
            'precision' => 'int',
            'symbol_first' => 'bool',
        ];
    }

	/**
	 * Get the table associated with the model.
	 *
	 * @return string
	 */
	public function getTable(): string
	{
		return config('world.migrations.currencies.table_name', parent::getTable());
	}
}
