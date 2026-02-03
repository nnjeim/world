<?php

namespace Nnjeim\World\Actions\Geolocate;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Nnjeim\World\Actions\ActionInterface;
use Nnjeim\World\Actions\BaseAction;
use Nnjeim\World\Actions\Geolocate\Transformers\IndexTransformer;
use Nnjeim\World\Geolocate\Exceptions\DatabaseNotFoundException;
use Nnjeim\World\Geolocate\Exceptions\GeolocateException;
use Nnjeim\World\Geolocate\GeolocateService;
use Nnjeim\World\Http\Response\ResponseBuilder;

class IndexAction extends BaseAction implements ActionInterface
{
    use IndexTransformer;

    protected string $cacheTag = 'geolocate';

    protected string $attribute = 'geolocate';

    protected GeolocateService $geolocateService;

    protected ?string $errorMessage = null;

    public function __construct(GeolocateService $geolocateService)
    {
        $this->geolocateService = $geolocateService;
    }

    /**
     * Execute the geolocation action.
     *
     * @param array $args
     * @return $this
     */
    public function execute(array $args = []): self
    {
        [
            'ip' => $ip,
        ] = $args + [
            'ip' => null,
        ];

        // Get IP from middleware or detect from request if not provided
        if (empty($ip)) {
            $ip = $this->geolocateService->resolveClientIp();
        }

        // Check if we already have a resolved geolocation for this request
        // This avoids redundant lookups when called multiple times
        $resolvedIp = $this->geolocateService->getResolvedGeolocate()['ip'] ?? null;
        if ($this->geolocateService->hasResolvedGeolocate() && $resolvedIp === $ip) {
            $this->data = collect($this->geolocateService->getResolvedGeolocate());
            $this->success = true;

            return $this;
        }

        $this->cacheKey = "geolocate_{$ip}_" . app()->getLocale();

        try {
            // DEBUG: Bypass cache temporarily
            Cache::forget($this->cacheKey);

            // Cache the results
            $cacheTtl = config('world.geolocate.cache_ttl', 86400);

            $this->data = Cache::remember(
                $this->cacheKey,
                $cacheTtl,
                fn () => $this->performGeolocation($ip)
            );

            $this->success = ! empty($this->data) && $this->data->isNotEmpty();

            // Store the resolved geolocation for reuse within this request
            if ($this->success) {
                $this->geolocateService->setResolvedGeolocate($this->data->toArray());
            }
        } catch (DatabaseNotFoundException $e) {
            $this->success = false;
            $this->data = collect([]);
            $this->errorMessage = $e->getMessage();
        } catch (GeolocateException $e) {
            $this->success = false;
            $this->data = collect([]);
            $this->errorMessage = $e->getMessage();
        } catch (Exception $e) {
            $this->success = false;
            $this->data = collect([]);
            // Show detailed message in debug mode, generic message in production
            $this->errorMessage = config('app.debug', false)
                ? $e->getMessage()
                : 'An error occurred while geolocating the IP address.';
        }

        return $this;
    }

    /**
     * Build the response.
     *
     * @return ResponseBuilder
     */
    public function withResponse(): ResponseBuilder
    {
        $response = ResponseBuilder::make()
            ->setSuccess($this->success)
            ->setData($this->data)
            ->setStatusCode(
                $this->success
                    ? ResponseBuilder::HTTP_OK
                    : ResponseBuilder::HTTP_NOT_FOUND
            );

        if ($this->success) {
            $response->setAttributeMessage($this->attribute, true);
        } else {
            $defaultMessage = trans('world::response.errors.record_not_found', ['attribute' => $this->attribute]);
            $response->setMessage($this->errorMessage ?? $defaultMessage);
            $response->setErrors(['message' => [$this->errorMessage ?? $defaultMessage]]);
        }

        return $response;
    }

    /**
     * Perform the actual geolocation lookup.
     *
     * @param string $ip
     * @return Collection
     * @throws DatabaseNotFoundException
     * @throws GeolocateException
     */
    protected function performGeolocation(string $ip): Collection
    {
        $geoData = $this->geolocateService->geolocate($ip);

        // DEBUG: Log what we're getting from geolocate
        \Log::info('Geolocate debug', [
            'ip' => $ip,
            'geoData' => $geoData,
            'countryModel' => config('world.models.countries'),
        ]);

        try {
            $result = $this->transform($geoData);

            // DEBUG: Log transformed result
            \Log::info('Transform result', ['result' => $result->toArray()]);

            return $result;
        } catch (Exception $e) {
            // Re-throw with context so we can debug transform failures
            throw new GeolocateException("Transform failed: {$e->getMessage()}");
        }
    }
}
