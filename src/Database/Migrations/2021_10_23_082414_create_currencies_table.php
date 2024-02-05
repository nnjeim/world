<?php

use \Nnjeim\World\Database\Migrations\BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends BaseMigration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(config('world.migrations.currencies.table_name'), function (Blueprint $table) {
			$table->id();
			$table->foreignId('country_id');
			$table->string('name');
			$table->string('code');
			$table->tinyInteger('precision')->default(2);
			$table->string('symbol');
			$table->string('symbol_native');
			$table->tinyInteger('symbol_first')->default(1);
			$table->string('decimal_mark', 1)->default('.');
			$table->string('thousands_separator', 1)->default(',');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists(config('world.migrations.currencies.table_name'));
	}
}
