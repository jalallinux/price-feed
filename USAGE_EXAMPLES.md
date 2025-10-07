# Usage Examples

## Table of Contents
- [Basic Usage](#basic-usage)
- [Multiple Drivers](#multiple-drivers)
- [Working with Data](#working-with-data)
- [Cache Management](#cache-management)
- [Error Handling](#error-handling)

## Basic Usage

### Get Single Currency Price

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Get Bitcoin price (uses default driver from config)
$btcPrice = PriceFeed::getPrice(Currency::BTC);

echo "BTC Price: $" . $btcPrice->price;
```

### Get Multiple Currency Prices

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

$currencies = [
    Currency::BTC,
    Currency::ETH,
    Currency::USDT,
];

$prices = PriceFeed::getPrices($currencies);

foreach ($prices as $currency => $priceData) {
    echo "{$currency}: $" . $priceData->price . "\n";
}
```

## Multiple Drivers

### Using Different Drivers for Different Assets

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Get crypto prices from CoinGecko
$btc = PriceFeed::getPrice(Currency::BTC, 'coingecko');
$eth = PriceFeed::getPrice(Currency::ETH, 'coingecko');

// Get fiat exchange rates
$eur = PriceFeed::getPrice(Currency::EUR, 'exchangerate');
$gbp = PriceFeed::getPrice(Currency::GBP, 'exchangerate');

// Get precious metal prices
$gold = PriceFeed::getPrice(Currency::GOLD, 'goldapi');
$silver = PriceFeed::getPrice(Currency::SILVER, 'goldapi');
```

### Working with Driver Instances

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Get a specific driver
$binance = PriceFeed::driver('binance');

// Check what currencies are supported
$supported = $binance->getSupportedCurrencies();

// Get prices only for supported currencies
foreach ($supported as $currency) {
    $price = $binance->getPrice($currency);
    echo "{$currency->value}: $" . $price->price . "\n";
}
```

## Working with Data

### Accessing Price Data Properties

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

$btcPrice = PriceFeed::getPrice(Currency::BTC);

// Access all available properties
echo "Currency: " . $btcPrice->currency->value . "\n";
echo "Price: $" . $btcPrice->price . "\n";
echo "Symbol: " . $btcPrice->symbol . "\n";
echo "24h Change: $" . $btcPrice->change24h . "\n";
echo "24h Change %: " . $btcPrice->changePercentage24h . "%" . "\n";
echo "24h High: $" . $btcPrice->high24h . "\n";
echo "24h Low: $" . $btcPrice->low24h . "\n";
echo "24h Volume: $" . $btcPrice->volume24h . "\n";
echo "Market Cap: $" . $btcPrice->marketCap . "\n";
echo "Timestamp: " . $btcPrice->timestamp->format('Y-m-d H:i:s') . "\n";

// Access raw API response
var_dump($btcPrice->raw);
```

### Converting to Array

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

$btcPrice = PriceFeed::getPrice(Currency::BTC);

// Convert to array (using Spatie Laravel Data)
$priceArray = $btcPrice->toArray();

// Store in database or cache
\Cache::put('btc_price', $priceArray, now()->addMinutes(5));
```

### JSON Serialization

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

$btcPrice = PriceFeed::getPrice(Currency::BTC);

// Convert to JSON (Spatie Laravel Data handles this automatically)
$json = $btcPrice->toJson();

// Return in API response
return response()->json($btcPrice);
```

## Cache Management

### Using Cache (Enabled by Default)

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// First call - fetches from API
$btcPrice = PriceFeed::getPrice(Currency::BTC);

// Second call - returns from cache (within TTL)
$btcPrice = PriceFeed::getPrice(Currency::BTC);
```

### Clearing Cache

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Clear cache for specific currency and driver
PriceFeed::clearCache(Currency::BTC, 'coingecko');

// Clear all cache for a specific driver
PriceFeed::clearCache(driver: 'coingecko');

// Clear all price feed cache
PriceFeed::clearCache();
```

### Configure Cache in Config File

```php
// config/price-feed.php

'cache' => [
    'enabled' => true,
    'ttl' => 300, // 5 minutes in seconds
    'prefix' => 'price_feed',
],
```

## Error Handling

### Handling Unsupported Currencies

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;
use JalalLinuX\PriceFeed\Exceptions\UnsupportedCurrencyException;

try {
    $driver = PriceFeed::driver('binance');

    // Check before fetching
    if ($driver->supports(Currency::BTC)) {
        $price = $driver->getPrice(Currency::BTC);
    } else {
        echo "Currency not supported by this driver\n";
    }

} catch (UnsupportedCurrencyException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Handling API Errors

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;
use JalalLinuX\PriceFeed\Exceptions\ApiException;

try {
    $price = PriceFeed::getPrice(Currency::BTC, 'coingecko');

} catch (ApiException $e) {
    // API request failed
    \Log::error('Price feed API error', [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
    ]);

    // Use fallback or cached data
    $price = \Cache::get('btc_price_fallback');
}
```

### Handling Missing Drivers

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Exceptions\DriverNotFoundException;

try {
    $price = PriceFeed::getPrice(Currency::BTC, 'nonexistent');

} catch (DriverNotFoundException $e) {
    echo "Driver not found: " . $e->getMessage();
}
```

## Advanced Examples

### Building a Price Comparison Service

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

class PriceComparison
{
    public function compare(Currency $currency): array
    {
        $drivers = ['tgju', 'goldapi', 'brsapi'];
        $prices = [];

        foreach ($drivers as $driver) {
            try {
                $driverInstance = PriceFeed::driver($driver);

                if ($driverInstance->supports($currency)) {
                    $price = $driverInstance->getPrice($currency);
                    $prices[$driver] = $price->price;
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to get price from {$driver}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'prices' => $prices,
            'average' => !empty($prices) ? array_sum($prices) / count($prices) : 0,
            'min' => !empty($prices) ? min($prices) : 0,
            'max' => !empty($prices) ? max($prices) : 0,
        ];
    }
}

// Usage
$comparison = new PriceComparison();
$result = $comparison->compare(Currency::BTC);
```

### Creating a Price Alert System

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

class PriceAlert
{
    public function checkPriceAlert(Currency $currency, float $targetPrice): bool
    {
        $currentPrice = PriceFeed::getPrice($currency);

        if ($currentPrice->price >= $targetPrice) {
            // Send notification
            \Notification::send(
                auth()->user(),
                new PriceTargetReached($currency, $currentPrice->price)
            );

            return true;
        }

        return false;
    }
}
```

### Portfolio Value Calculation

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

class Portfolio
{
    protected array $holdings = [
        Currency::BTC => 0.5,
        Currency::ETH => 10,
        Currency::GOLD => 100, // grams
    ];

    public function getTotalValue(): float
    {
        $total = 0;

        foreach ($this->holdings as $currency => $amount) {
            $price = PriceFeed::getPrice($currency);
            $total += $price->price * $amount;
        }

        return $total;
    }

    public function getPortfolioBreakdown(): array
    {
        $breakdown = [];
        $total = $this->getTotalValue();

        foreach ($this->holdings as $currency => $amount) {
            $price = PriceFeed::getPrice($currency);
            $value = $price->price * $amount;

            $breakdown[] = [
                'currency' => $currency->value,
                'amount' => $amount,
                'price' => $price->price,
                'value' => $value,
                'percentage' => ($value / $total) * 100,
            ];
        }

        return $breakdown;
    }
}
```
