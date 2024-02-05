<?php

use \Nnjeim\World\Database\Migrations\BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitiesTable extends BaseMigration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
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

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists(config('world.migrations.cities.table_name'));
	}
}
