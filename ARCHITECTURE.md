# Price Feed Package Architecture

## Overview

The Price Feed package follows a driver-based architecture pattern, similar to Laravel's filesystem, cache, and mail systems. This design allows for flexible integration with multiple third-party price providers while maintaining a consistent interface.

## Core Components

### 1. Manager Pattern (`PriceFeedManager`)

The `PriceFeedManager` extends Laravel's `Manager` class and serves as the central point for:
- Driver instantiation and management
- Driver switching
- Caching logic
- Configuration management

**Location:** `src/PriceFeedManager.php`

### 2. Driver Interface (`DriverInterface`)

All drivers must implement this interface, ensuring consistency across different providers.

**Location:** `src/Contracts/DriverInterface.php`

**Methods:**
- `getPrice(Currency $currency): PriceData` - Get single currency price
- `getPrices(array $currencies): Collection` - Get multiple prices
- `getSupportedCurrencies(): array` - List supported currencies
- `supports(Currency $currency): bool` - Check currency support
- `getName(): string` - Get driver name

### 3. Abstract Driver (`AbstractDriver`)

Base implementation providing common functionality for all drivers:
- HTTP client setup with Laravel's `Http` facade
- Authentication handling
- Error handling
- Default `getPrices()` implementation

**Location:** `src/Drivers/AbstractDriver.php`

### 4. Currency Enum

PHP 8.4 enum defining all supported currencies with helper methods.

**Location:** `src/Enums/Currency.php`

**Categories:**
- Cryptocurrencies (BTC, ETH, etc.)
- Fiat Currencies (USD, EUR, IRR, etc.)
- Precious Metals (GOLD, SILVER, etc.)

### 5. Data Transfer Object (`PriceData`)

Uses Spatie's Laravel Data package for type-safe, validated data structures.

**Location:** `src/DataTransferObjects/PriceData.php`

**Properties:**
- `currency` - The currency enum
- `price` - Current price
- `symbol` - Currency symbol
- `change24h` - 24-hour price change
- `changePercentage24h` - 24-hour percentage change
- `high24h` - 24-hour high
- `low24h` - 24-hour low
- `volume24h` - 24-hour trading volume
- `marketCap` - Market capitalization
- `timestamp` - Data timestamp
- `raw` - Raw API response

### 6. Exceptions

Custom exception hierarchy for better error handling:

```
PriceFeedException (base)
├── DriverNotFoundException
├── UnsupportedCurrencyException
└── ApiException
```

**Location:** `src/Exceptions/`

## Included Drivers

### 1. GoldApiDriver

**API:** GoldAPI
**Supported:** Precious metals
**Authentication:** API key via `x-access-token` header

### 2. Tgju

**API:** TGJU.org API
**Supported:** Cryptocurrencies, Fiat currencies, Precious metals (all in IRR)
**Authentication:** Not required (free public API)
**Special Features:**
- All prices denominated in Iranian Rial (IRR)
- Covers Iranian market specifically
- Handles comma-separated price strings
- Real-time data with Persian timestamps

## Configuration Structure

```php
// config/price-feed.php

return [
    // Default driver to use
    'default' => 'tgju',

    // Driver configurations
    'drivers' => [
        'driver_name' => [
            'driver' => DriverClass::class,      // Driver class
            'api_key' => env('API_KEY'),         // API key
            'base_url' => 'https://api.example.com', // Base URL
            'currencies' => [                     // Supported currencies
                Currency::BTC,
                Currency::ETH,
            ],
            'options' => [                        // Driver-specific options
                'timeout' => 10,
                'convert' => 'USD',
            ],
        ],
    ],

    // Cache configuration
    'cache' => [
        'enabled' => true,
        'ttl' => 60,        // seconds
        'prefix' => 'price_feed',
    ],

    // Fallback configuration
    'fallback' => [
        'enabled' => false,
        'drivers' => [],
    ],
];
```

## Data Flow

### 1. Basic Request Flow

```
User Request
    ↓
Facade (PriceFeed)
    ↓
Manager (PriceFeedManager)
    ↓
Cache Check
    ↓
Driver Instance
    ↓
HTTP Request to Third-party API
    ↓
Response Mapping to PriceData DTO
    ↓
Cache Store
    ↓
Return to User
```

### 2. Driver Resolution

```
PriceFeed::getPrice(Currency::BTC, 'coingecko')
    ↓
PriceFeedManager::getPrice()
    ↓
PriceFeedManager::driver('coingecko')
    ↓
PriceFeedManager::createDriver('coingecko')
    ↓
Load config: config('price-feed.drivers.coingecko')
    ↓
Instantiate: new CoinGeckoDriver($config)
    ↓
Return DriverInterface instance
```

## Caching Strategy

The package implements a multi-level caching strategy:

1. **Key Format:** `{prefix}.{driver}.{currency}`
   - Example: `price_feed.coingecko.BTC`

2. **TTL:** Configurable per installation (default: 60 seconds)

3. **Cache Methods:**
   - Automatic caching on `getPrice()`
   - Manual clearing via `clearCache()`
   - Granular control (specific currency, driver, or all)

## Service Provider Registration

```php
// src/PriceFeedServiceProvider.php

public function packageRegistered(): void
{
    // Register manager as singleton
    $this->app->singleton('price-feed', function ($app) {
        return new PriceFeedManager($app);
    });

    // Create alias for type-hinting
    $this->app->alias('price-feed', PriceFeedManager::class);
}
```

## Facade Implementation

```php
// src/Facades/PriceFeed.php

class PriceFeed extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'price-feed'; // References the singleton
    }
}
```

## Extension Points

### Creating a New Driver

1. Create driver class implementing `DriverInterface` or extending `AbstractDriver`
2. Implement `getPrice(Currency $currency): PriceData`
3. Override `withAuthentication()` if needed
4. Add configuration to `config/price-feed.php`

Example:

```php
namespace App\Drivers;

use JalalLinuX\PriceFeed\Drivers\AbstractDriver;

class MyCustomDriver extends AbstractDriver
{
    public function getPrice(Currency $currency): PriceData
    {
        $response = $this->getHttpClient()
            ->get('/price', ['symbol' => $currency->value]);

        return PriceData::from([
            'currency' => $currency,
            'price' => $response->json()['price'],
            // ... other fields
        ]);
    }
}
```

### Adding New Currencies

Simply add new cases to the `Currency` enum:

```php
// src/Enums/Currency.php

enum Currency: string
{
    // Existing currencies...

    // Add new currency
    case NEW_COIN = 'NEWCOIN';
}
```

## Design Patterns Used

1. **Manager Pattern** - For driver management
2. **Strategy Pattern** - Each driver is a strategy for fetching prices
3. **Factory Pattern** - Manager creates driver instances
4. **Repository Pattern** - Drivers act as repositories for price data
5. **DTO Pattern** - PriceData for data transfer
6. **Facade Pattern** - Simple interface to complex subsystem

## Dependencies

- **spatie/laravel-data** - For type-safe DTOs
- **spatie/laravel-package-tools** - For package scaffolding
- **illuminate/contracts** - Laravel core contracts
- **illuminate/support** - Laravel support utilities
- **illuminate/http** - HTTP client

## Testing Considerations

When writing tests for drivers:

1. Mock HTTP responses using Laravel's `Http::fake()`
2. Test currency support checks
3. Test error handling
4. Test cache behavior
5. Test DTO mapping

Example:

```php
use Illuminate\Support\Facades\Http;

test('can fetch BTC price from CoinGecko', function () {
    Http::fake([
        'api.coingecko.com/*' => Http::response([
            'market_data' => [
                'current_price' => ['usd' => 50000],
            ],
        ]),
    ]);

    $driver = new CoinGeckoDriver($config);
    $price = $driver->getPrice(Currency::BTC);

    expect($price->price)->toBe(50000.0);
});
```

## Performance Considerations

1. **Caching** - Reduces API calls significantly
2. **Lazy Loading** - Drivers only instantiated when needed
3. **Concurrent Requests** - Use `getPrices()` for batch requests
4. **HTTP Timeouts** - Configurable per driver
5. **Retry Logic** - Built into HTTP client (3 retries)

## Security Considerations

1. **API Keys** - Stored in `.env`, never committed
2. **HTTPS** - All drivers use HTTPS
3. **Rate Limiting** - Respect API provider limits
4. **Input Validation** - Enum ensures valid currencies
5. **Exception Handling** - Prevents sensitive data leakage
