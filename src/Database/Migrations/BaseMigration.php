<?php

namespace Nnjeim\World\Database\Migrations;

use Illuminate\Database\Migrations\Migration;

class BaseMigration extends Migration
{
    public function __construct()
    {
        $this->connection ??= config('world.connection');
    }
}
