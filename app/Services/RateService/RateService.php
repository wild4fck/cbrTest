<?php

namespace App\Services\RateService;

use Carbon\Carbon;
use App\Services\RateService\Clients\Interfaces\RateClientInterface;

/**
 * @author Wild4fck <wild4fck@yandex.ru>
 */
class RateService
{
    /**
     * @var \App\Services\RateService\Clients\Interfaces\RateClientInterface
     */
    protected RateClientInterface $client;
    
    /**
     * @param \App\Services\RateService\Clients\Interfaces\RateClientInterface $client
     */
    public function __construct(RateClientInterface $client)
    {
        $this->client = $client;
    }
    
    /**
     * @param \Carbon\Carbon $date
     * @param string         $currencyCode
     * @param null|string    $baseCurrencyCode
     *
     * @return array
     */
    public function getComparisonWithYesterday(Carbon $date, string $currencyCode, ?string $baseCurrencyCode): array
    {
        $baseCurrencyCode = $baseCurrencyCode ?? 'RUR';
        
        $rate = $this->client->getRate($date, $currencyCode, $baseCurrencyCode);
        // Получение разницы с предыдущим торговым днем
        $previousRate = $this->client->getRate((clone $date)->subDay(), $currencyCode, $baseCurrencyCode);
        $difference = $rate - $previousRate;
        
        return [
            'rate' => $rate,
            'difference' => round($difference, 4),
        ];
    }
    
    /**
     * @param string      $currencyCharCode
     * @param null|string $baseCurrencyCharCode
     *
     * @return array
     */
    public function getComparisonWithYesterdayForHalfYear(string $currencyCharCode, ?string $baseCurrencyCharCode): array
    {
        $baseCurrencyCharCode = $baseCurrencyCharCode ?? 'RUR';
        
        $result = [];
        
        $halfYearRates = $this->client->getHalfYearRates($currencyCharCode);
        
        if ($baseCurrencyCharCode !== 'RUR') {
            $baseCurrencyHalfYearRates = $this->client->getHalfYearRates($baseCurrencyCharCode);
            $halfYearRates = array_map(static function ($el1, $el2) {
                return [
                    'date' => $el1['date'],
                    'value' => $el1['value'] / $el2['value'],
                ];
            }, $halfYearRates, $baseCurrencyHalfYearRates);
        }
        
        
        foreach ($halfYearRates as $key => $record) {
            if ($key === 0) {
                continue;
            }
            
            $result[] = [
                'rate' => $record['value'],
                'difference' => round($record['value'] - $halfYearRates[$key - 1]['value'], 4),
                'date' => $record['date'],
            ];
        }
        
        return $result;
    }
}
