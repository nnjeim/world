<?php

namespace Nnjeim\World\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nnjeim\World\Geolocate\GeolocateService;

class Geolocate
{
    protected GeolocateService $geolocateService;

    public function __construct(GeolocateService $geolocateService)
    {
        $this->geolocateService = $geolocateService;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Extract and store the client IP for later use
        $ip = $this->geolocateService->getClientIp();
        $this->geolocateService->setClientIp($ip);

        return $next($request);
    }
}
