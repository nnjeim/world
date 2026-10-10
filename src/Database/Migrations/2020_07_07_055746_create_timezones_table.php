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
        if (Schema::hasTable(config('world.migrations.timezones.table_name'))) {
            return;
        }

        Schema::create(config('world.migrations.timezones.table_name'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id');
            $table->string('name');
        });
    }
};
