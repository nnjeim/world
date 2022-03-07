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

	/**
	 * Get the table associated with the model.
	 *
	 * @return string
	 */
	public function getTable(): string
	{
		return config('world.migrations.languages.table_name', parent::getTable());
	}
}
