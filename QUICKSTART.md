# Quick Start Guide

Get up and running with the Price Feed package in minutes.

## Installation

### 1. Install via Composer

```bash
composer require jalallinux/price-feed
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --provider="JalalLinuX\PriceFeed\PriceFeedServiceProvider" --tag="config"
```

### 3. Configure Environment Variables

Add these to your `.env` file:

```env
# Default driver (tgju, brsapi, or tgn)
PRICE_FEED_DRIVER=tgju

# TGJU Configuration (Free - No API key required)
TGJU_CACHE_ENABLED=true
TGJU_CACHE_TTL=120

# Brsapi Configuration (Requires API key)
BRSAPI_API_KEY=your_brsapi_key_here
BRSAPI_CACHE_ENABLED=true
BRSAPI_CACHE_TTL=120

# TGN Configuration (Requires username and API key)
TGN_USERNAME=your_username
TGN_API_KEY=your_tgn_api_key
TGN_CACHE_ENABLED=true
TGN_CACHE_TTL=120
```

## Basic Usage

### Get a Single Price

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Get Bitcoin price
$btcPrice = PriceFeed::price(Currency::BTC);
echo $btcPrice->price; // 125000000.0
echo $btcPrice->unit->value; // IRR
```

### Get Multiple Prices

```php
$prices = PriceFeed::prices([
    Currency::BTC,
    Currency::ETH,
    Currency::USD
]);

foreach ($prices as $currency => $priceData) {
    echo "{$currency}: {$priceData->price} {$priceData->unit->value}\n";
}
```

### Get All Supported Currencies

```php
$currencies = PriceFeed::supportedCurrencies();
// Returns array of Currency enum cases
```

## Using Different Drivers

### TGJU Driver (Free)

```php
// TGJU provides fiat currencies and precious metals in IRR
$usdPrice = PriceFeed::driver('tgju')->getPrice(Currency::USD);
$goldPrice = PriceFeed::driver('tgju')->getPrice(Currency::IR_GOLD_18);
```

### Brsapi Driver (Requires API Key)

```php
// Brsapi provides cryptocurrencies, fiat currencies, and precious metals in IRT
$btcPrice = PriceFeed::driver('brsapi')->getPrice(Currency::BTC);
$ethPrice = PriceFeed::driver('brsapi')->getPrice(Currency::ETH);
```

### TGN Driver (Requires Credentials)

```php
// TGN provides fiat currencies and precious metals in IRT
$usdPrice = PriceFeed::driver('tgn')->getPrice(Currency::USD);
$goldPrice = PriceFeed::driver('tgn')->getPrice(Currency::GOLD_OUNCE);
```

## Working with Price Data

### Accessing Price Information

```php
$priceData = PriceFeed::price(Currency::BTC);

// Basic information
echo $priceData->currency->value; // BTC
echo $priceData->price; // 125000000.0
echo $priceData->unit->value; // IRR
echo $priceData->symbol; // BTC

// Market data (if available)
echo $priceData->change24h; // 2500000.0
echo $priceData->changePercentage24h; // 2.04
echo $priceData->high24h; // 130000000.0
echo $priceData->low24h; // 120000000.0
echo $priceData->volume24h; // 1500000000.0
echo $priceData->marketCap; // 2500000000000.0

// Timestamp
echo $priceData->timestamp->format('Y-m-d H:i:s'); // 2024-01-15 14:30:00

// Raw API data
print_r($priceData->raw);
```

### Converting to Array

```php
$priceArray = $priceData->toArray();
/*
[
    'currency' => 'BTC',
    'price' => 125000000.0,
    'unit' => 'IRR',
    'symbol' => 'BTC',
    'change24h' => 2500000.0,
    'changePercentage24h' => 2.04,
    'high24h' => 130000000.0,
    'low24h' => 120000000.0,
    'volume24h' => 1500000000.0,
    'marketCap' => 2500000000000.0,
    'timestamp' => '2024-01-15 14:30:00',
    'raw' => [...]
]
*/
```

## Cache Management

### Clear Specific Cache

```php
// Clear cache for specific currency
PriceFeed::clearCache(Currency::BTC);

// Clear cache for specific driver
PriceFeed::clearCache(null, 'tgju');

// Clear all cache
PriceFeed::clearCache();
```

### Cache Configuration

Each driver can have different cache settings:

```php
// In config/price-feed.php
'tgju' => [
    'cache_enabled' => true,
    'cache_ttl' => 120, // 2 minutes
    'cache_prefix' => 'price_feed',
],
```

## Error Handling

### Catching Exceptions

```php
use JalalLinuX\PriceFeed\Exceptions\ApiException;
use JalalLinuX\PriceFeed\Exceptions\DriverNotFoundException;
use JalalLinuX\PriceFeed\Exceptions\UnsupportedCurrencyException;

try {
    $price = PriceFeed::price(Currency::BTC);
} catch (UnsupportedCurrencyException $e) {
    // Currency not supported by current driver
    echo "Currency not supported: " . $e->getMessage();
} catch (DriverNotFoundException $e) {
    // Driver not found or not configured
    echo "Driver not found: " . $e->getMessage();
} catch (ApiException $e) {
    // API request failed
    echo "API error: " . $e->getMessage();
}
```

## Common Use Cases

### 1. Display Current Prices

```php
public function index()
{
    $currencies = [
        Currency::BTC,
        Currency::ETH,
        Currency::USD,
        Currency::EUR
    ];
    
    $prices = PriceFeed::prices($currencies);
    
    return view('prices.index', compact('prices'));
}
```

### 2. Price Comparison

```php
public function comparePrices()
{
    $currency = Currency::BTC;
    
    $tgjuPrice = PriceFeed::driver('tgju')->getPrice($currency);
    $brsapiPrice = PriceFeed::driver('brsapi')->getPrice($currency);
    
    return [
        'tgju' => $tgjuPrice->price,
        'brsapi' => $brsapiPrice->price,
        'difference' => abs($tgjuPrice->price - $brsapiPrice->price)
    ];
}
```

### 3. Price Alerts

```php
public function checkPriceAlert(Currency $currency, float $threshold)
{
    $price = PriceFeed::price($currency);
    
    if ($price->price > $threshold) {
        // Send alert
        $this->sendAlert($currency, $price->price);
    }
}
```

### 4. Batch Processing

```php
public function updateAllPrices()
{
    $currencies = PriceFeed::supportedCurrencies();
    
    foreach ($currencies as $currency) {
        try {
            $price = PriceFeed::price($currency);
            $this->savePriceToDatabase($currency, $price);
        } catch (Exception $e) {
            Log::error("Failed to fetch price for {$currency->value}: " . $e->getMessage());
        }
    }
}
```

## Configuration Examples

### Full Configuration

```php
// config/price-feed.php
return [
    'default' => 'tgju',
    
'drivers' => [
        'tgju' => [
            'driver' => \JalalLinuX\PriceFeed\Drivers\TgjuDriver::class,
            'base_url' => 'https://call5.tgju.org',
            'unit' => \JalalLinuX\PriceFeed\Enums\CurrencyUnit::IRR,
            'cache_enabled' => true,
            'cache_ttl' => 120,
            'currencies' => [
                Currency::USD,
                Currency::EUR,
                Currency::IR_GOLD_18,
                // ... more currencies
            ],
            'options' => [
                'timeout' => 10,
            ],
        ],
        // ... other drivers
    ],
];
```

### Custom Driver Configuration

```php
// Add custom driver
'my_custom_driver' => [
        'driver' => \App\Drivers\MyCustomDriver::class,
    'api_key' => env('MY_API_KEY'),
    'base_url' => 'https://api.example.com',
    'unit' => \JalalLinuX\PriceFeed\Enums\CurrencyUnit::USD,
    'cache_enabled' => true,
    'cache_ttl' => 300,
        'currencies' => [
            Currency::BTC,
            Currency::ETH,
        ],
        'options' => [
        'timeout' => 15,
    ],
],
```

## Testing

### Basic Test

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

public function test_can_get_bitcoin_price()
{
    $price = PriceFeed::price(Currency::BTC);
    
    $this->assertInstanceOf(PriceData::class, $price);
    $this->assertEquals(Currency::BTC, $price->currency);
    $this->assertIsFloat($price->price);
    $this->assertGreaterThan(0, $price->price);
}
```

### Testing with Specific Driver

```php
public function test_tgju_driver_works()
{
    $driver = PriceFeed::driver('tgju');
    $price = $driver->getPrice(Currency::USD);
    
    $this->assertInstanceOf(PriceData::class, $price);
    $this->assertEquals(Currency::USD, $price->currency);
}
```

## Troubleshooting

### Common Issues

1. **Driver Not Found**
   - Check driver configuration in `config/price-feed.php`
   - Verify driver class exists and is properly namespaced

2. **API Key Issues**
   - Verify API keys are set in `.env` file
   - Check API key validity with provider

3. **Currency Not Supported**
   - Check if currency is in driver's supported currencies list
   - Verify currency mapping in driver implementation

4. **Cache Issues**
   - Clear cache: `PriceFeed::clearCache()`
   - Check cache configuration
   - Verify cache driver is working

### Debug Mode

Enable detailed error messages by setting:

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

## Next Steps

- Read the [Architecture Documentation](ARCHITECTURE.md) for advanced usage
- Check the [API Reference](docs/) for detailed method documentation
- Explore [Driver Documentation](docs/TGJU_DRIVER.md) for specific driver details
- See [Examples](examples/) for more complex use cases

## Support

- [GitHub Issues](https://github.com/jalallinux/price-feed/issues)
- [Documentation](https://github.com/jalallinux/price-feed#readme)
- [Email Support](mailto:smjjalalzadeh93@gmail.com)