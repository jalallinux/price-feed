<?php

namespace JalalLinuX\PriceFeed\Drivers;

use Illuminate\Http\Client\PendingRequest;
use JalalLinuX\PriceFeed\DataTransferObjects\PriceData;
use JalalLinuX\PriceFeed\Enums\Currency;

class GoldApiDriver extends AbstractDriver
{
    /**
     * Metal symbol mapping for GoldAPI
     */
    protected array $metalMap = [
        'GOLD' => 'XAU',
        'SILVER' => 'XAG',
        'PLATINUM' => 'XPT',
        'PALLADIUM' => 'XPD',
    ];

    /**
     * Add authentication to the HTTP client
     */
    protected function withAuthentication(PendingRequest $client): PendingRequest
    {
        return $client->withHeaders([
            'x-access-token' => $this->apiKey,
        ]);
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
            $metalSymbol = $this->metalMap[$currency->value] ?? $currency->value;
            $baseCurrency = $this->options['base_currency'] ?? 'USD';

            $response = $this->getHttpClient()
                ->get("/{$metalSymbol}/{$baseCurrency}");

            if (! $response->successful()) {
                $this->handleApiError(
                    new \Exception($response->body()),
                    'GoldAPI request failed'
                );
            }

            $data = $response->json();

            if (! isset($data['price'])) {
                $this->handleApiError(
                    new \Exception('Invalid API response'),
                    'Unable to parse GoldAPI response'
                );
            }

            return new PriceData(
                currency: $currency,
                price: (float) $data['price'],
                symbol: $metalSymbol,
                change24h: isset($data['ch']) ? (float) $data['ch'] : null,
                changePercentage24h: isset($data['chp']) ? (float) $data['chp'] : null,
                high24h: isset($data['high_price']) ? (float) $data['high_price'] : null,
                low24h: isset($data['low_price']) ? (float) $data['low_price'] : null,
                volume24h: null,
                marketCap: null,
                timestamp: new \DateTime,
                raw: $data
            );
        } catch (\Throwable $e) {
            $this->handleApiError($e, 'Failed to fetch price from GoldAPI');
        }
    }
}
