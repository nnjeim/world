<?php

namespace Nnjeim\World\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
	protected $fillable = [
		'code',
		'name',
		'name_native',
		'dir',
	];

	public $timestamps = false;
}
