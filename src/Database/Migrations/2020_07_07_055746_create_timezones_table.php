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
        Schema::create(config('world.migrations.timezones.table_name'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('world.migrations.timezones.table_name'));
    }
};
