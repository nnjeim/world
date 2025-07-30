<?php

namespace Nnjeim\World\Models;

use Nnjeim\World\Models\Traits\WorldConnection;
use Nnjeim\World\Models\Traits\TimezoneRelations;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $country_id
 * @property string $name
 *
 * @property-read Model|Country|null $country
 */
class Timezone extends Model
{
	use TimezoneRelations;
    use WorldConnection;

	protected $fillable = [
		'country_id',
		'name',
	];

	public $timestamps = false;

    protected function casts(): array
    {
        return [
            'country_id' => 'int',
        ];
    }

	/**
	 * Get the table associated with the model.
	 *
	 * @return string
	 */
	public function getTable(): string
	{
		return config('world.migrations.timezones.table_name', parent::getTable());
	}
}
