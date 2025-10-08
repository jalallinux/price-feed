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
                Currency::IR_GOLD_18,
                Currency::SILVER_OUNCE,
            ],
            'options' => [
                'timeout' => 10,
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
                Currency::IR_GOLD_18,
                Currency::SILVER_OUNCE,
            ],
            'options' => [
                'timeout' => 10,
            ],
        ],

        'tgn' => [
            'driver' => \JalalLinuX\PriceFeed\Drivers\TgnDriver::class,
            'username' => env('TGN_USERNAME'),
            'api_key' => env('TGN_API_KEY'),
            'base_url' => 'https://webservice.tgnsrv.ir',
            'cache_enabled' => (bool) env('TGN_CACHE_ENABLED', true),
            'cache_ttl' => env('TGN_CACHE_TTL', 120), // Cache duration in seconds (2 minutes for Iranian market)
            'cache_prefix' => 'price_feed',
            'currencies' => [
                // Fiat Currencies (exchange rates to IRR)
                Currency::USD,
                Currency::EUR,
                Currency::AED,
                // Precious Metals and Coins (prices in IRR)
                Currency::GOLD_OUNCE,
                Currency::IR_GOLD_18,
                Currency::IR_COIN_1G,
                Currency::IR_COIN_QUARTER,
                Currency::IR_COIN_HALF,
                Currency::IR_COIN_EMAMI,
                Currency::IR_COIN_BAHAR,
            ],
            'options' => [
                'timeout' => 10,
            ],
        ],

    ],
];
