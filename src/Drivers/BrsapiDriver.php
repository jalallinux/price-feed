<?php

namespace JalalLinuX\PriceFeed\Drivers;

use JalalLinuX\PriceFeed\DataTransferObjects\PriceData;
use JalalLinuX\PriceFeed\Enums\Currency;

class BrsapiDriver extends AbstractDriver
{
    /**
     * Currency key mapping for Brsapi
     * Maps our Currency enum to Brsapi's data keys
     */
    protected array $keyMap = [
        // Cryptocurrencies (from Cryptocurrency endpoint)
        'BTC' => ['type' => 'crypto', 'key' => 'Bitcoin'],
        'ETH' => ['type' => 'crypto', 'key' => 'Ethereum'],
        'USDT' => ['type' => 'crypto', 'key' => 'Tether'],
        'BNB' => ['type' => 'crypto', 'key' => 'Binance Coin'],
        'XRP' => ['type' => 'crypto', 'key' => 'XRP'],
        'ADA' => ['type' => 'crypto', 'key' => 'Cardano'],
        'DOGE' => ['type' => 'crypto', 'key' => 'Dogecoin'],
        'SOL' => ['type' => 'crypto', 'key' => 'Solana'],
        'TRX' => ['type' => 'crypto', 'key' => 'TRON'],
        'DOT' => ['type' => 'crypto', 'key' => 'Polkadot'],
        'LTC' => ['type' => 'crypto', 'key' => 'Litecoin'],
        'SHIB' => ['type' => 'crypto', 'key' => 'SHIBA INU'],
        'AVAX' => ['type' => 'crypto', 'key' => 'Avalanche'],
        'UNI' => ['type' => 'crypto', 'key' => 'Uniswap'],
        'LINK' => ['type' => 'crypto', 'key' => 'Chainlink'],
        'MATIC' => ['type' => 'crypto', 'key' => 'Polygon Ecosystem Token'],

        // Fiat Currencies (from Gold_Currency endpoint)
        'USD' => ['type' => 'currency', 'key' => 'USD'],
        'EUR' => ['type' => 'currency', 'key' => 'EUR'],
        'GBP' => ['type' => 'currency', 'key' => 'GBP'],
        'JPY' => ['type' => 'currency', 'key' => 'JPY'],
        'CNY' => ['type' => 'currency', 'key' => 'CNY'],
        'AUD' => ['type' => 'currency', 'key' => 'AUD'],
        'CAD' => ['type' => 'currency', 'key' => 'CAD'],
        'CHF' => ['type' => 'currency', 'key' => 'CHF'],
        'AED' => ['type' => 'currency', 'key' => 'AED'],
        'TRY' => ['type' => 'currency', 'key' => 'TRY'],

        // Precious Metals (from Commodity endpoint and Gold_Currency endpoint)
        'GOLD' => ['type' => 'gold', 'key' => 'IR_GOLD_18K'],
        'SILVER' => ['type' => 'commodity', 'key' => 'XAGUSD'],
        'PLATINUM' => ['type' => 'commodity', 'key' => 'XPTUSD'],
        'PALLADIUM' => ['type' => 'commodity', 'key' => 'XPDUSD'],
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
            $mapping = $this->keyMap[$currency->value] ?? null;

            if (! $mapping || ! isset($mapping['type']) || ! isset($mapping['key'])) {
                throw new \Exception("Currency {$currency->value} not supported by Brsapi");
            }

            $data = $this->getCachedApiResponse($mapping['type']);
            $priceInfo = $this->extractPriceData($data, $mapping);

            if (! $priceInfo) {
                throw new \Exception(
                    "Currency {$currency->value} (key: {$mapping['key']}, type: {$mapping['type']}) not found in Brsapi response"
                );
            }

            if (! isset($priceInfo['price']) || $priceInfo['price'] === null) {
                throw new \Exception(
                    "Price data is null for {$currency->value}. Raw data: ".json_encode($priceInfo)
                );
            }

            return new PriceData(
                currency: $currency,
                price: $priceInfo['price'],
                symbol: $currency->value,
                change24h: $priceInfo['change_value'] ?? null,
                changePercentage24h: $priceInfo['change_percent'] ?? null,
                high24h: null,
                low24h: null,
                volume24h: null,
                marketCap: $priceInfo['market_cap'] ?? null,
                timestamp: isset($priceInfo['time_unix'])
                    ? new \DateTime('@'.$priceInfo['time_unix'])
                    : new \DateTime,
                raw: $priceInfo
            );
        } catch (\Throwable $e) {
            // Get more detailed error information
            $errorDetails = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'currency' => $currency->value ?? 'unknown',
            ];

            throw new \Exception(
                "Failed to fetch price from Brsapi: {$e->getMessage()}\n".
                "File: {$e->getFile()}:{$e->getLine()}\n".
                "Currency: {$currency->value}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get cached API response or fetch from Brsapi API
     * This ensures we only make one API call per cache period for each endpoint type
     */
    protected function getCachedApiResponse(string $type): array
    {
        $cacheKey = "{$this->cachePrefix}:{$this->getName()}:{$type}:api_response";

        if (! $this->cacheEnabled) {
            return $this->fetchApiResponse($type);
        }

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () use ($type) {
            return $this->fetchApiResponse($type);
        });
    }

    /**
     * Fetch raw API response from Brsapi based on type
     */
    protected function fetchApiResponse(string $type): array
    {
        $endpoint = match ($type) {
            'crypto' => '/Api/Market/Cryptocurrency.php',
            'commodity' => '/Api/Market/Commodity.php',
            'gold' => '/Api/Market/Gold_Currency.php',
            'currency' => '/Api/Market/Gold_Currency.php',
            default => throw new \Exception("Unknown type: {$type}"),
        };

        $response = $this->getHttpClient()
            ->get($endpoint, [
                'key' => $this->apiKey,
            ]);

        if (! $response->successful()) {
            throw new \Exception('Brsapi API request failed: '.$response->body());
        }

        $jsonData = $response->json();

        if (! is_array($jsonData)) {
            throw new \Exception('Brsapi API returned invalid response format (not an array)');
        }

        return $jsonData;
    }

    /**
     * Extract price data from API response based on currency mapping
     */
    protected function extractPriceData(array $data, array $mapping): ?array
    {
        $type = $mapping['type'];
        $key = $mapping['key'];

        // Handle cryptocurrency data
        if ($type === 'crypto') {
            foreach ($data as $item) {
                if (isset($item['name_en']) && $item['name_en'] === $key) {
                    return [
                        'price' => $this->parsePrice($item['price_toman'] ?? $item['price'] ?? null),
                        'change_percent' => $item['change_percent'] ?? null,
                        'change_value' => null,
                        'market_cap' => $item['market_cap'] ?? null,
                        'time_unix' => $item['time_unix'] ?? null,
                    ];
                }
            }
        }

        // Handle gold data (from Gold_Currency endpoint)
        if ($type === 'gold' && isset($data['gold'])) {
            foreach ($data['gold'] as $item) {
                if (isset($item['symbol']) && $item['symbol'] === $key) {
                    return [
                        'price' => $this->parsePrice($item['price'] ?? null),
                        'change_percent' => $item['change_percent'] ?? null,
                        'change_value' => $item['change_value'] ?? null,
                        'market_cap' => null,
                        'time_unix' => $item['time_unix'] ?? null,
                    ];
                }
            }
        }

        // Handle currency data (from Gold_Currency endpoint)
        if ($type === 'currency' && isset($data['currency'])) {
            foreach ($data['currency'] as $item) {
                if (isset($item['symbol']) && str_contains($item['symbol'], $key)) {
                    return [
                        'price' => $this->parsePrice($item['price'] ?? null),
                        'change_percent' => $item['change_percent'] ?? null,
                        'change_value' => $item['change_value'] ?? null,
                        'market_cap' => null,
                        'time_unix' => $item['time_unix'] ?? null,
                    ];
                }
            }
        }

        // Handle commodity data (precious metals from Commodity endpoint)
        if ($type === 'commodity' && isset($data['metal_precious'])) {
            $searchKey = strtolower($key);
            foreach ($data['metal_precious'] as $item) {
                if (isset($item['symbol']) && str_contains(strtolower($item['symbol']), $searchKey)) {
                    return [
                        'price' => $this->parsePrice($item['price'] ?? null),
                        'change_percent' => $item['change_percent'] ?? null,
                        'change_value' => $item['change_value'] ?? null,
                        'market_cap' => null,
                        'time_unix' => $item['time_unix'] ?? null,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Parse price string to float
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
     * Authentication using API key in query parameter
     */
    protected function withAuthentication(\Illuminate\Http\Client\PendingRequest $client): \Illuminate\Http\Client\PendingRequest
    {
        // Brsapi uses key in query parameter, not headers
        return $client;
    }
}
