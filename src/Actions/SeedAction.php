<?php

namespace Nnjeim\World\Actions;

use Illuminate\Database\Seeder;
use Nnjeim\World\Data\WorldDataset;
use Nnjeim\World\Data\WorldDataSynchronizer;

class SeedAction extends Seeder
{
    public function run(): void
    {
        app(WorldDataSynchronizer::class)->sync(app(WorldDataset::class));
        $this->command?->info('World data updated without deleting existing records.');
    }
}
