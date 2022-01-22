<?php

namespace Nnjeim\World\Tests\Unit;

use Nnjeim\World\Actions\City;
use Nnjeim\World\Actions\Country;
use Nnjeim\World\Actions\Currency;
use Nnjeim\World\Actions\State;
use Nnjeim\World\Actions\Timezone;
use Nnjeim\World\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class CountryTest extends TestCase
{
	/** @test */
	public function can_respond_with_countries()
	{
		$action = app(Country\IndexAction::class)->execute();

		self::assertTrue($action->success === true);
		self::assertNotEmpty($action->data);
		self::assertTrue($action->statusCode === Response::HTTP_OK);
	}

	/** @test */
	public function can_respond_with_country()
	{
		$action = app(Country\IndexAction::class)->execute([
			'filters' => [
				'iso2' => 'FR',
			],
		]);

		self::assertTrue($action->success === true);
		self::assertNotEmpty($action->data);
		self::assertTrue($action->statusCode === Response::HTTP_OK);
	}

	/** @test */
	public function can_respond_with_states()
	{
		$action = app(State\IndexAction::class)->execute([
			'filters' => [
				'country_id' => 182,
			],
		]);

		self::assertTrue($action->success === true);
		self::assertNotEmpty($action->data);
		self::assertTrue($action->statusCode === Response::HTTP_OK);
	}

	/** @test */
	public function can_respond_with_cities()
	{
		$action = app(City\IndexAction::class)->execute([
			'filters' => [
				'country_id' => 182,
			],
		]);

		self::assertTrue($action->success === true);
		self::assertNotEmpty($action->data);
		self::assertTrue($action->statusCode === Response::HTTP_OK);
	}

	/** @test */
	public function can_respond_with_timezones()
	{
		$action = app(Timezone\IndexAction::class)->execute([
			'filters' => [
				'country_id' => 182,
			],
		]);

		self::assertTrue($action->success === true);
		self::assertNotEmpty($action->data);
		self::assertTrue($action->statusCode === Response::HTTP_OK);
	}

	/** @test */
	public function can_respond_with_currencies()
	{
		$action = app(Currency\IndexAction::class)->execute([
			'filters' => [
				'country_id' => 182,
			],
		]);

		self::assertTrue($action->success === true);
		self::assertNotEmpty($action->data);
		self::assertTrue($action->statusCode === Response::HTTP_OK);
	}
}
