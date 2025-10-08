# Price Feed

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jalallinux/price-feed.svg?style=flat-square)](https://packagist.org/packages/jalallinux/price-feed)
[![Total Downloads](https://img.shields.io/packagist/dt/jalallinux/price-feed.svg?style=flat-square)](https://packagist.org/packages/jalallinux/price-feed)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

A comprehensive Laravel package for fetching real-time prices of multiple asset types including cryptocurrencies, fiat currencies, gold, silver, and precious metal derivatives through a unified driver-based architecture.

## Features

- **Multi-Asset Support**: Cryptocurrencies, fiat currencies, and precious metals
- **Driver-Based Architecture**: Easily extensible with multiple data providers
- **Built-in Caching**: Configurable caching with TTL support
- **Type Safety**: Full PHP 8.4+ type safety with enums and DTOs
- **Laravel Integration**: Seamless Laravel service provider and facade integration
- **Multiple Providers**: Support for TGJU, Brsapi, and TGN APIs
- **Comprehensive Testing**: Full test coverage with Pest PHP

## Supported Assets

### Cryptocurrencies
- Bitcoin (BTC), Ethereum (ETH), Tether (USDT)
- Binance Coin (BNB), XRP, Cardano (ADA)
- Dogecoin (DOGE), Solana (SOL), TRON (TRX)
- Polkadot (DOT), Polygon (MATIC), Litecoin (LTC)
- Shiba Inu (SHIB), Avalanche (AVAX)
- Uniswap (UNI), Chainlink (LINK)

### Fiat Currencies
- USD, EUR, GBP, JPY, CNY
- AUD, CAD, CHF, IRR, AED, TRY

### Precious Metals
- Gold (various purities and forms)
- Silver (999, 925, ounce)
- Platinum, Palladium
- Iranian Gold Coins (various denominations)

## Installation

You can install the package via Composer:

```bash
composer require jalallinux/price-feed
```

The package will automatically register its service provider and facade.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="JalalLinuX\PriceFeed\PriceFeedServiceProvider" --tag="config"
```

Configure your environment variables:

```env
# Default driver
PRICE_FEED_DRIVER=tgju

# TGJU Configuration
TGJU_CACHE_ENABLED=true
TGJU_CACHE_TTL=120

# Brsapi Configuration
BRSAPI_API_KEY=your_api_key_here
BRSAPI_CACHE_ENABLED=true
BRSAPI_CACHE_TTL=120

# TGN Configuration
TGN_USERNAME=your_username
TGN_API_KEY=your_api_key
TGN_CACHE_ENABLED=true
TGN_CACHE_TTL=120
```

## Usage

### Basic Usage

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Get price for a single currency
$btcPrice = PriceFeed::price(Currency::BTC);

// Get prices for multiple currencies
$prices = PriceFeed::prices([
    Currency::BTC,
    Currency::ETH,
    Currency::USD
]);

// Get all supported currencies
$supportedCurrencies = PriceFeed::supportedCurrencies();

// Get available drivers
$drivers = PriceFeed::availableDrivers();
```

### Using Specific Drivers

```php
// Use a specific driver
$btcPrice = PriceFeed::driver('brsapi')->getPrice(Currency::BTC);

// Get prices from specific driver
$prices = PriceFeed::driver('tgn')->getPrices([
    Currency::USD,
    Currency::EUR
]);
```

### Working with Price Data

```php
$priceData = PriceFeed::price(Currency::BTC);

echo $priceData->currency->value; // BTC
echo $priceData->price; // 125000000.0
echo $priceData->unit->value; // IRR
echo $priceData->symbol; // BTC
echo $priceData->change24h; // 2500000.0
echo $priceData->changePercentage24h; // 2.04
echo $priceData->high24h; // 130000000.0
echo $priceData->low24h; // 120000000.0
echo $priceData->timestamp->format('Y-m-d H:i:s'); // 2024-01-15 14:30:00
```

### Cache Management

```php
// Clear cache for specific currency
PriceFeed::clearCache(Currency::BTC);

// Clear cache for specific driver
PriceFeed::clearCache(null, 'tgju');

// Clear all cache
PriceFeed::clearCache();
```

## Available Drivers

### TGJU Driver
- **Provider**: TGJU (Tehran Gold and Jewelry Union)
- **Coverage**: Fiat currencies, precious metals
- **Unit**: Iranian Rial (IRR)
- **Authentication**: None required
- **API**: Free public API

### Brsapi Driver
- **Provider**: Brsapi.ir
- **Coverage**: Cryptocurrencies, fiat currencies, precious metals
- **Unit**: Iranian Toman (IRT)
- **Authentication**: API key required
- **API**: Commercial API

### TGN Driver
- **Provider**: TGN Services
- **Coverage**: Fiat currencies, precious metals, coins
- **Unit**: Iranian Toman (IRT)
- **Authentication**: Username and API key required
- **API**: Commercial API

## Configuration Reference

The package configuration is located in `config/price-feed.php`:

```php
return [
    'default' => env('PRICE_FEED_DRIVER', 'tgju'),
    
    'drivers' => [
        'tgju' => [
            'driver' => \JalalLinuX\PriceFeed\Drivers\TgjuDriver::class,
            'base_url' => 'https://call5.tgju.org',
            'unit' => \JalalLinuX\PriceFeed\Enums\CurrencyUnit::IRR,
            'cache_enabled' => true,
            'cache_ttl' => 120,
            'currencies' => [
                // Configured currencies
            ],
        ],
        // Other drivers...
    ],
];
```

## Testing

Run the tests with:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

## Code Quality

Format code with Laravel Pint:

```bash
composer format
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email smjjalalzadeh93@gmail.com instead of using the issue tracker.

## Credits

- [JalalLinuX](https://github.com/jalallinux)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

If you find this package useful, please consider starring it on GitHub and sharing it with others!