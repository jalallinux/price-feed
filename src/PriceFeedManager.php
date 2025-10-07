<?php

namespace JalalLinuX\PriceFeed;

use Illuminate\Support\Collection;
use Illuminate\Support\Manager;
use JalalLinuX\PriceFeed\Contracts\DriverInterface;
use JalalLinuX\PriceFeed\DataTransferObjects\PriceData;
use JalalLinuX\PriceFeed\Enums\Currency;
use JalalLinuX\PriceFeed\Exceptions\DriverNotFoundException;
use JalalLinuX\PriceFeed\Exceptions\UnsupportedCurrencyException;

class PriceFeedManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('price-feed.default');
    }

    /**
     * Create a driver instance.
     */
    protected function createDriver($driver): DriverInterface
    {
        $config = $this->config->get("price-feed.drivers.{$driver}");

        if (! $config) {
            throw new DriverNotFoundException("Driver [{$driver}] is not configured.");
        }

        $driverClass = $config['driver'];

        if (! class_exists($driverClass)) {
            throw new DriverNotFoundException("Driver class [{$driverClass}] not found.");
        }

        return new $driverClass($config);
    }

    /**
     * Get the price for a single currency
     */
    public function getPrice(Currency $currency, ?string $driver = null): PriceData
    {
        $driverInstance = $driver ? $this->driver($driver) : $this->driver();

        if (! $driverInstance->supports($currency)) {
            throw new UnsupportedCurrencyException(
                "Currency [{$currency->value}] is not supported by driver [{$driverInstance->getName()}]"
            );
        }

        return $this->withCache($driver ?? $this->getDefaultDriver(), $currency, function () use ($driverInstance, $currency) {
            return $driverInstance->getPrice($currency);
        });
    }

    /**
     * Get prices for multiple currencies
     */
    public function getPrices(array $currencies, ?string $driver = null): Collection
    {
        $driverInstance = $driver ? $this->driver($driver) : $this->driver();

        return $driverInstance->getPrices($currencies);
    }

    /**
     * Get all supported currencies for a driver
     */
    public function getSupportedCurrencies(?string $driver = null): array
    {
        $driverInstance = $driver ? $this->driver($driver) : $this->driver();

        return $driverInstance->getSupportedCurrencies();
    }

    /**
     * Get all configured drivers
     */
    public function getAvailableDrivers(): array
    {
        return array_keys($this->config->get('price-feed.drivers', []));
    }

    /**
     * Cache wrapper for price data
     */
    protected function withCache(string $driver, Currency $currency, \Closure $callback): PriceData
    {
        $cacheConfig = $this->config->get('price-feed.cache', []);

        if (! ($cacheConfig['enabled'] ?? false)) {
            return $callback();
        }

        $cacheKey = sprintf(
            '%s.%s.%s',
            $cacheConfig['prefix'] ?? 'price_feed',
            $driver,
            $currency->value
        );

        return $this->container->make('cache')->remember(
            $cacheKey,
            $cacheConfig['ttl'] ?? 60,
            $callback
        );
    }

    /**
     * Clear cache for a specific currency and driver
     */
    public function clearCache(?Currency $currency = null, ?string $driver = null): void
    {
        $cacheConfig = $this->config->get('price-feed.cache', []);
        $prefix = $cacheConfig['prefix'] ?? 'price_feed';

        if ($currency && $driver) {
            $cacheKey = sprintf('%s.%s.%s', $prefix, $driver, $currency->value);
            $this->container->make('cache')->forget($cacheKey);
        } elseif ($driver) {
            // Clear all currencies for this driver
            foreach (Currency::cases() as $curr) {
                $cacheKey = sprintf('%s.%s.%s', $prefix, $driver, $curr->value);
                $this->container->make('cache')->forget($cacheKey);
            }
        } else {
            // Clear all cache
            $this->container->make('cache')->flush();
        }
    }
}
