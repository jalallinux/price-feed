# TGJU Driver Documentation

## Overview

The TGJU driver integrates with [TGJU.org](https://www.tgju.org), Iran's leading financial data platform. TGJU provides real-time prices for cryptocurrencies, fiat currencies, and precious metals, all priced in Iranian Rial (IRR).

## Key Features

- ✅ **Free to use** - No API key required
- 🇮🇷 **Iranian Market Focus** - All prices in IRR (Iranian Rial)
- 📊 **Comprehensive Coverage** - Cryptocurrencies, forex, and precious metals
- ⚡ **Real-time Data** - Up-to-date market prices
- 🔄 **24h Statistics** - Price changes, highs, lows, and percentage changes

## API Information

- **Base URL:** `https://call5.tgju.org`
- **Endpoint:** `/ajax.json`
- **Authentication:** Not required
- **Rate Limit:** Not publicly documented
- **Response Format:** JSON

## Supported Assets

### Cryptocurrencies (in IRR)

| Currency | TGJU Key | Description |
|----------|----------|-------------|
| BTC | `btc-irr` | Bitcoin |
| ETH | `eth-irr` | Ethereum |
| USDT | `usdt-irr` | Tether |
| BNB | `crypto-binance-coin-irr` | Binance Coin |
| XRP | `xrp-irr` | Ripple |
| ADA | `crypto-cardano-irr` | Cardano |
| DOGE | `crypto-dogecoin-irr` | Dogecoin |
| SOL | `crypto-solana-irr` | Solana |
| TRX | `crypto-tron-irr` | Tron |
| DOT | `crypto-polkadot-irr` | Polkadot |
| LTC | `crypto-litecoin-irr` | Litecoin |
| SHIB | `crypto-shiba-inu-irr` | Shiba Inu |
| AVAX | `crypto-avalanche-irr` | Avalanche |
| UNI | `crypto-uniswap` | Uniswap |
| LINK | `crypto-chainlink-irr` | Chainlink |

### Fiat Currencies (to IRR)

| Currency | TGJU Key | Description |
|----------|----------|-------------|
| USD | `price_dollar_rl` | US Dollar |
| EUR | `price_eur` | Euro |
| GBP | `price_gbp` | British Pound |
| JPY | `usd-jpy-ask` | Japanese Yen |
| CNY | `usd-cny-ask` | Chinese Yuan |
| AUD | `price_aud` | Australian Dollar |
| CAD | `usd-cad-ask` | Canadian Dollar |
| CHF | `usd-chf-ask` | Swiss Franc |
| AED | `price_aed` | UAE Dirham |
| TRY | `price_try` | Turkish Lira |

### Precious Metals (in IRR)

| Metal | TGJU Key | Description |
|-------|----------|-------------|
| GOLD | `geram18` | Gold (18k, per gram) |
| SILVER | `silver` | Silver (per ounce) |

## Installation & Configuration

The TGJU driver is pre-configured in the package. No additional setup is required!

```php
// config/price-feed.php
'drivers' => [
    'tgju' => [
        'driver' => \JalalLinuX\PriceFeed\Drivers\TgjuDriver::class,
        'api_key' => null, // No API key needed!
        'base_url' => 'https://call5.tgju.org',
        'currencies' => [/* supported currencies */],
        'options' => [
            'timeout' => 10,
        ],
    ],
],
```

## Usage Examples

### Basic Usage

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Get Bitcoin price in IRR
$btcPrice = PriceFeed::getPrice(Currency::BTC, 'tgju');

echo "BTC Price: ﷼" . number_format($btcPrice->price) . " IRR\n";
echo "24h Change: " . $btcPrice->changePercentage24h . "%\n";
echo "24h High: ﷼" . number_format($btcPrice->high24h) . " IRR\n";
echo "24h Low: ﷼" . number_format($btcPrice->low24h) . " IRR\n";
```

### Get USD to IRR Exchange Rate

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

$usdToIRR = PriceFeed::getPrice(Currency::USD, 'tgju');

echo "USD/IRR: ﷼" . number_format($usdToIRR->price) . "\n";
echo "Change: " . $usdToIRR->changePercentage24h . "%\n";
```

### Get Multiple Cryptocurrency Prices

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

$cryptos = [
    Currency::BTC,
    Currency::ETH,
    Currency::USDT,
    Currency::BNB,
];

$tgju = PriceFeed::driver('tgju');
$prices = $tgju->getPrices($cryptos);

foreach ($prices as $currency => $priceData) {
    echo sprintf(
        "%s: ﷼%s IRR (Change: %s%%)\n",
        $currency,
        number_format($priceData->price),
        $priceData->changePercentage24h
    );
}
```

### Get Gold Price in Iran

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

// Gold price per gram (18k)
$gold = PriceFeed::getPrice(Currency::GOLD, 'tgju');

echo "Gold (18k) per gram: ﷼" . number_format($gold->price) . " IRR\n";
echo "High: ﷼" . number_format($gold->high24h) . " IRR\n";
echo "Low: ﷼" . number_format($gold->low24h) . " IRR\n";
```

### Currency Converter Dashboard

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;

class IranianMarketDashboard
{
    public function getExchangeRates(): array
    {
        $currencies = [
            Currency::USD,
            Currency::EUR,
            Currency::GBP,
            Currency::AED,
            Currency::TRY,
        ];

        $tgju = PriceFeed::driver('tgju');
        $rates = [];

        foreach ($currencies as $currency) {
            $price = $tgju->getPrice($currency);

            $rates[] = [
                'currency' => $currency->value,
                'rate' => $price->price,
                'formatted' => number_format($price->price),
                'change' => $price->changePercentage24h,
                'trend' => $price->changePercentage24h > 0 ? '↑' : '↓',
                'color' => $price->changePercentage24h > 0 ? 'green' : 'red',
            ];
        }

        return $rates;
    }

    public function getCryptoMarket(): array
    {
        $cryptos = [
            Currency::BTC,
            Currency::ETH,
            Currency::USDT,
            Currency::BNB,
            Currency::XRP,
        ];

        $tgju = PriceFeed::driver('tgju');
        $market = [];

        foreach ($cryptos as $crypto) {
            $price = $tgju->getPrice($crypto);

            $market[] = [
                'symbol' => $crypto->value,
                'price_irr' => $price->price,
                'formatted_price' => '﷼' . number_format($price->price),
                'change_24h' => $price->changePercentage24h,
                'high_24h' => $price->high24h,
                'low_24h' => $price->low24h,
                'last_update' => $price->timestamp->format('Y-m-d H:i:s'),
            ];
        }

        return $market;
    }
}

// Usage in a Controller
public function dashboard()
{
    $dashboard = new IranianMarketDashboard();

    return view('market.dashboard', [
        'exchange_rates' => $dashboard->getExchangeRates(),
        'crypto_market' => $dashboard->getCryptoMarket(),
    ]);
}
```

## Response Structure

TGJU returns data in the following format:

```json
{
  "current": {
    "btc-irr": {
      "p": "3,450,000,000",        // Current price
      "h": "3,500,000,000",        // 24h high
      "l": "3,400,000,000",        // 24h low
      "d": "50,000,000",           // 24h change
      "dp": 1.47,                  // 24h change percentage
      "dt": "high",                // Direction type
      "t": "۰۷:۵۹:۱۰",             // Time (Persian)
      "t_en": "07:59:10",          // Time (English)
      "ts": "2025-10-07 07:59:10" // Timestamp
    }
  }
}
```

## Special Considerations

### Price Format

TGJU returns prices as strings with comma separators (e.g., `"1,125,050,000"`). The driver automatically parses these to floats.

```php
// TGJU Response: "3,450,000,000"
// Parsed to: 3450000000.0
```

### Currency Denomination

All cryptocurrency and fiat currency prices are denominated in **IRR (Iranian Rial)**:

- `BTC-IRR`: Bitcoin price in Rials
- `USD`: US Dollar to Rial exchange rate
- `GOLD`: Gold per gram in Rials

### Data Freshness

TGJU provides real-time data with timestamps. Always check the `timestamp` field to verify data freshness:

```php
$btc = PriceFeed::getPrice(Currency::BTC, 'tgju');

if ($btc->timestamp > now()->subMinutes(5)) {
    echo "Data is fresh!";
} else {
    echo "Data might be stale";
}
```

### Limitations

- ❌ **No Volume Data**: TGJU doesn't provide 24h trading volume
- ❌ **No Market Cap**: Market capitalization not available
- ⚠️ **IRR Only**: All prices are in Iranian Rial (no USD or other base currencies)
- ⚠️ **Rate Limits**: Not publicly documented, use caching to reduce API calls

## Caching Recommendations

Since TGJU is a free API, it's recommended to enable caching:

```php
// config/price-feed.php
'cache' => [
    'enabled' => true,
    'ttl' => 300, // 5 minutes (recommended for TGJU)
    'prefix' => 'price_feed',
],
```

## Error Handling

```php
use JalalLinuX\PriceFeed\Facades\PriceFeed;
use JalalLinuX\PriceFeed\Enums\Currency;
use JalalLinuX\PriceFeed\Exceptions\ApiException;
use JalalLinuX\PriceFeed\Exceptions\UnsupportedCurrencyException;

try {
    $price = PriceFeed::getPrice(Currency::BTC, 'tgju');

} catch (UnsupportedCurrencyException $e) {
    // Currency not supported by TGJU
    \Log::warning('Currency not supported', [
        'currency' => Currency::BTC->value,
        'driver' => 'tgju',
    ]);

} catch (ApiException $e) {
    // API request failed
    \Log::error('TGJU API error', [
        'message' => $e->getMessage(),
    ]);

    // Use cached data as fallback
    $price = \Cache::get('tgju_btc_fallback');
}
```

## Performance Tips

1. **Use Batch Requests**: When fetching multiple prices, use `getPrices()` instead of multiple `getPrice()` calls
2. **Enable Caching**: Set appropriate TTL (5-10 minutes recommended)
3. **Handle Stale Data**: TGJU data can sometimes be delayed, always check timestamps
4. **Fallback Strategy**: Keep a fallback mechanism for when TGJU is unavailable

## Comparison with Other Drivers

| Feature | TGJU | CoinGecko | Binance |
|---------|------|-----------|---------|
| API Key | ❌ Not Required | ⚠️ Optional | ⚠️ Optional |
| Price Currency | IRR only | USD, EUR, etc. | USD |
| Volume Data | ❌ No | ✅ Yes | ✅ Yes |
| Market Cap | ❌ No | ✅ Yes | ❌ No |
| Fiat Currencies | ✅ Yes | ❌ No | ❌ No |
| Precious Metals | ✅ Yes | ❌ No | ❌ No |
| Iranian Market | ✅ Optimized | ❌ No | ❌ No |

## Use Cases

### Best For:
- 🇮🇷 Iranian market applications
- 💱 IRR exchange rate tracking
- 📊 Local crypto prices
- 🏦 Financial dashboards in Iran
- 💰 Gold/Silver trading in Iranian market

### Not Ideal For:
- 🌍 Global price comparisons (USD-based)
- 📈 Volume analysis
- 💹 Market cap tracking
- 🔄 High-frequency trading

## Additional Resources

- **TGJU Website**: https://www.tgju.org
- **API Endpoint**: https://call5.tgju.org/ajax.json
- **Package Documentation**: [README.md](../README.md)
- **Architecture Guide**: [ARCHITECTURE.md](../ARCHITECTURE.md)

## Support

For issues specific to TGJU integration:
1. Check TGJU website status
2. Verify API endpoint is accessible
3. Review error logs
4. Report issues at: https://github.com/jalallinux/price-feed/issues