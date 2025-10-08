# Price Feed Architecture

This document outlines the architecture and design patterns used in the Price Feed package.

## Overview

The Price Feed package follows a driver-based architecture that allows for easy integration with multiple price data providers. The package is built using Laravel's service container and follows SOLID principles for maintainability and extensibility.

## Core Components

### 1. Service Provider (`PriceFeedServiceProvider`)

The service provider registers the package with Laravel's service container:

- Registers the `PriceFeedManager` as a singleton
- Publishes configuration files
- Registers the facade alias

```php
$this->app->singleton('price-feed', function ($app) {
    return new PriceFeedManager($app);
});
```

### 2. Manager (`PriceFeedManager`)

The manager extends Laravel's `Manager` class and handles:

- Driver creation and management
- Price fetching with caching
- Currency validation
- Error handling

**Key Methods:**
- `getPrice(Currency $currency, ?string $driver = null)`: Fetch single currency price
- `getPrices(array $currencies, ?string $driver = null)`: Fetch multiple currency prices
- `getSupportedCurrencies(?string $driver = null)`: Get supported currencies
- `clearCache(?Currency $currency = null, ?string $driver = null)`: Clear cache

### 3. Static Facade (`PriceFeed`)

Provides a clean static interface to the manager:

```php
PriceFeed::price(Currency::BTC);
PriceFeed::prices([Currency::BTC, Currency::ETH]);
PriceFeed::supportedCurrencies();
```

### 4. Driver Interface (`DriverInterface`)

Defines the contract that all drivers must implement:

```php
interface DriverInterface
{
    public function getPrice(Currency $currency): PriceData;
    public function getPrices(array $currencies): Collection;
    public function getSupportedCurrencies(): array;
    public function supports(Currency $currency): bool;
    public function getName(): string;
}
```

### 5. Abstract Driver (`AbstractDriver`)

Provides common functionality for all drivers:

- HTTP client configuration
- Authentication handling
- Caching implementation
- Error handling
- Currency validation

**Key Features:**
- Configurable timeout and retry logic
- Built-in caching with TTL support
- Standardized error handling
- Authentication abstraction

## Data Structures

### 1. Currency Enum (`Currency`)

Defines all supported currencies with helper methods:

```php
enum Currency: string
{
    case BTC = 'BTC';
    case ETH = 'ETH';
    // ... more currencies
    
    public function isCryptocurrency(): bool;
    public function isFiat(): bool;
    public function isPreciousMetal(): bool;
}
```

**Categories:**
- **Cryptocurrencies**: BTC, ETH, USDT, etc.
- **Fiat Currencies**: USD, EUR, GBP, etc.
- **Precious Metals**: Gold, Silver, Platinum, etc.

### 2. Currency Unit Enum (`CurrencyUnit`)

Defines the units for price display:

```php
enum CurrencyUnit: string
{
    case IRR = 'IRR';  // Iranian Rial
    case IRT = 'IRT';  // Iranian Toman
    case USD = 'USD';  // US Dollar
    case EUR = 'EUR';  // Euro
}
```

### 3. Price Data DTO (`PriceData`)

Immutable data transfer object for price information:

```php
class PriceData extends Data
{
    public function __construct(
        public Currency $currency,
        public float $price,
        public CurrencyUnit $unit,
        public ?string $symbol = null,
        public ?float $change24h = null,
        public ?float $changePercentage24h = null,
        public ?float $high24h = null,
        public ?float $low24h = null,
        public ?float $volume24h = null,
        public ?float $marketCap = null,
        public ?\DateTimeInterface $timestamp = null,
        public array $raw = []
    ) {}
}
```

## Driver Implementation

### Driver Structure

Each driver extends `AbstractDriver` and implements:

1. **Currency Mapping**: Maps internal currency enums to API-specific keys
2. **API Integration**: Handles HTTP requests to external APIs
3. **Data Parsing**: Converts API responses to `PriceData` objects
4. **Error Handling**: Provides meaningful error messages

### Current Drivers

#### 1. TGJU Driver (`TgjuDriver`)

- **API**: Free public API from Tehran Gold and Jewelry Union
- **Coverage**: Fiat currencies, precious metals
- **Unit**: Iranian Rial (IRR)
- **Authentication**: None required
- **Caching**: Single API call cached for all currencies

**Key Features:**
- Handles comma-separated price strings
- Single API endpoint for all data
- Automatic timestamp parsing

#### 2. Brsapi Driver (`BrsapiDriver`)

- **API**: Commercial API from brsapi.ir
- **Coverage**: Cryptocurrencies, fiat currencies, precious metals
- **Unit**: Iranian Toman (IRT)
- **Authentication**: API key required
- **Caching**: Separate cache per endpoint type

**Key Features:**
- Multiple API endpoints (crypto, currency, commodity)
- Complex data extraction logic
- Market cap and volume data

#### 3. TGN Driver (`TgnDriver`)

- **API**: Commercial API from TGN Services
- **Coverage**: Fiat currencies, precious metals, coins
- **Unit**: Iranian Toman (IRT)
- **Authentication**: Username and API key required
- **Caching**: Single API call cached for all currencies

**Key Features:**
- URL-based authentication
- Simple price extraction
- Iranian coin support

## Caching Strategy

### Multi-Level Caching

1. **Driver-Level Caching**: Each driver caches its own API responses
2. **Manager-Level Caching**: Manager caches processed `PriceData` objects
3. **Configurable TTL**: Each driver can have different cache durations

### Cache Keys

```php
// Driver-level cache
"{cache_prefix}:{driver_name}:api_response"

// Manager-level cache
"{cache_prefix}:{driver_name}:{currency}"
```

### Cache Management

- Automatic cache invalidation based on TTL
- Manual cache clearing methods
- Configurable cache prefixes

## Error Handling

### Exception Hierarchy

```
PriceFeedException (base)
├── ApiException
├── DriverNotFoundException
└── UnsupportedCurrencyException
```

### Error Scenarios

1. **API Failures**: Network issues, invalid responses
2. **Driver Not Found**: Invalid driver configuration
3. **Unsupported Currency**: Currency not supported by driver
4. **Configuration Errors**: Missing API keys, invalid URLs

## Configuration Management

### Environment Variables

```env
PRICE_FEED_DRIVER=tgju
TGJU_CACHE_ENABLED=true
TGJU_CACHE_TTL=120
BRSAPI_API_KEY=your_key
TGN_USERNAME=your_username
TGN_API_KEY=your_key
```

### Driver Configuration

Each driver is configured with:

- **Driver Class**: The driver implementation
- **API Credentials**: Keys, usernames, etc.
- **Base URL**: API endpoint
- **Unit**: Currency unit for prices
- **Cache Settings**: TTL, prefix, enabled flag
- **Supported Currencies**: Array of supported currencies
- **Options**: Timeout, retry settings

## Testing Strategy

### Test Structure

- **Unit Tests**: Individual component testing
- **Integration Tests**: Driver API integration testing
- **Architecture Tests**: Package structure validation

### Test Coverage

- All drivers tested with real API calls
- Currency validation testing
- Error handling verification
- Cache behavior testing

## Extension Points

### Adding New Drivers

1. Create driver class extending `AbstractDriver`
2. Implement required methods
3. Add driver configuration
4. Register in service provider

### Adding New Currencies

1. Add currency to `Currency` enum
2. Update driver mappings
3. Add to driver configurations
4. Update tests

### Custom Caching

Override `getCachedPrice()` method in drivers for custom caching logic.

## Performance Considerations

### API Call Optimization

- Single API calls cached for multiple currencies
- Configurable timeouts and retries
- Efficient data parsing

### Memory Management

- Immutable data objects
- Efficient collection handling
- Proper resource cleanup

### Scalability

- Stateless driver instances
- Configurable cache TTL
- Horizontal scaling support

## Security Considerations

### API Key Management

- Environment variable storage
- No hardcoded credentials
- Secure configuration handling

### Data Validation

- Input sanitization
- Type safety enforcement
- Error message sanitization

### Rate Limiting

- Built-in retry logic
- Configurable timeouts
- Cache-based rate limiting

## Future Enhancements

### Planned Features

1. **WebSocket Support**: Real-time price updates
2. **More Drivers**: Additional API providers
3. **Price Alerts**: Notification system
4. **Historical Data**: Price history tracking
5. **Webhook Support**: Event-driven updates

### Architecture Improvements

1. **Event System**: Laravel events for price updates
2. **Queue Integration**: Background price fetching
3. **Metrics Collection**: Performance monitoring
4. **Health Checks**: Driver status monitoring