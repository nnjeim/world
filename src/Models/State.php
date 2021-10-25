<?php

namespace Nnjeim\World\Models;

use Nnjeim\World\Models\Traits\StateRelations;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
	use StateRelations;

	protected $fillable = [
		'country_id',
		'name',
	];

	public $timestamps = false;
}
