<?php

namespace Nnjeim\World\Models;

use Nnjeim\World\Models\Traits\WorldConnection;
use Nnjeim\World\Models\Traits\CityRelations;

use Illuminate\Database\Eloquent\Model;

/**
 * Class City
 *
 * @property int $id
 * @property int $country_id
 * @property int $state_id
 * @property string $name
 * @property string $country_code
 * @property string|null $state_code
 * @property string|null $latitude
 * @property string|null $longitude
 *
 * @property-read Model|Country|null $country
 * @property-read Model|State|null $state
 */
class City extends Model
{
	use CityRelations;
    use WorldConnection;

	protected $guarded = [];

	public $timestamps = false;

    protected function casts(): array
    {
        return [
            'country_id' => 'int',
            'state_id' => 'int',
        ];
    }

	/**
	 * Get the table associated with the model.
	 *
	 * @return string
	 */
	public function getTable(): string
	{
		return config('world.migrations.cities.table_name', parent::getTable());
	}
}
