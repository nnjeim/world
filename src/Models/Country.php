<?php

namespace Nnjeim\World\Models;

use Nnjeim\World\Models\Traits\WorldConnection;
use Nnjeim\World\Models\Traits\CountryRelations;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 *
 * @property int $id
 * @property string $iso2
 * @property string $name
 * @property int $status
 * @property string|null $phone_code
 * @property string|null $iso3
 * @property string|null $region
 * @property string|null $subregion
 * @property string|null $native
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $emoji
 * @property string|null $emojiU
 *
 * @property-read Model[]|State[] $states
 * @property-read Model[]|City[] $cities
 * @property-read Model[]|Timezone[] $timezones
 * @property-read Model|Currency|null $currency
 */
class Country extends Model
{
	use CountryRelations;
    use WorldConnection;

	protected $guarded = [];

	public $timestamps = false;

    protected function casts(): array
    {
        return [
            'status' => 'int',
			'timezones' => 'array',
            'translations' => 'array',
        ];
    }

	/**
	 * Get the table associated with the model.
	 *
	 * @return string
	 */
	public function getTable(): string
	{
		return config('world.migrations.countries.table_name', parent::getTable());
	}
}
