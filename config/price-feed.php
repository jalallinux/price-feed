<?php

use JalalLinuX\PriceFeed\Enums\Currency;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This option defines the default price feed driver that will be used
    | by the package. You may change this to any of the configured drivers.
    |
    */
    'default' => env('PRICE_FEED_DRIVER', 'tgju'),

    /*
    |--------------------------------------------------------------------------
    | Price Feed Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the drivers for fetching price feeds. Each driver
    | can have its own API keys, base URLs, and supported currencies.
    |
    */
    'drivers' => [

        'brsapi' => [
            'driver' => \JalalLinuX\PriceFeed\Drivers\BrsapiDriver::class,
            'api_key' => env('BRSAPI_API_KEY'),
            'base_url' => 'https://brsapi.ir',
            'cache_enabled' => (bool) env('BRSAPI_CACHE_ENABLED', true),
            'cache_ttl' => env('BRSAPI_CACHE_TTL', 120), // Cache duration in seconds (2 minutes)
            'cache_prefix' => 'price_feed',
            'currencies' => [
                // Cryptocurrencies (prices in Toman)
                Currency::BTC,
                Currency::ETH,
                Currency::USDT,
                Currency::BNB,
                Currency::XRP,
                Currency::ADA,
                Currency::DOGE,
                Currency::SOL,
                Currency::TRX,
                Currency::DOT,
                Currency::LTC,
                Currency::SHIB,
                Currency::AVAX,
                Currency::UNI,
                Currency::LINK,
                Currency::MATIC,
                // Fiat Currencies (exchange rates to Toman)
                Currency::USD,
                Currency::EUR,
                Currency::GBP,
                Currency::JPY,
                Currency::CNY,
                Currency::AUD,
                Currency::CAD,
                Currency::CHF,
                Currency::AED,
                Currency::TRY,
                // Precious Metals (prices in Toman)
                Currency::GOLD,
                Currency::SILVER,
                Currency::PLATINUM,
                Currency::PALLADIUM,
            ],
            'options' => [
                'timeout' => 10,
            ],
        ],

        'goldapi' => [
            'driver' => \JalalLinuX\PriceFeed\Drivers\GoldApiDriver::class,
            'api_key' => env('GOLDAPI_API_KEY'),
            'base_url' => 'https://www.goldapi.io/api',
            'cache_enabled' => (bool) env('GOLDAPI_CACHE_ENABLED', true),
            'cache_ttl' => env('GOLDAPI_CACHE_TTL', 300), // Cache duration in seconds (5 minutes for precious metals)
            'cache_prefix' => 'price_feed',
            'currencies' => [
                Currency::GOLD,
                Currency::SILVER,
                Currency::PLATINUM,
                Currency::PALLADIUM,
            ],
            'options' => [
                'timeout' => 10,
                'base_currency' => 'USD',
            ],
        ],

        'tgju' => [
            'driver' => \JalalLinuX\PriceFeed\Drivers\TgjuDriver::class,
            'api_key' => env('TGJU_API_KEY'),
            'base_url' => 'https://call5.tgju.org',
            'cache_enabled' => (bool) env('TGJU_CACHE_ENABLED', true),
            'cache_ttl' => env('TGJU_CACHE_TTL', 120), // Cache duration in seconds (2 minutes for Iranian market)
            'cache_prefix' => 'price_feed',
            'currencies' => [
                // Fiat Currencies (exchange rates to IRR)
                Currency::USD,
                Currency::EUR,
                Currency::GBP,
                Currency::JPY,
                Currency::CNY,
                Currency::AUD,
                Currency::CAD,
                Currency::CHF,
                Currency::AED,
                Currency::TRY,
                // Precious Metals (prices in IRR)
                Currency::GOLD,
                Currency::SILVER,
            ],
            'options' => [
                'timeout' => 10,
            ],
        ],

    ],
];
