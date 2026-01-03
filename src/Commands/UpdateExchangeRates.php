<?php

namespace Nnjeim\World\Commands;

use Illuminate\Console\Command;
use Nnjeim\World\Services\ExchangeRateService;

class UpdateExchangeRates extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'world:update-exchange-rates';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update exchange rates for all currencies from the configured API provider';

	/**
	 * Execute the console command.
	 */
	public function handle(ExchangeRateService $service): int
	{
		$this->info('Updating exchange rates...');

		$result = $service->updateExchangeRates();

		if ($result['success']) {
			$this->info($result['message']);
			$this->info("Updated {$result['count']} exchange rates.");
			return Command::SUCCESS;
		}

		$this->error($result['message']);
		return Command::FAILURE;
	}
}

