<?php

namespace JalalLinuX\PriceFeed\Contracts;

use Illuminate\Support\Collection;
use JalalLinuX\PriceFeed\DataTransferObjects\PriceData;
use JalalLinuX\PriceFeed\Enums\Currency;

interface DriverInterface
{
    /**
     * Get the price for a single currency
     */
    public function getPrice(Currency $currency): PriceData;

    /**
     * Get prices for multiple currencies
     *
     * @param  array<Currency>  $currencies
     * @return Collection<Currency, PriceData>
     */
    public function getPrices(array $currencies): Collection;

    /**
     * Get all supported currencies for this driver
     *
     * @return array<Currency>
     */
    public function getSupportedCurrencies(): array;

    /**
     * Check if the driver supports a specific currency
     */
    public function supports(Currency $currency): bool;

    /**
     * Get the driver name
     */
    public function getName(): string;
}
