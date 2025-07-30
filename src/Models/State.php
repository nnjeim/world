<?php

namespace Nnjeim\World\Models;

use Nnjeim\World\Models\Traits\StateRelations;

use Illuminate\Database\Eloquent\Model;
use Nnjeim\World\Models\Traits\WorldConnection;

/**
 * Class State
 *
 * @property int $id
 * @property string $name
 * @property string|null $code
 * @property int|null $country_id
 * @property string|null $country_code
 * @property string|null $state_code
 * @property string|null $type
 * @property string|null $latitude
 * @property string|null $longitude
 *
 * @property-read Model|Country|null $country
 * @property-read Model[]|City[] $cities
 */
class State extends Model
{
	use StateRelations;
    use WorldConnection;

	protected $guarded = [];

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
		return config('world.migrations.states.table_name', parent::getTable());
	}
}
