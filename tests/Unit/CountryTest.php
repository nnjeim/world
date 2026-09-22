<?php

namespace Nnjeim\World\Tests\Unit;

use Nnjeim\World\Actions\BaseAction;
use Nnjeim\World\Actions\City;
use Nnjeim\World\Actions\Country;
use Nnjeim\World\Actions\Currency;
use Nnjeim\World\Actions\Language;
use Nnjeim\World\Actions\State;
use Nnjeim\World\Actions\Timezone;
use Nnjeim\World\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class CountryTest extends TestCase
{
	public function test_can_respond_with_countries(): void
	{
		$this->assertSuccessfulAction(app(Country\IndexAction::class)->execute());
	}

	public function test_can_respond_with_a_filtered_country(): void
	{
		$action = app(Country\IndexAction::class)->execute([
			'fields' => 'iso2',
			'filters' => [
				'iso2' => 'FR',
			],
		]);

		$this->assertSuccessfulAction($action);
		self::assertSame('FR', $action->data->first()['iso2']);
	}

	public function test_can_respond_with_states(): void
	{
		$action = app(State\IndexAction::class)->execute([
			'filters' => [
				'country_id' => 182,
			],
		]);

		$this->assertSuccessfulAction($action);
	}

	public function test_can_respond_with_cities(): void
	{
		$action = app(City\IndexAction::class)->execute([
			'filters' => [
				'country_id' => 182,
			],
		]);

		$this->assertSuccessfulAction($action);
	}

	public function test_can_respond_with_timezones(): void
	{
		$action = app(Timezone\IndexAction::class)->execute([
			'filters' => [
				'country_id' => 182,
			],
		]);

		$this->assertSuccessfulAction($action);
	}

	public function test_can_respond_with_currencies(): void
	{
		$action = app(Currency\IndexAction::class)->execute([
			'filters' => [
				'country_id' => 182,
			],
		]);

		$this->assertSuccessfulAction($action);
	}

	public function test_can_respond_with_languages(): void
	{
		$this->assertSuccessfulAction(app(Language\IndexAction::class)->execute());
	}

	private function assertSuccessfulAction(BaseAction $action): void
	{
		self::assertTrue($action->success);
		self::assertNotEmpty($action->data);
		self::assertSame(Response::HTTP_OK, $action->withResponse()->statusCode);
	}
}
