<?php

namespace JalalLinuX\PriceFeed\Drivers;

use JalalLinuX\PriceFeed\DataTransferObjects\PriceData;
use JalalLinuX\PriceFeed\Enums\Currency;

class TgjuDriver extends AbstractDriver
{
    /**
     * Currency key mapping for TGJU API
     * Maps our Currency enum to TGJU's instrument keys
     * All crypto prices are in IRR (Iranian Rial)
     */
    protected array $keyMap = [
        // Cryptocurrencies (all in IRR)
        'BTC' => 'btc-irr',
        'ETH' => 'eth-irr',
        'USDT' => 'usdt-irr',
        'BNB' => 'crypto-binance-coin-irr',
        'XRP' => 'xrp-irr',
        'ADA' => 'crypto-cardano-irr',
        'DOGE' => 'crypto-dogecoin-irr',
        'SOL' => 'crypto-solana-irr',
        'TRX' => 'crypto-tron-irr',
        'DOT' => 'crypto-polkadot-irr',
        // 'MATIC' => 'crypto-polygon-irr', // Not available in TGJU with IRR
        'LTC' => 'crypto-litecoin-irr',
        'SHIB' => 'crypto-shiba-inu-irr',
        'AVAX' => 'crypto-avalanche-irr',
        'UNI' => 'crypto-uniswap', // Available but without -irr suffix
        'LINK' => 'crypto-chainlink-irr',

        // Fiat Currencies (to IRR)
        'USD' => 'price_dollar_rl',
        'EUR' => 'price_eur',
        'GBP' => 'price_gbp',
        'JPY' => 'usd-jpy-ask',
        'CNY' => 'usd-cny-ask',
        'AUD' => 'price_aud',
        'CAD' => 'usd-cad-ask',
        'CHF' => 'usd-chf-ask',
        'AED' => 'price_aed',
        'TRY' => 'price_try',

        // Precious Metals (in IRR)
        'GOLD' => 'geram18', // Gold per gram (18k)
        'SILVER' => 'silver', // Silver per ounce
    ];

    /**
     * Get the price for a single currency
     */
    public function getPrice(Currency $currency): PriceData
    {
        return $this->getCachedPrice($currency, function () use ($currency) {
            return $this->fetchPriceFromApi($currency);
        });
    }

    /**
     * Fetch price from API (called when cache misses)
     */
    protected function fetchPriceFromApi(Currency $currency): PriceData
    {
        try {
            // Get cached API response or fetch from API
            $data = $this->getCachedApiResponse();

            // Get the key for this currency
            $key = $this->keyMap[$currency->value] ?? strtolower($currency->value);

            // Check if data exists for this currency
            if (! isset($data['current'][$key])) {
                $this->handleApiError(
                    new \Exception("Currency {$currency->value} not found in TGJU response"),
                    'Currency data not available'
                );
            }

            $instrumentData = $data['current'][$key];

            // Parse price - TGJU returns prices as strings with commas
            $price = $this->parsePrice($instrumentData['p'] ?? 0);
            $high = $this->parsePrice($instrumentData['h'] ?? null);
            $low = $this->parsePrice($instrumentData['l'] ?? null);
            $change = $this->parsePrice($instrumentData['d'] ?? null);
            $changePercentage = isset($instrumentData['dp']) ? (float) $instrumentData['dp'] : null;

            // Parse timestamp
            $timestamp = isset($instrumentData['ts'])
                ? new \DateTime($instrumentData['ts'])
                : new \DateTime;

            return new PriceData(
                currency: $currency,
                price: $price,
                symbol: $currency->value,
                change24h: $change,
                changePercentage24h: $changePercentage,
                high24h: $high,
                low24h: $low,
                volume24h: null, // TGJU doesn't provide volume
                marketCap: null, // TGJU doesn't provide market cap
                timestamp: $timestamp,
                raw: $instrumentData
            );
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Failed to fetch price from TGJU');
        }
    }

    /**
     * Get cached API response or fetch from TGJU API
     * This ensures we only make one API call per cache period, even when fetching multiple currencies
     */
    protected function getCachedApiResponse(): array
    {
        $cacheKey = "{$this->cachePrefix}:{$this->getName()}:api_response";

        if (! $this->cacheEnabled) {
            return $this->fetchApiResponse();
        }

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->fetchApiResponse();
        });
    }

    /**
     * Fetch raw API response from TGJU
     */
    protected function fetchApiResponse(): array
    {
        $response = $this->getHttpClient()->get('/ajax.json');

        if (! $response->successful()) {
            $this->handleApiError(
                new \Exception($response->body()),
                'TGJU API request failed'
            );
        }

        return $response->json();
    }

    /**
     * Parse price string to float
     * TGJU returns prices as strings with commas (e.g., "1,125,050,000")
     */
    protected function parsePrice(string|int|float|null $price): float
    {
        if ($price === null || $price === '') {
            return 0.0;
        }

        // Remove commas and convert to float
        $cleanPrice = str_replace(',', '', (string) $price);

        return (float) $cleanPrice;
    }

    /**
     * Authentication not required for TGJU (it's a free public API)
     */
    protected function withAuthentication(\Illuminate\Http\Client\PendingRequest $client): \Illuminate\Http\Client\PendingRequest
    {
        // TGJU is a free API, no authentication needed
        return $client;
    }
}
