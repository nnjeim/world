<?php

namespace Nnjeim\World\Tests\Unit;

use Nnjeim\World\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SearchFilterTest extends TestCase
{
	#[DataProvider('searchCases')]
	public function test_search_does_not_return_records_outside_filters(
		string $module,
		array $matchingFilters,
		array $excludingFilters,
		array $searches,
		int $expectedId
	): void {
		foreach ($searches as $search) {
			$action = app('world')->{$module}([
				'filters' => $excludingFilters,
				'search' => $search,
			]);

			self::assertSame([], $action->data->all());
		}
	}

	#[DataProvider('searchCases')]
	public function test_search_matches_either_field_within_filters(
		string $module,
		array $matchingFilters,
		array $excludingFilters,
		array $searches,
		int $expectedId
	): void {
		foreach ($searches as $search) {
			$action = app('world')->{$module}([
				'filters' => $matchingFilters,
				'search' => $search,
			]);

			self::assertSame([$expectedId], $action->data->pluck('id')->all());
		}
	}

	#[DataProvider('searchCases')]
	public function test_search_without_filters_matches_either_field(
		string $module,
		array $matchingFilters,
		array $excludingFilters,
		array $searches,
		int $expectedId
	): void {
		foreach ($searches as $search) {
			$action = app('world')->{$module}(['search' => $search]);

			self::assertSame([$expectedId], $action->data->pluck('id')->all());
		}
	}

	#[DataProvider('searchCases')]
	public function test_empty_search_still_respects_filters(
		string $module,
		array $matchingFilters,
		array $excludingFilters,
		array $searches,
		int $expectedId
	): void {
		$action = app('world')->{$module}([
			'filters' => $matchingFilters,
			'search' => '',
		]);

		self::assertSame([$expectedId], $action->data->pluck('id')->all());
	}

	public static function searchCases(): array
	{
		return [
			'countries' => ['countries', ['iso2' => 'RO'], ['iso2' => 'FR'], ['RO', 'Romania'], 183],
			'currencies' => ['currencies', ['country_id' => 182], ['country_id' => 183], ['Euro', 'EUR'], 1],
			'languages' => ['languages', ['code' => 'fr'], ['code' => 'en'], ['fr', 'French'], 1],
		];
	}
}
