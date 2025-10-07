<?php

namespace JalalLinuX\PriceFeed\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use JalalLinuX\PriceFeed\Contracts\DriverInterface;
use JalalLinuX\PriceFeed\DataTransferObjects\PriceData;
use JalalLinuX\PriceFeed\Enums\Currency;

/**
 * @method static PriceData getPrice(Currency $currency, ?string $driver = null)
 * @method static Collection getPrices(array $currencies, ?string $driver = null)
 * @method static array getSupportedCurrencies(?string $driver = null)
 * @method static array getAvailableDrivers()
 * @method static void clearCache(?Currency $currency = null, ?string $driver = null)
 * @method static DriverInterface driver(?string $driver = null)
 *
 * @see \JalalLinuX\PriceFeed\PriceFeedManager
 */
class PriceFeed extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'price-feed';
    }
}
