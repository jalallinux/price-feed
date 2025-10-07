<?php

namespace JalalLinuX\PriceFeed;

use Illuminate\Support\Collection;
use JalalLinuX\PriceFeed\DataTransferObjects\PriceData;
use JalalLinuX\PriceFeed\Enums\Currency;

class PriceFeed
{
    /**
     * Get price for a currency using the default driver
     */
    public static function price(Currency $currency): PriceData
    {
        return app('price-feed')->getPrice($currency);
    }

    /**
     * Get prices for multiple currencies using the default driver
     */
    public static function prices(array $currencies): Collection
    {
        return app('price-feed')->getPrices($currencies);
    }

    /**
     * Get all supported currencies for the default driver
     */
    public static function supportedCurrencies(): array
    {
        return app('price-feed')->getSupportedCurrencies();
    }

    /**
     * Get all available drivers
     */
    public static function availableDrivers(): array
    {
        return app('price-feed')->getAvailableDrivers();
    }

    /**
     * Clear cache for specific currency and driver
     */
    public static function clearCache(?Currency $currency = null, ?string $driver = null): void
    {
        app('price-feed')->clearCache($currency, $driver);
    }
}
