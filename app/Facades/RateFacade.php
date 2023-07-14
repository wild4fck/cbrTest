<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getComparisonWithYesterday(\Carbon\Carbon $date, string $currencyCode, string $baseCurrencyCode = 'RUR')
 * @method static array getComparisonWithYesterdayForHalfYear(string $currencyCharCode, string $baseCurrencyCharCode = 'RUR')
 *
 * @see \App\Services\RateService\RateService
 */
class RateFacade extends Facade
{
    /** @inheritDoc */
    protected static function getFacadeAccessor(): string
    {
        return 'rate';
    }
}
