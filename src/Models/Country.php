<?php

namespace Nnjeim\World\Models;

use Nnjeim\World\Models\Traits\CountryRelations;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
	use CountryRelations;

	protected $fillable = [
		'iso2',
		'iso3',
		'name',
		'phone_code',
		'dialling_pattern',
		'region',
		'sub_region',
		'status',
	];

	public $timestamps = false;
}
