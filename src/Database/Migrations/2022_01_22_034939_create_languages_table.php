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
		Schema::create(config('world.migrations.languages.table_name'), function (Blueprint $table) {
			$table->id();
			$table->char('code', 2);
			$table->string('name');
			$table->string('name_native');
			$table->char('dir', 3);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists(config('world.migrations.languages.table_name'));
	}
};
