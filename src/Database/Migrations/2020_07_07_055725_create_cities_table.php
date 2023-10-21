<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if(config('world.modules.cities')){
			Schema::create(config('world.migrations.cities.table_name'), function (Blueprint $table) {
				$table->id();
				$table->foreignId('country_id');
				$table->foreignId('state_id');
				$table->string('name');

				foreach (config('world.migrations.cities.optional_fields') as $field => $value) {
					if ($value['required']) {
						$table->string($field, $value['length'] ?? null);
					}
				}
			});
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if(config('world.modules.cities')){
			Schema::dropIfExists(config('world.migrations.cities.table_name'));
		}
	}
}
