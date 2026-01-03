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
		Schema::create(config('world.migrations.exchange_rates.table_name'), function (Blueprint $table) {
			$table->id();
			$table->foreignId('currency_id')->constrained(config('world.migrations.currencies.table_name'))->onDelete('cascade');
			$table->decimal('exchange_rate', 15, 6);
			$table->string('base_currency', 3)->default('USD');
			$table->timestamp('created_at')->useCurrent();
			$table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
			
			$table->index(['currency_id', 'created_at']);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists(config('world.migrations.exchange_rates.table_name'));
	}
};

