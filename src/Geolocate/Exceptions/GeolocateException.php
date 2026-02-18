<?php

namespace Nnjeim\World\Geolocate\Exceptions;

use Exception;

class GeolocateException extends Exception
{
    public static function addressNotFound(string $ip): self
    {
        return new self("The IP address '{$ip}' could not be found in the database.");
    }

    public static function invalidIp(string $ip): self
    {
        return new self("The IP address '{$ip}' is not a valid IP address.");
    }

    public static function privateIp(string $ip): self
    {
        return new self("The IP address '{$ip}' is a private or reserved IP address.");
    }
}
