<?php

namespace App\Services\RateService\Clients\Interfaces;

use Carbon\Carbon;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
interface RateClientInterface
{
    /**
     * @param \Carbon\Carbon $date
     * @param string         $currencyCode
     * @param string         $baseCurrencyCode
     *
     * @return float
     */
    public function getRate(Carbon $date, string $currencyCode, string $baseCurrencyCode): float;
    
    /**
     * @param string $currencyCharCode
     *
     * @return array
     */
    public function getHalfYearRates(string $currencyCharCode): array;
}
