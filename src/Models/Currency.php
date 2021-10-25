<?php

namespace Nnjeim\World\Models;

use Nnjeim\World\Models\Traits\CurrencyRelations;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
	use CurrencyRelations;

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
}
