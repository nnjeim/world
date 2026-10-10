<?php

use Nnjeim\World\Database\Migrations\BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if (Schema::hasTable(config('world.migrations.languages.table_name'))) {
			return;
		}

		Schema::create(config('world.migrations.languages.table_name'), function (Blueprint $table) {
			$table->id();
			$table->char('code', 2);
			$table->string('name');
			$table->string('name_native');
			$table->char('dir', 3);
		});
	}
};
