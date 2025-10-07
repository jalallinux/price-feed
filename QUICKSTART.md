# Quick Start Guide

Get up and running with Price Feed in 5 minutes!

## Installation

```bash
composer require jalallinux/price-feed
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="price-feed-config"
```

Add API keys to your `.env`:

```env
TGJU_API_KEY=your_key_here
BRSAPI_API_KEY=your_key_here
GOLDAPI_API_KEY=your_key_here
```

## Basic Usage

### 1. Get a Single Price

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Using default driver
$btcPrice = PriceFeed::getPrice(Currency::BTC);
echo "BTC: $" . $btcPrice->price;

// Using specific driver
$ethPrice = PriceFeed::getPrice(Currency::ETH, 'binance');
echo "ETH: $" . $ethPrice->price;
```

### 2. Get Multiple Prices

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

$prices = PriceFeed::getPrices([
    Currency::BTC,
    Currency::ETH,
    Currency::USDT,
]);

foreach ($prices as $currency => $priceData) {
    echo "{$currency}: $" . $priceData->price . "\n";
}
```

### 3. Access Detailed Information

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

$btc = PriceFeed::getPrice(Currency::BTC);

echo "Price: $" . $btc->price . "\n";
echo "24h Change: " . $btc->changePercentage24h . "%\n";
echo "24h High: $" . $btc->high24h . "\n";
echo "24h Low: $" . $btc->low24h . "\n";
echo "Volume: $" . $btc->volume24h . "\n";
echo "Market Cap: $" . $btc->marketCap . "\n";
```

## Available Currencies

### Cryptocurrencies
- BTC, ETH, USDT, BNB, XRP, ADA, DOGE, SOL, TRX, DOT, MATIC, LTC, SHIB, AVAX, UNI, LINK

### Fiat Currencies
- USD, EUR, GBP, JPY, CNY, AUD, CAD, CHF, IRR, AED, TRY

### Precious Metals
- GOLD, SILVER, PLATINUM, PALLADIUM

## Available Drivers

| Driver    | Asset Type      | Requires API Key |
|-----------|-----------------|------------------|
| `tgju`    | TGJU            | Yes |
| `brsapi`  | BRSAPI          | Yes |
| `goldapi` | Precious Metals | Yes |

## Common Tasks

### Check Supported Currencies

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;

$driver = PriceFeed::driver('binance');
$currencies = $driver->getSupportedCurrencies();

foreach ($currencies as $currency) {
    echo $currency->value . "\n";
}
```

### Check if Currency is Supported

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

$driver = PriceFeed::driver('binance');

if ($driver->supports(Currency::BTC)) {
    $price = $driver->getPrice(Currency::BTC);
}
```

### Clear Cache

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Clear specific currency cache
PriceFeed::clearCache(Currency::BTC, 'coingecko');

// Clear all cache for a driver
PriceFeed::clearCache(driver: 'coingecko');

// Clear all cache
PriceFeed::clearCache();
```

### Convert to Array/JSON

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

$btcPrice = PriceFeed::getPrice(Currency::BTC);

// To array
$array = $btcPrice->toArray();

// To JSON
$json = $btcPrice->toJson();

// In API response (automatic serialization)
return response()->json($btcPrice);
```

## Error Handling

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;
use JalalLinuX\PriceFeed\Exceptions\ApiException;
use JalalLinuX\PriceFeed\Exceptions\UnsupportedCurrencyException;

try {
    $price = PriceFeed::getPrice(Currency::BTC, 'coingecko');

} catch (UnsupportedCurrencyException $e) {
    // Currency not supported by this driver
    echo "Currency not supported";

} catch (ApiException $e) {
    // API request failed
    echo "API error: " . $e->getMessage();
}
```

## Cache Configuration

Edit `config/price-feed.php`:

```php
'cache' => [
    'enabled' => true,           // Enable/disable caching
    'ttl' => 60,                 // Cache duration in seconds
    'prefix' => 'price_feed',    // Cache key prefix
],
```

## Creating a Custom Driver

1. Create a driver class:

```php
namespace App\Drivers;

use JalalLinuX\PriceFeed\Drivers\AbstractDriver;
use JalalLinuX\PriceFeed\DataTransferObjects\PriceData;
use JalalLinuX\PriceFeed\Enums\Currency;

class MyCustomDriver extends AbstractDriver
{
    public function getPrice(Currency $currency): PriceData
    {
        $response = $this->getHttpClient()
            ->get('/api/price/' . $currency->value);

        $data = $response->json();

        return PriceData::from([
            'currency' => $currency,
            'price' => $data['price'],
            'symbol' => $currency->value,
            'timestamp' => now(),
            'raw' => $data,
        ]);
    }
}
```

2. Register in `config/price-feed.php`:

```php
'drivers' => [
    'mycustom' => [
        'driver' => \App\Drivers\MyCustomDriver::class,
        'api_key' => env('MYCUSTOM_API_KEY'),
        'base_url' => 'https://api.mycustom.com',
        'currencies' => [
            Currency::BTC,
            Currency::ETH,
        ],
        'options' => [
            'timeout' => 10,
        ],
    ],
],
```

3. Use it:

```php
$price = PriceFeed::getPrice(Currency::BTC, 'mycustom');
```

## Next Steps

- Read the full [README.md](README.md) for more details
- Check [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md) for advanced examples
- Review [ARCHITECTURE.md](ARCHITECTURE.md) to understand the internals
- Write tests for your implementation

## Getting Help

- 📖 [Documentation](README.md)
- 💬 [GitHub Discussions](https://github.com/jalallinux/price-feed/discussions)
- 🐛 [Report Issues](https://github.com/jalallinux/price-feed/issues)
- 📧 Contact: smjjalalzadeh93@gmail.com

## Example: Building a Simple Price Dashboard

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

class PriceDashboard
{
    public function getCryptoPrices(): array
    {
        $cryptos = [
            Currency::BTC,
            Currency::ETH,
            Currency::BNB,
            Currency::XRP,
        ];

        $prices = PriceFeed::getPrices($cryptos);

        $dashboard = [];
        foreach ($prices as $currency => $priceData) {
            $dashboard[] = [
                'name' => $currency,
                'price' => '$' . number_format($priceData->price, 2),
                'change' => $priceData->changePercentage24h . '%',
                'trend' => $priceData->changePercentage24h > 0 ? '↑' : '↓',
                'volume' => '$' . $this->formatVolume($priceData->volume24h),
            ];
        }

        return $dashboard;
    }

    private function formatVolume(?float $volume): string
    {
        if (!$volume) return 'N/A';

        if ($volume >= 1_000_000_000) {
            return number_format($volume / 1_000_000_000, 2) . 'B';
        }
        if ($volume >= 1_000_000) {
            return number_format($volume / 1_000_000, 2) . 'M';
        }
        return number_format($volume, 2);
    }
}

// Usage in a controller
public function index()
{
    $dashboard = new PriceDashboard();
    $prices = $dashboard->getCryptoPrices();

    return view('dashboard', ['prices' => $prices]);
}
```

## Example: Building a Price Alert

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;
use Illuminate\Support\Facades\Cache;

class PriceAlertService
{
    public function checkAlert(Currency $currency, float $targetPrice): bool
    {
        $currentPrice = PriceFeed::getPrice($currency);

        if ($currentPrice->price >= $targetPrice) {
            // Send notification
            $this->sendNotification($currency, $currentPrice->price, $targetPrice);
            return true;
        }

        return false;
    }

    private function sendNotification(Currency $currency, float $current, float $target): void
    {
        // Implement your notification logic
        \Log::info("Price Alert: {$currency->value} reached ${current} (target: ${target})");
    }
}

// Setup in a scheduled job
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        $alertService = new PriceAlertService();
        $alertService->checkAlert(Currency::BTC, 50000);
    })->everyMinute();
}
```

Happy coding! 🚀
