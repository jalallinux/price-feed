<?php

namespace JalalLinuX\PriceFeed\Enums;

use Spatie\LaravelData\Concerns\EnumerableMethods;

enum CurrencyUnit: string
{
    case IRR = 'IRR';
    case IRT = 'IRT';
    case USD = 'USD';
    case EUR = 'EUR';
}
