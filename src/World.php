<?php

namespace Nnjeim\World;

use Illuminate\Support\Facades\Facade;

class World extends Facade
{
	/**
	 * Get the registered name of the component.
	 */
	protected static function getFacadeAccessor(): string
	{
		return 'world';
	}
}
