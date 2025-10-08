<?php

namespace JalalLinuX\PriceFeed\Enums;

enum Currency: string
{
    // Cryptocurrencies
    case BTC = 'BTC';
    case ETH = 'ETH';
    case USDT = 'USDT';
    case BNB = 'BNB';
    case XRP = 'XRP';
    case ADA = 'ADA';
    case DOGE = 'DOGE';
    case SOL = 'SOL';
    case TRX = 'TRX';
    case DOT = 'DOT';
    case MATIC = 'MATIC';
    case LTC = 'LTC';
    case SHIB = 'SHIB';
    case AVAX = 'AVAX';
    case UNI = 'UNI';
    case LINK = 'LINK';

    // Fiat Currencies
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case JPY = 'JPY';
    case CNY = 'CNY';
    case AUD = 'AUD';
    case CAD = 'CAD';
    case CHF = 'CHF';
    case IRR = 'IRR';
    case AED = 'AED';
    case TRY = 'TRY';

    // Precious Metals
    case GOLD_OUNCE = 'GOLD_OUNCE';
    case IR_GOLD_18 = 'IR_GOLD_18';
    case IR_GOLD_24 = 'IR_GOLD_24';
    case IR_GOLD_MELTED = 'IR_GOLD_MELTED';
    case IR_COIN_1G = 'IR_COIN_1G';
    case IR_COIN_QUARTER = 'IR_COIN_QUARTER';
    case IR_COIN_HALF = 'IR_COIN_HALF';
    case IR_COIN_EMAMI = 'IR_COIN_EMAMI';
    case IR_COIN_BAHAR = 'IR_COIN_BAHAR';

    case SILVER_OUNCE = 'SILVER_OUNCE';
    case SILVER_999 = 'SILVER_999';
    case SILVER_925 = 'SILVER_925';

    case PLATINUM_OUNCE = 'PLATINUM_OUNCE';
    case PALLADIUM_OUNCE = 'PALLADIUM_OUNCE';

    /**
     * Get all cryptocurrency cases
     */
    public static function cryptocurrencies(): array
    {
        return [
            self::BTC,
            self::ETH,
            self::USDT,
            self::BNB,
            self::XRP,
            self::ADA,
            self::DOGE,
            self::SOL,
            self::TRX,
            self::DOT,
            self::MATIC,
            self::LTC,
            self::SHIB,
            self::AVAX,
            self::UNI,
            self::LINK,
        ];
    }

    /**
     * Get all fiat currency cases
     */
    public static function fiatCurrencies(): array
    {
        return [
            self::USD,
            self::EUR,
            self::GBP,
            self::JPY,
            self::CNY,
            self::AUD,
            self::CAD,
            self::CHF,
            self::IRR,
            self::AED,
            self::TRY,
        ];
    }

    /**
     * Get all precious metal cases
     */
    public static function preciousMetals(): array
    {
        return [
            self::GOLD_OUNCE,
            self::IR_GOLD_18,
            self::IR_GOLD_24,
            self::IR_GOLD_MELTED,
            self::IR_COIN_1G,
            self::IR_COIN_QUARTER,
            self::IR_COIN_HALF,
            self::IR_COIN_EMAMI,
            self::IR_COIN_BAHAR,
            self::SILVER_OUNCE,
            self::SILVER_999,
            self::SILVER_925,
            self::PLATINUM_OUNCE,
            self::PALLADIUM_OUNCE,
        ];
    }

    /**
     * Check if currency is a cryptocurrency
     */
    public function isCryptocurrency(): bool
    {
        return in_array($this, self::cryptocurrencies());
    }

    /**
     * Check if currency is a fiat currency
     */
    public function isFiat(): bool
    {
        return in_array($this, self::fiatCurrencies());
    }

    /**
     * Check if currency is a precious metal
     */
    public function isPreciousMetal(): bool
    {
        return in_array($this, self::preciousMetals());
    }
}
