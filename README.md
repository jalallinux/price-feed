# Price Feed

**Price Feed** is a Laravel package for fetching real-time prices of multiple asset types — including cryptocurrencies, fiat currencies, gold, silver, and metal derivatives — through a unified, driver-based architecture.

Each driver connects to a third-party provider (such as TGJU, Brsapi, or GoldAPI) and implements a shared interface. This makes it easy to extend, switch, or customize sources dynamically — all through Laravel's powerful configuration system.

---

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jalallinux/price-feed.svg?style=flat-square)](https://packagist.org/packages/jalallinux/price-feed)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jalallinux/price-feed/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jalallinux/price-feed/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jalallinux/price-feed/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jalallinux/price-feed/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jalallinux/price-feed.svg?style=flat-square)](https://packagist.org/packages/jalallinux/price-feed)

---

## 🚀 Features

- 🧩 **Driver-based architecture** — Easily integrate multiple third-party providers.
- ⚙️ **Dynamic configuration** — Define drivers and supported currencies from config files or database.
- 💱 **Supports multiple asset types:**
    - Cryptocurrencies (BTC, ETH, etc.)
    - Fiat currencies (USD, EUR, IRR, etc.)
    - Precious metals (Gold, Silver, etc.)
- 🧠 **Unified interface** — All drivers implement a shared contract.
- ⚡ **Real-time prices** — Fetch fresh rates using Laravel's `Http` facade.
- 🧰 **Extensible design** — Build and register your own drivers easily.
- 💾 **Smart caching** — Built-in per-driver caching to reduce API calls and improve performance.

---

## 🧭 Installation

Install the package via Composer:

```bash
composer require jalallinux/price-feed
```

Publish and customize the configuration file:

```bash
php artisan vendor:publish --tag="price-feed-config"
```

Example of the published config:

```php
return [

    'default' => env('PRICE_FEED_DRIVER', 'tgju'),

    'drivers' => [
        'brsapi' => [
            'driver' => \JalalLinuX\PriceFeed\Drivers\BrsapiDriver::class,
            'api_key' => env('BRSAPI_API_KEY'),
            'base_url' => 'https://brsapi.ir',
            'cache_enabled' => env('BRSAPI_CACHE_ENABLED', true),
            'cache_ttl' => env('BRSAPI_CACHE_TTL', 120), // seconds
            'cache_prefix' => 'price_feed',
            'currencies' => [
                Currency::BTC,
                Currency::ETH,
                Currency::USD,
                Currency::GOLD,
                // ...
            ],
            'options' => [
                'timeout' => 10,
            ],
        ],
        'tgju' => [
            'driver' => \JalalLinuX\PriceFeed\Drivers\TgjuDriver::class,
            'api_key' => null, // Free API
            'base_url' => 'https://call5.tgju.org',
            'cache_enabled' => env('TGJU_CACHE_ENABLED', true),
            'cache_ttl' => env('TGJU_CACHE_TTL', 120), // seconds
            'cache_prefix' => 'price_feed',
            'currencies' => [
                Currency::BTC,
                Currency::USD,
                Currency::GOLD,
                // ...
            ],
            'options' => [
                'timeout' => 10,
            ],
        ],
        'goldapi' => [
            'driver' => \JalalLinuX\PriceFeed\Drivers\GoldApiDriver::class,
            'api_key' => env('GOLDAPI_API_KEY'),
            'base_url' => 'https://www.goldapi.io/api',
            'cache_enabled' => env('GOLDAPI_CACHE_ENABLED', true),
            'cache_ttl' => env('GOLDAPI_CACHE_TTL', 300),
            'cache_prefix' => 'price_feed',
            'currencies' => [
                Currency::GOLD,
                Currency::SILVER,
                // ...
            ],
            'options' => [
                'timeout' => 10,
            ],
        ],
    ],

];
```

---

## 🧪 Usage Example

### Basic Usage

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Get Bitcoin price using the default driver
$btcPrice = PriceFeed::getPrice(Currency::BTC);

echo "BTC Price: $" . $btcPrice->price;
echo "24h Change: " . $btcPrice->changePercentage24h . "%";
echo "Volume: $" . $btcPrice->volume24h;

// Get prices for multiple currencies
$prices = PriceFeed::getPrices([Currency::BTC, Currency::ETH, Currency::USDT]);

foreach ($prices as $currency => $priceData) {
    echo "{$currency}: $" . $priceData->price . "\n";
}
```

### Using Specific Drivers

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Get Bitcoin price from Brsapi (Iranian market)
$btcPrice = PriceFeed::getPrice(Currency::BTC, 'brsapi');

// Get USD price in IRR from TGJU (Iranian market)
$usdIRR = PriceFeed::getPrice(Currency::USD, 'tgju');

// Get Gold price from GoldAPI (international)
$goldPrice = PriceFeed::getPrice(Currency::GOLD, 'goldapi');

// Get Ethereum price from Brsapi
$ethPrice = PriceFeed::getPrice(Currency::ETH, 'brsapi');
```

### Using Driver Instances

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Get a driver instance
$tgju = PriceFeed::driver('tgju');

// Get supported currencies
$supportedCurrencies = $tgju->getSupportedCurrencies();

// Check if a currency is supported
if ($tgju->supports(Currency::BTC)) {
    $price = $tgju->getPrice(Currency::BTC);
}
```

### Cache Configuration

The package includes built-in caching to reduce API calls and improve performance. Each driver can have its own cache settings:

**Per-Driver Configuration:**

```php
'drivers' => [
    'brsapi' => [
        // ...
        'cache_enabled' => env('BRSAPI_CACHE_ENABLED', true),
        'cache_ttl' => env('BRSAPI_CACHE_TTL', 120), // Cache for 2 minutes
        'cache_prefix' => 'price_feed',
    ],
    'tgju' => [
        // ...
        'cache_enabled' => env('TGJU_CACHE_ENABLED', true),
        'cache_ttl' => env('TGJU_CACHE_TTL', 120), // Cache for 2 minutes
        'cache_prefix' => 'price_feed',
    ],
    'goldapi' => [
        // ...
        'cache_enabled' => env('GOLDAPI_CACHE_ENABLED', true),
        'cache_ttl' => env('GOLDAPI_CACHE_TTL', 300), // Cache for 5 minutes
        'cache_prefix' => 'price_feed',
    ],
],
```

**Environment Variables:**

```env
# Enable/disable caching per driver
BRSAPI_CACHE_ENABLED=true
BRSAPI_CACHE_TTL=120

TGJU_CACHE_ENABLED=true
TGJU_CACHE_TTL=120

GOLDAPI_CACHE_ENABLED=true
GOLDAPI_CACHE_TTL=300
```

**Benefits:**
- ✅ Reduces API calls significantly (e.g., fetching 100 currencies makes only 1 API call for TGJU)
- ✅ Improves response time for repeated requests
- ✅ Helps avoid hitting API rate limits
- ✅ Per-driver TTL allows custom caching strategies for different data sources

### Helper Methods

```php
use JalalLinuX\PriceFeed\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Get available drivers
$drivers = PriceFeed::availableDrivers();

// Get supported currencies for default driver
$currencies = PriceFeed::supportedCurrencies();
```

### Creating Custom Drivers

You can create your own driver by extending the `AbstractDriver` or implementing the `DriverInterface`:

```php
namespace App\PriceFeed\Drivers;

use JalalLinuX\PriceFeed\Drivers\AbstractDriver;
use JalalLinuX\PriceFeed\DataTransferObjects\PriceData;
use JalalLinuX\PriceFeed\Enums\Currency;

class MyCustomDriver extends AbstractDriver
{
    public function getPrice(Currency $currency): PriceData
    {
        // Fetch data from your source using HTTP client
        $response = $this->getHttpClient()
            ->get('/api/price', [
                'symbol' => $currency->value,
            ]);

        $data = $response->json();

        return PriceData::from([
            'currency' => $currency,
            'price' => $data['price'],
            'symbol' => $currency->value,
            'change_24h' => $data['change_24h'] ?? null,
            'change_percentage_24h' => $data['change_percentage_24h'] ?? null,
            'volume_24h' => $data['volume'] ?? null,
            'timestamp' => now(),
            'raw' => $data,
        ]);
    }
}
```

Then register it in your config:

```php
'drivers' => [
    'custom' => [
        'driver' => App\PriceFeed\Drivers\MyCustomDriver::class,
        'api_key' => env('CUSTOM_API_KEY'),
        'base_url' => 'https://api.custom.com',
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

---

## 🧰 Testing

Run the tests using:

```bash
composer test
```

---

## 🧱 Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

---

## 🔒 Security

Please review [our security policy](../../security/policy) for how to report vulnerabilities.

---

## 🪪 License

The MIT License (MIT). See [License File](LICENSE.md) for more information.

---

**Developed by [JalalLinuX](https://github.com/jalallinux)**
