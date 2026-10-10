<?php

namespace Nnjeim\World\Database\Migrations;

use Illuminate\Database\Migrations\Migration;

class BaseMigration extends Migration
{
    public function __construct()
    {
        $this->connection ??= config('world.connection');
    }

    /**
     * World tables can contain application references and locally edited data.
     * Keep them, including source tracking, when a migration batch is rolled back.
     * Removing the package schema requires an explicit application migration.
     */
    public function down(): void
    {
        // Intentionally retain tables on rollback, whether created here or adopted.
    }
}
