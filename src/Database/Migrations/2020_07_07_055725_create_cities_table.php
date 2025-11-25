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
        Schema::create(config('world.migrations.cities.table_name'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('state_id')->constrained('states')->cascadeOnDelete()->cascadeOnUpdate();
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
     */
    public function down(): void
    {
        Schema::dropIfExists(config('world.migrations.cities.table_name'));
    }
};
