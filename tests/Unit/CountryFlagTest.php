<?php

namespace Nnjeim\World\Tests\Unit;

use Nnjeim\World\CountryFlag;
use Nnjeim\World\Tests\TestCase;
use Nnjeim\World\World;

class CountryFlagTest extends TestCase
{
    public function test_flags_are_computed_without_an_optional_database_column(): void
    {
        $country = World::countries(['fields' => 'iso2,flag', 'filters' => ['iso2' => 'FR']])->data->first();
        self::assertSame('🇫🇷', $country['flag']);
        self::assertSame('FR', $country['iso2']);
        self::assertCount(2, World::countries(['fields' => 'flag', 'filters' => ['flag' => '🇫🇷']])->data);
        self::assertArrayNotHasKey('flag', World::countries()->data->first());
    }

    public function test_country_endpoint_can_return_flags(): void
    {
        \Illuminate\Support\Facades\Route::get('/world-test/countries', [\Nnjeim\World\Http\Controllers\Country\CountryController::class, 'index']);
        $this->getJson('/world-test/countries?fields=iso2,flag&filters[iso2]=FR')
            ->assertOk()->assertJsonPath('data.0.iso2', 'FR')->assertJsonPath('data.0.flag', '🇫🇷');
    }

    public function test_helper_handles_lowercase_and_rejects_invalid_codes(): void
    {
        self::assertSame('🇰🇾', CountryFlag::emoji('ky'));
        foreach (['', 'FRA', '12', '<script>'] as $invalid) {
            self::assertNull(CountryFlag::emoji($invalid));
        }
    }
}
