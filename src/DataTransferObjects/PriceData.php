<?php

namespace JalalLinuX\PriceFeed\DataTransferObjects;

use JalalLinuX\PriceFeed\Enums\Currency;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class PriceData extends Data
{
    public function __construct(
        public Currency $currency,
        public float $price,
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
}
