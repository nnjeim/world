<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Nnjeim\World\Actions\SeedAction;

class WorldSeeder extends Seeder
{
	public function run()
	{
		$this->call([
			SeedAction::class,
		]);
	}
}
