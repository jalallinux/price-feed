<?php

namespace JalalLinuX\PriceFeed\Drivers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use JalalLinuX\PriceFeed\Contracts\DriverInterface;
use JalalLinuX\PriceFeed\DataTransferObjects\PriceData;
use JalalLinuX\PriceFeed\Enums\Currency;
use JalalLinuX\PriceFeed\Exceptions\ApiException;

abstract class AbstractDriver implements DriverInterface
{
    protected array $config;

    protected string $baseUrl;

    protected ?string $apiKey;

    protected array $options;

    protected array $supportedCurrencies;

    protected int $cacheTtl;

    protected bool $cacheEnabled;

    protected string $cachePrefix;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->baseUrl = $config['base_url'];
        $this->apiKey = $config['api_key'] ?? null;
        $this->options = $config['options'] ?? [];
        $this->supportedCurrencies = $config['currencies'] ?? [];

        // Cache configuration (all from driver config)
        $this->cacheEnabled = $config['cache_enabled'] ?? true;
        $this->cacheTtl = $config['cache_ttl'] ?? 60;
        $this->cachePrefix = $config['cache_prefix'] ?? 'price_feed';
    }

    /**
     * Get the HTTP client instance
     */
    protected function getHttpClient(): PendingRequest
    {
        $client = Http::baseUrl($this->baseUrl)
            ->timeout($this->options['timeout'] ?? 10)
            ->retry(3, 100);

        if ($this->apiKey) {
            $client = $this->withAuthentication($client);
        }

        return $client;
    }

    /**
     * Add authentication to the HTTP client
     * Override this method to customize authentication
     */
    protected function withAuthentication(PendingRequest $client): PendingRequest
    {
        return $client->withHeaders([
            'X-API-Key' => $this->apiKey,
        ]);
    }

    /**
     * Get prices for multiple currencies
     */
    public function getPrices(array $currencies): Collection
    {
        return collect($currencies)->mapWithKeys(function (Currency $currency) {
            return [$currency->value => $this->getPrice($currency)];
        });
    }

    /**
     * Get all supported currencies for this driver
     */
    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    /**
     * Check if the driver supports a specific currency
     */
    public function supports(Currency $currency): bool
    {
        return in_array($currency, $this->supportedCurrencies);
    }

    /**
     * Get the driver name
     */
    public function getName(): string
    {
        return class_basename($this);
    }

    /**
     * Handle API errors
     */
    protected function handleApiError(\Throwable $e, string $message = 'API request failed'): never
    {
        throw new ApiException("{$message}: {$e->getMessage()}", $e->getCode(), $e);
    }

    /**
     * Generate cache key for a currency
     */
    protected function getCacheKey(Currency $currency): string
    {
        return "{$this->cachePrefix}:{$this->getName()}:{$currency->value}";
    }

    /**
     * Get price from cache or fetch from API
     */
    protected function getCachedPrice(Currency $currency, callable $callback): PriceData
    {
        if (! $this->cacheEnabled) {
            return $callback();
        }

        $cacheKey = $this->getCacheKey($currency);

        return Cache::remember($cacheKey, $this->cacheTtl, $callback);
    }

    /**
     * Get the price for a single currency
     * Must be implemented by concrete drivers
     */
    abstract public function getPrice(Currency $currency): PriceData;
}
