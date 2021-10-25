<?php

namespace Database\Seeders;

use Nnjeim\World\Actions\SeedAction;

use Illuminate\Database\Seeder;

class WorldSeeder extends Seeder
{
    public function run() {
        app(SeedAction::class)->execute();
    }
}
