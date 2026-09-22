<?php

namespace Nnjeim\World\Tests\Unit;

use GeoIp2\Database\Reader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Nnjeim\World\Actions\Geolocate\IndexAction;
use Nnjeim\World\Geolocate\GeolocateService;
use Nnjeim\World\Tests\TestCase;

class GeolocateTest extends TestCase
{
	public function test_geoip2_v3_reader_is_available(): void
	{
		self::assertTrue(class_exists(Reader::class));
	}

	public function test_api_fallback_maps_geolocation_data(): void
	{
		$this->fakeGeolocationApi();

		$data = app(GeolocateService::class)->geolocate('8.8.8.8');

		self::assertSame('US', $data['country_code']);
		self::assertSame('Mountain View', $data['city_name']);
		self::assertSame(37.386, $data['latitude']);
	}

	public function test_geolocation_cache_stores_an_array_and_returns_a_collection(): void
	{
		Cache::flush();
		$this->fakeGeolocationApi();

		$action = new IndexAction(new GeolocateService());
		$action->execute(['ip' => '8.8.8.8']);

		self::assertInstanceOf(Collection::class, $action->data);
		self::assertIsArray(Cache::get('geolocate_8.8.8.8_en'));

		(new IndexAction(new GeolocateService()))->execute(['ip' => '8.8.8.8']);

		Http::assertSentCount(1);
	}

	private function fakeGeolocationApi(): void
	{
		config()->set('world.geolocate.database_path', storage_path('missing-GeoLite2-City.mmdb'));
		config()->set('world.geolocate.fallback_api', true);

		Http::fake([
			'ip-api.com/*' => Http::response([
				'status' => 'success',
				'country' => 'United States',
				'countryCode' => 'US',
				'region' => 'CA',
				'regionName' => 'California',
				'city' => 'Mountain View',
				'zip' => '94035',
				'lat' => 37.386,
				'lon' => -122.0838,
				'timezone' => 'America/Los_Angeles',
			], 200),
		]);
	}
}
