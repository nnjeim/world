<?php

namespace Nnjeim\World\Geolocate;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Support\Facades\Http;
use Nnjeim\World\Geolocate\Exceptions\DatabaseNotFoundException;
use Nnjeim\World\Geolocate\Exceptions\GeolocateException;

class GeolocateService
{
    protected ?Reader $reader = null;

    protected string $databasePath;

    /**
     * The client IP address resolved by middleware.
     */
    protected ?string $clientIp = null;

    /**
     * The cached geolocation result for the current request.
     */
    protected ?array $resolvedGeolocate = null;

    public function __construct()
    {
        $this->databasePath = config('world.geolocate.database_path', storage_path('app/geoip/GeoLite2-City.mmdb'));
    }

    /**
     * Set the client IP (called by middleware).
     *
     * @param string $ip
     * @return $this
     */
    public function setClientIp(string $ip): self
    {
        $this->clientIp = $ip;

        return $this;
    }

    /**
     * Get the stored client IP or detect it from the request.
     *
     * @return string
     */
    public function resolveClientIp(): string
    {
        return $this->clientIp ?? $this->getClientIp();
    }

    /**
     * Get the cached geolocation result.
     *
     * @return array|null
     */
    public function getResolvedGeolocate(): ?array
    {
        return $this->resolvedGeolocate;
    }

    /**
     * Set the cached geolocation result.
     *
     * @param array $geolocate
     * @return $this
     */
    public function setResolvedGeolocate(array $geolocate): self
    {
        $this->resolvedGeolocate = $geolocate;

        return $this;
    }

    /**
     * Check if geolocation has been resolved for this request.
     *
     * @return bool
     */
    public function hasResolvedGeolocate(): bool
    {
        return $this->resolvedGeolocate !== null;
    }

    /**
     * Get the GeoIP2 Reader instance.
     *
     * @throws DatabaseNotFoundException
     */
    protected function getReader(): Reader
    {
        if ($this->reader === null) {
            if (!file_exists($this->databasePath)) {
                throw DatabaseNotFoundException::notFound($this->databasePath);
            }

            $this->reader = new Reader($this->databasePath);
        }

        return $this->reader;
    }

    /**
     * Geolocate an IP address.
     *
     * @param string $ip
     * @return array
     * @throws GeolocateException
     */
    public function geolocate(string $ip): array
    {
        if (!$this->isValidIp($ip)) {
            throw GeolocateException::invalidIp($ip);
        }

        if ($this->isPrivateIp($ip)) {
            throw GeolocateException::privateIp($ip);
        }

        // Try local database first
        if ($this->databaseExists()) {
            return $this->geolocateWithDatabase($ip);
        }

        // Fallback to external API if enabled
        if (config('world.geolocate.fallback_api', true)) {
            return $this->geolocateWithApi($ip);
        }

        throw new GeolocateException(
            "GeoIP database not found. Run 'php artisan world:geoip' to download it, " .
            "or enable 'fallback_api' in config to use ip-api.com."
        );
    }

    /**
     * Geolocate using local MaxMind database.
     *
     * @param string $ip
     * @return array
     * @throws GeolocateException
     */
    protected function geolocateWithDatabase(string $ip): array
    {
        try {
            $record = $this->getReader()->city($ip);

            return [
                'ip' => $ip,
                'country_code' => $record->country->isoCode,
                'country_name' => $record->country->name,
                'state_code' => $record->mostSpecificSubdivision->isoCode,
                'state_name' => $record->mostSpecificSubdivision->name,
                'city_name' => $record->city->name,
                'postal_code' => $record->postal->code,
                'latitude' => $record->location->latitude,
                'longitude' => $record->location->longitude,
                'timezone' => $record->location->timeZone,
                'accuracy_radius' => $record->location->accuracyRadius,
            ];
        } catch (AddressNotFoundException $e) {
            throw GeolocateException::addressNotFound($ip);
        }
    }

    /**
     * Geolocate using ip-api.com (free, no license required).
     *
     * @param string $ip
     * @return array
     * @throws GeolocateException
     */
    protected function geolocateWithApi(string $ip): array
    {
        try {
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,message,country,countryCode,region,regionName,city,zip,lat,lon,timezone',
            ]);

            if (!$response->successful()) {
                throw GeolocateException::addressNotFound($ip);
            }

            $data = $response->json();

            if (($data['status'] ?? '') !== 'success') {
                throw GeolocateException::addressNotFound($ip);
            }

            return [
                'ip' => $ip,
                'country_code' => $data['countryCode'] ?? null,
                'country_name' => $data['country'] ?? null,
                'state_code' => $data['region'] ?? null,
                'state_name' => $data['regionName'] ?? null,
                'city_name' => $data['city'] ?? null,
                'postal_code' => $data['zip'] ?? null,
                'latitude' => $data['lat'] ?? null,
                'longitude' => $data['lon'] ?? null,
                'timezone' => $data['timezone'] ?? null,
                'accuracy_radius' => null,
            ];
        } catch (GeolocateException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new GeolocateException("Failed to geolocate IP using external API: {$e->getMessage()}");
        }
    }

    /**
     * Validate if the given string is a valid IP address.
     *
     * @param string $ip
     * @return bool
     */
    public function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Check if the IP address is private or reserved.
     *
     * @param string $ip
     * @return bool
     */
    public function isPrivateIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    /**
     * Extract the client IP from the request.
     *
     * @return string
     */
    public function getClientIp(): string
    {
        $request = request();

        // Check common proxy headers first
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',      // Standard proxy header
            'HTTP_X_REAL_IP',            // Nginx proxy
            'HTTP_CLIENT_IP',            // Generic
        ];

        foreach ($headers as $header) {
            if ($ip = $request->server($header)) {
                // X-Forwarded-For can contain multiple IPs, take the first
                $ip = trim(explode(',', $ip)[0]);
                if ($this->isValidIp($ip)) {
                    return $ip;
                }
            }
        }

        return $request->ip() ?? '127.0.0.1';
    }

    /**
     * Check if the GeoIP database exists.
     *
     * @return bool
     */
    public function databaseExists(): bool
    {
        return file_exists($this->databasePath);
    }

    /**
     * Get the database path.
     *
     * @return string
     */
    public function getDatabasePath(): string
    {
        return $this->databasePath;
    }
}
