<?php

namespace Nnjeim\World\Http\Controllers\Geolocate;

use Nnjeim\World\Http\Controllers\BaseController;
use Nnjeim\World\Http\Middleware\Geolocate;

class GeolocateController extends BaseController
{
    protected string $requestBasePath = 'Nnjeim\\World\\Http\\Requests\\Geolocate';

    protected string $actionBasePath = 'Nnjeim\\World\\Actions\\Geolocate';

    public function __construct()
    {
        $this->middleware(Geolocate::class);
    }
}
