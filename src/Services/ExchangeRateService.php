<?php

namespace Nnjeim\World\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Nnjeim\World\Models\Currency;
use Nnjeim\World\Models\ExchangeRate;

class ExchangeRateService
{
	protected string $provider;
	protected ?string $apiKey;
	protected ?string $apiUrl;
	protected string $baseCurrency;

	public function __construct()
	{
		$this->provider = config('world.exchange_rates.provider', 'exchangerate-api');
		$this->apiKey = config('world.exchange_rates.api_key');
		$this->apiUrl = config('world.exchange_rates.api_url');
		$this->baseCurrency = config('world.exchange_rates.base_currency', 'USD');
	}

	/**
	 * Fetch and update exchange rates for all currencies
	 *
	 * @return array
	 */
	public function updateExchangeRates(): array
	{
		if (!config('world.exchange_rates.enabled')) {
			return [
				'success' => false,
				'message' => 'Exchange rates are disabled. Set WORLD_EXCHANGE_RATES_ENABLED=true in your .env file.',
			];
		}

		try {
			$rates = $this->fetchRates();
			
			if (empty($rates)) {
				return [
					'success' => false,
					'message' => 'No exchange rates were fetched from the API.',
				];
			}

			$updated = 0;
			$currencies = Currency::all();

			foreach ($currencies as $currency) {
				$currencyCode = strtoupper($currency->code);
				
				// Skip base currency (rate is always 1.0)
				if ($currencyCode === $this->baseCurrency) {
					continue;
				}

				// Get rate for this currency
				$rate = $this->getRateForCurrency($rates, $currencyCode);
				
				if ($rate !== null) {
					ExchangeRate::create([
						'currency_id' => $currency->id,
						'exchange_rate' => $rate,
						'base_currency' => $this->baseCurrency,
					]);
					$updated++;
				}
			}

			// Also create rate for base currency (1.0)
			$baseCurrency = Currency::where('code', $this->baseCurrency)->first();
			if ($baseCurrency) {
				ExchangeRate::create([
					'currency_id' => $baseCurrency->id,
					'exchange_rate' => 1.0,
					'base_currency' => $this->baseCurrency,
				]);
			}

			return [
				'success' => true,
				'message' => "Successfully updated {$updated} exchange rates.",
				'count' => $updated,
			];
		} catch (Exception $e) {
			Log::error('Exchange rate update failed: ' . $e->getMessage());
			
			return [
				'success' => false,
				'message' => 'Failed to update exchange rates: ' . $e->getMessage(),
			];
		}
	}

	/**
	 * Fetch rates from the configured API provider
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function fetchRates(): array
	{
		return match ($this->provider) {
			'exchangerate-api' => $this->fetchFromExchangeRateApi(),
			'fixer' => $this->fetchFromFixer(),
			'currencylayer' => $this->fetchFromCurrencyLayer(),
			'custom' => $this->fetchFromCustomApi(),
			default => throw new Exception("Unsupported exchange rate provider: {$this->provider}"),
		};
	}

	/**
	 * Fetch rates from exchangerate-api.com (free tier available)
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function fetchFromExchangeRateApi(): array
	{
		$url = $this->apiUrl ?: 'https://api.exchangerate-api.com/v4/latest/' . $this->baseCurrency;
		
		$response = Http::timeout(30)->get($url);

		if (!$response->successful()) {
			throw new Exception("ExchangeRate-API request failed: " . $response->body());
		}

		$data = $response->json();

		if (!isset($data['rates']) || !is_array($data['rates'])) {
			throw new Exception("Invalid response format from ExchangeRate-API");
		}

		return $data['rates'];
	}

	/**
	 * Fetch rates from Fixer.io
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function fetchFromFixer(): array
	{
		if (!$this->apiKey) {
			throw new Exception("API key is required for Fixer.io provider");
		}

		$url = $this->apiUrl ?: 'https://api.fixer.io/latest';
		
		$response = Http::timeout(30)
			->get($url, [
				'access_key' => $this->apiKey,
				'base' => $this->baseCurrency,
			]);

		if (!$response->successful()) {
			throw new Exception("Fixer.io request failed: " . $response->body());
		}

		$data = $response->json();

		if (!isset($data['success']) || !$data['success']) {
			throw new Exception("Fixer.io API error: " . ($data['error']['info'] ?? 'Unknown error'));
		}

		return $data['rates'] ?? [];
	}

	/**
	 * Fetch rates from CurrencyLayer
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function fetchFromCurrencyLayer(): array
	{
		if (!$this->apiKey) {
			throw new Exception("API key is required for CurrencyLayer provider");
		}

		$url = $this->apiUrl ?: 'https://api.currencylayer.com/live';
		
		$response = Http::timeout(30)
			->get($url, [
				'access_key' => $this->apiKey,
				'source' => $this->baseCurrency,
			]);

		if (!$response->successful()) {
			throw new Exception("CurrencyLayer request failed: " . $response->body());
		}

		$data = $response->json();

		if (!isset($data['success']) || !$data['success']) {
			throw new Exception("CurrencyLayer API error: " . ($data['error']['info'] ?? 'Unknown error'));
		}

		// CurrencyLayer returns rates in format: "USDGBP": 0.75
		// We need to convert to: "GBP": 0.75
		$rates = [];
		foreach ($data['quotes'] ?? [] as $pair => $rate) {
			$currency = str_replace($this->baseCurrency, '', $pair);
			$rates[$currency] = $rate;
		}

		return $rates;
	}

	/**
	 * Fetch rates from a custom API endpoint
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function fetchFromCustomApi(): array
	{
		if (!$this->apiUrl) {
			throw new Exception("API URL is required for custom provider");
		}

		$params = [];
		if ($this->apiKey) {
			$params['api_key'] = $this->apiKey;
		}
		$params['base'] = $this->baseCurrency;

		$response = Http::timeout(30)->get($this->apiUrl, $params);

		if (!$response->successful()) {
			throw new Exception("Custom API request failed: " . $response->body());
		}

		$data = $response->json();

		// Expect custom API to return rates in 'rates' key
		if (!isset($data['rates']) || !is_array($data['rates'])) {
			throw new Exception("Invalid response format from custom API. Expected 'rates' array.");
		}

		return $data['rates'];
	}

	/**
	 * Get the exchange rate for a specific currency code
	 *
	 * @param array $rates
	 * @param string $currencyCode
	 * @return float|null
	 */
	protected function getRateForCurrency(array $rates, string $currencyCode): ?float
	{
		return $rates[$currencyCode] ?? null;
	}
}

