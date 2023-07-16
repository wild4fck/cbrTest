<?php

namespace Tests\Feature\Services\RateService\ComparisonForHalfYear;

use Rate;
use Carbon\Carbon;
use Tests\TestCase;

class ComparisonForHalfYearTest extends TestCase
{
    /**
     * @dataProvider dataSet
     * @throws \JsonException
     */
    public function test_request(array $data): void
    {
        $knownDate = Carbon::create(2001, 5, 21, 12);
        Carbon::setTestNow($knownDate);
        
        $result = json_encode(Rate::getComparisonWithYesterdayForHalfYear($data['params']['currency_code'], $data['params']['base_currency_code'] ?? null), JSON_THROW_ON_ERROR);
        
        $this->assertJson($result, $data['result']);
    }
    
    /**
     * Массив кейсов для проверки с запрещенным банком и без
     *
     * @return array[]
     */
    public static function dataSet(): array
    {
        return [
            'Кейс 1 (Успешный запрос без базовой валюты)' => [
                [
                    'params' => [
                        'currency_code' => 'USD',
                    ],
                    'result' => ResultEnum::CASE_1->result()
                ],
            ],
            'Кейс 2 (Успешный запрос c базовой валютой)' => [
                [
                    'params' => [
                        'currency_code' => 'EUR',
                        'base_currency_code' => 'USD',
                    ],
                    'result' => ResultEnum::CASE_2->result()
                ],
            ],
            'Кейс 3 (Неудачный запрос c неизвестной валютой)' => [
                [
                    'params' => [
                        'currency_code' => 'EURR',
                    ],
                    'result' => ResultEnum::CASE_3->result()
                ],
            ],
            'Кейс 4 (Неудачный запрос c неизвестной базовой)' => [
                [
                    'params' => [
                        'currency_code' => 'EUR',
                        'base_currency_code' => 'USDD',
                    ],
                    'result' => ResultEnum::CASE_3->result()
                ],
            ],
        ];
    }
}
