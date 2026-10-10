<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Nnjeim\World\Database\Migrations\BaseMigration;

return new class extends BaseMigration
{
    public function up(): void
    {
        if (Schema::hasTable(config('world.migrations.data_records.table_name', 'world_data_records'))) {
            return;
        }

        Schema::create(config('world.migrations.data_records.table_name', 'world_data_records'), function (Blueprint $table) {
            $table->id();
            $table->string('module', 20);
            $table->string('source_key', 100);
            $table->string('scope', 2);
            $table->unsignedBigInteger('record_id');
            $table->text('attributes');
            $table->unique(['module', 'source_key']);
            $table->unique(['module', 'record_id']);
            $table->index(['module', 'scope']);
        });
    }
};
