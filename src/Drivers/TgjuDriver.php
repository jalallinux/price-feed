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
        Currency::BTC->value => 'btc-irr',
        Currency::ETH->value => 'eth-irr',
        Currency::USDT->value => 'usdt-irr',
        Currency::BNB->value => 'crypto-binance-coin-irr',
        Currency::XRP->value => 'xrp-irr',
        Currency::ADA->value => 'crypto-cardano-irr',
        Currency::DOGE->value => 'crypto-dogecoin-irr',
        Currency::SOL->value => 'crypto-solana-irr',
        Currency::TRX->value => 'crypto-tron-irr',
        Currency::DOT->value => 'crypto-polkadot-irr',
        Currency::LTC->value => 'crypto-litecoin-irr',
        Currency::SHIB->value => 'crypto-shiba-inu-irr',
        Currency::AVAX->value => 'crypto-avalanche-irr',
        Currency::UNI->value => 'crypto-uniswap',
        Currency::LINK->value => 'crypto-chainlink-irr',

        // Fiat Currencies (to IRR)
        Currency::USD->value => 'price_dollar_rl',
        Currency::EUR->value => 'price_eur',
        Currency::GBP->value => 'price_gbp',
        Currency::JPY->value => 'usd-jpy-ask',
        Currency::CNY->value => 'usd-cny-ask',
        Currency::AUD->value => 'price_aud',
        Currency::CAD->value => 'usd-cad-ask',
        Currency::CHF->value => 'usd-chf-ask',
        Currency::AED->value => 'price_aed',
        Currency::TRY->value => 'price_try',

        // Precious Metals (in IRR)
        Currency::IR_GOLD_18->value => 'geram18',
        Currency::IR_GOLD_24->value => 'geram24',
        Currency::SILVER_OUNCE->value => 'silver',
        Currency::SILVER_925->value => 'silver_925',
        Currency::SILVER_999->value => 'silver_999',
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
            $timestamp = isset($instrumentData['ts']) ? new \DateTime($instrumentData['ts']) : new \DateTime;

            return new PriceData(
                currency: $currency,
                price: $price,
                unit: $this->unit,
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
