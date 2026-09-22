<?php

namespace Nnjeim\World\Tests\Unit;

use Nnjeim\World\Tests\TestCase;

class DataIntegrityTest extends TestCase
{
	public function test_release_data_and_locales_are_internally_consistent(): void
	{
		$states = $this->loadJson('states.json');
		$cities = $this->loadJson('cities.json');
		$countries = $this->loadJson('countries.json');

		$this->assertUniqueIds($states, 'state');
		$this->assertUniqueIds($cities, 'city');
		$this->assertCityStateReferences($states, $cities);
		$this->assertAlgerianWilayas($states, $cities);
		$this->assertBulgarianEuroData($countries);
		$this->assertLocaleParity();
	}

	private function assertCityStateReferences(array $states, array $cities): void
	{
		$statesById = [];
		foreach ($states as $state) {
			$statesById[$state['id']] = $state;
		}

		$invalidCityIds = [];
		foreach ($cities as $city) {
			$state = $statesById[$city['state_id']] ?? null;
			if (
				$state === null
				|| $state['country_id'] !== $city['country_id']
				|| $state['name'] !== $city['state_name']
			) {
				$invalidCityIds[] = $city['id'];
			}
		}

		self::assertSame([], $invalidCityIds, 'Cities with invalid state references were found.');
	}

	private function assertUniqueIds(array $records, string $recordType): void
	{
		$ids = array_column($records, 'id');

		self::assertCount(
			count($ids),
			array_unique($ids, SORT_REGULAR),
			"Duplicate {$recordType} IDs found."
		);
	}

	private function assertAlgerianWilayas(array $states, array $cities): void
	{
		$algerianStates = [];
		foreach ($states as $state) {
			if ($state['country_code'] === 'DZ') {
				$algerianStates[$state['id']] = $state;
			}
		}

		$newCodes = [];
		$newStateCityCounts = [];
		foreach ($algerianStates as $state) {
			$code = (int) $state['state_code'];
			if ($code >= 59 && $code <= 69) {
				$newCodes[] = $code;
				$newStateCityCounts[$state['id']] = 0;
			}
		}

		sort($newCodes);
		self::assertSame(range(59, 69), $newCodes);

		foreach ($cities as $city) {
			if ($city['country_code'] !== 'DZ') {
				continue;
			}

			$state = $algerianStates[$city['state_id']] ?? null;
			self::assertNotNull($state, "Missing Algerian state for city {$city['id']}.");
			self::assertSame($state['state_code'], $city['state_code']);
			self::assertSame($state['name'], $city['state_name']);

			if (array_key_exists($city['state_id'], $newStateCityCounts)) {
				$newStateCityCounts[$city['state_id']]++;
			}
		}

		foreach ($newStateCityCounts as $stateId => $cityCount) {
			self::assertGreaterThan(0, $cityCount, "Algerian state {$stateId} has no cities.");
		}
	}

	private function assertBulgarianEuroData(array $countries): void
	{
		$bulgaria = current(array_filter(
			$countries,
			fn (array $country): bool => $country['iso2'] === 'BG'
		));

		self::assertIsArray($bulgaria);
		self::assertSame('EUR', $bulgaria['currency']);
		self::assertSame('Euro', $bulgaria['currency_name']);
		self::assertSame('€', $bulgaria['currency_symbol']);
	}

	private function assertLocaleParity(): void
	{
		$languageRoot = dirname(__DIR__, 2) . '/resources/lang';
		$english = require $languageRoot . '/en/country.php';
		$englishKeys = array_keys($english);
		sort($englishKeys);
		foreach (glob($languageRoot . '/*/country.php') as $translationFile) {
			$translation = require $translationFile;
			$translationKeys = array_keys($translation);
			sort($translationKeys);
			self::assertSame(
				$englishKeys,
				$translationKeys,
				basename(dirname($translationFile)) . ' country keys do not match English.'
			);
		}

		$localeDirectories = array_map(
			'basename',
			glob($languageRoot . '/*', GLOB_ONLYDIR)
		);
		sort($localeDirectories);

		$acceptedLocales = config('world.accepted_locales');
		sort($acceptedLocales);

		self::assertSame($acceptedLocales, $localeDirectories);
	}

	private function loadJson(string $filename): array
	{
		return json_decode(
			file_get_contents(dirname(__DIR__, 2) . '/resources/json/' . $filename),
			true,
			512,
			JSON_THROW_ON_ERROR
		);
	}
}
