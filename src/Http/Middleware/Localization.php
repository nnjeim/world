<?php

namespace Nnjeim\World\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class Localization
{
	/**
	 * @param  Request  $request
	 * @param  Closure  $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next): mixed
	{
		$requestLocale = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? config('app.fallback_locale'), 0, 2);

		$setLocale = in_array($requestLocale, config('world.accepted_locales'), true)
			? $requestLocale
			: config('app.fallback_locale');

		app()->setLocale($setLocale);

		return $next($request);
	}
}
