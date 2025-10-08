<?php

namespace JalalLinuX\PriceFeed\DataTransferObjects;

use JalalLinuX\PriceFeed\Enums\Currency;
use JalalLinuX\PriceFeed\Enums\CurrencyUnit;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
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
    ) {
        $this->timestamp ??= new \DateTime;
    }

    public function toArray(): array
    {
        return [
            'currency' => $this->currency->value,
            'price' => $this->price,
            'unit' => $this->unit->value,
            'symbol' => $this->symbol,
            'change24h' => $this->change24h,
            'changePercentage24h' => $this->changePercentage24h,
            'high24h' => $this->high24h,
            'low24h' => $this->low24h,
            'volume24h' => $this->volume24h,
            'marketCap' => $this->marketCap,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s'),
            'raw' => $this->raw,
        ];
    }
}
