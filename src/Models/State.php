<?php

namespace Nnjeim\World\Models;

use Nnjeim\World\Models\Traits\StateRelations;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
	use StateRelations;

	protected $guarded = [];

	public $timestamps = false;

	/**
	 * Get the table associated with the model.
	 *
	 * @return string
	 */
	public function getTable(): string
	{
		return config('world.migrations.states.table_name', parent::getTable());
	}
}
