<?php

namespace Nnjeim\World\Models;

use Nnjeim\World\Models\Traits\CityRelations;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
	use CityRelations;

	protected $fillable = [
		'country_id',
		'state_id',
		'name',
	];

	public $timestamps = false;
}
