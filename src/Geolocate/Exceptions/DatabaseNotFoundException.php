<?php

namespace Nnjeim\World\Geolocate\Exceptions;

use Exception;

class DatabaseNotFoundException extends Exception
{
    public static function notFound(string $path): self
    {
        return new self(
            "GeoIP database not found at '{$path}'. " .
            "Please run 'php artisan world:geoip' to download the database, " .
            "or manually download GeoLite2-City.mmdb from MaxMind."
        );
    }
}
