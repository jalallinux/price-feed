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
        Currency::BTC->value => ['type' => 'crypto', 'key' => 'Bitcoin'],
        Currency::ETH->value => ['type' => 'crypto', 'key' => 'Ethereum'],
        Currency::USDT->value => ['type' => 'crypto', 'key' => 'Tether'],
        Currency::BNB->value => ['type' => 'crypto', 'key' => 'Binance Coin'],
        Currency::XRP->value => ['type' => 'crypto', 'key' => 'XRP'],
        Currency::ADA->value => ['type' => 'crypto', 'key' => 'Cardano'],
        Currency::DOGE->value => ['type' => 'crypto', 'key' => 'Dogecoin'],
        Currency::SOL->value => ['type' => 'crypto', 'key' => 'Solana'],
        Currency::TRX->value => ['type' => 'crypto', 'key' => 'TRON'],
        Currency::DOT->value => ['type' => 'crypto', 'key' => 'Polkadot'],
        Currency::LTC->value => ['type' => 'crypto', 'key' => 'Litecoin'],
        Currency::SHIB->value => ['type' => 'crypto', 'key' => 'SHIBA INU'],
        Currency::AVAX->value => ['type' => 'crypto', 'key' => 'Avalanche'],
        Currency::UNI->value => ['type' => 'crypto', 'key' => 'Uniswap'],
        Currency::LINK->value => ['type' => 'crypto', 'key' => 'Chainlink'],
        Currency::MATIC->value => ['type' => 'crypto', 'key' => 'Polygon Ecosystem Token'],

        // Fiat Currencies (from Gold_Currency endpoint)
        Currency::USD->value => ['type' => 'currency', 'key' => 'USD'],
        Currency::EUR->value => ['type' => 'currency', 'key' => 'EUR'],
        Currency::GBP->value => ['type' => 'currency', 'key' => 'GBP'],
        Currency::JPY->value => ['type' => 'currency', 'key' => 'JPY'],
        Currency::CNY->value => ['type' => 'currency', 'key' => 'CNY'],
        Currency::AUD->value => ['type' => 'currency', 'key' => 'AUD'],
        Currency::CAD->value => ['type' => 'currency', 'key' => 'CAD'],
        Currency::CHF->value => ['type' => 'currency', 'key' => 'CHF'],
        Currency::AED->value => ['type' => 'currency', 'key' => 'AED'],
        Currency::TRY->value => ['type' => 'currency', 'key' => 'TRY'],

        // Precious Metals (from Commodity endpoint and Gold_Currency endpoint)
        Currency::GOLD_OUNCE->value => ['type' => 'gold', 'key' => 'XAUUSD'],
        Currency::IR_GOLD_18->value => ['type' => 'gold', 'key' => 'IR_GOLD_18K'],
        Currency::IR_GOLD_24->value => ['type' => 'gold', 'key' => 'IR_GOLD_24K'],
        Currency::IR_GOLD_MELTED->value => ['type' => 'gold', 'key' => 'IR_GOLD_MELTED'],
        Currency::IR_COIN_1G->value => ['type' => 'gold', 'key' => 'IR_COIN_1G'],
        Currency::IR_COIN_QUARTER->value => ['type' => 'gold', 'key' => 'IR_COIN_QUARTER'],
        Currency::IR_COIN_HALF->value => ['type' => 'gold', 'key' => 'IR_COIN_HALF'],
        Currency::IR_COIN_EMAMI->value => ['type' => 'gold', 'key' => 'IR_COIN_EMAMI'],
        Currency::IR_COIN_BAHAR->value => ['type' => 'gold', 'key' => 'IR_COIN_BAHAR'],
        Currency::SILVER_OUNCE->value => ['type' => 'commodity', 'key' => 'XAGUSD'],
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
                unit: $this->unit,
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
