<?php

namespace JalalLinuX\PriceFeed\Drivers;

use JalalLinuX\PriceFeed\DataTransferObjects\PriceData;
use JalalLinuX\PriceFeed\Enums\Currency;

class TgnDriver extends AbstractDriver
{
    protected string $username;

    /**
     * Currency key mapping for TGN API
     * Maps our Currency enum to TGN's response keys
     */
    protected array $keyMap = [
        // Fiat Currencies (exchange rates to IRR)
        Currency::USD->value => 'Dollar',
        Currency::EUR->value => 'Euro',
        Currency::AED->value => 'Derham',

        // Precious Metals and Coins (in IRR)
        Currency::GOLD_OUNCE->value => 'OunceTala',
        Currency::IR_GOLD_18->value => 'YekGram18',
        Currency::IR_COIN_1G->value => 'SekehGerami',
        Currency::IR_COIN_QUARTER->value => 'SekehRob',
        Currency::IR_COIN_HALF->value => 'SekehNim',
        Currency::IR_COIN_EMAMI->value => 'SekehEmam',
        Currency::IR_COIN_BAHAR->value => 'SekehTamam',
    ];

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->username = $config['username'] ?? '';
    }

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
            $key = $this->keyMap[$currency->value] ?? null;

            if (! $key || ! isset($data[$key])) {
                $this->handleApiError(
                    new \Exception("Currency {$currency->value} not found in TGN response"),
                    'Currency data not available'
                );
            }

            $price = (float) $data[$key];

            // Parse timestamp from TimeRead field
            $timestamp = isset($data['TimeRead'])
                ? \DateTime::createFromFormat('Y/m/d H:i:s', $data['TimeRead']) ?: new \DateTime
                : new \DateTime;

            return new PriceData(
                currency: $currency,
                price: $price,
                unit: $this->unit,
                symbol: $currency->value,
                change24h: null, // TGN doesn't provide 24h change
                changePercentage24h: null,
                high24h: null, // TGN doesn't provide 24h high
                low24h: null, // TGN doesn't provide 24h low
                volume24h: null, // TGN doesn't provide volume
                marketCap: null, // TGN doesn't provide market cap
                timestamp: $timestamp,
                raw: $data
            );
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Failed to fetch price from TGN');
        }
    }

    /**
     * Get cached API response or fetch from TGN API
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
     * Fetch raw API response from TGN
     */
    protected function fetchApiResponse(): array
    {
        $endpoint = "/Pr/Get/{$this->username}/{$this->apiKey}";
        $response = $this->getHttpClient()->get($endpoint);

        if (! $response->successful()) {
            $this->handleApiError(
                new \Exception($response->body()),
                'TGN API request failed'
            );
        }

        return $response->json();
    }

    /**
     * Authentication not required for TGN (credentials are in URL)
     */
    protected function withAuthentication(\Illuminate\Http\Client\PendingRequest $client): \Illuminate\Http\Client\PendingRequest
    {
        // TGN uses username and API key in the URL path, not headers
        return $client;
    }
}
