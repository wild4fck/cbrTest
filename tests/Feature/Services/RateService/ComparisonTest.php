<?php

namespace Tests\Feature\Services\RateService;

use Tests\TestCase;

class ComparisonTest extends TestCase
{
    /**
     * @dataProvider dataSet
     */
    public function test_request(array $data): void
    {
        $response = $this->get('/api/comparison-with-yesterday?' . http_build_query($data['params']));
        
        $response
            ->assertStatus($data['status'])
            ->assertJson($data['json']);
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
                        'date' => '13.07.2005',
                        'currency_code' => 'EUR',
                    ],
                    'status' => 200,
                    'json' => [
                        'message' => 'Rate comparison with yesterday',
                        'data' => [
                            'rate' => 34.7844,
                            'difference' => 0.2857,
                        ],
                    ],
                ],
            ],
            'Кейс 2 (Успешный запрос с базовой валютой)' => [
                [
                    'params' => [
                        'date' => '13.07.2005',
                        'currency_code' => 'EUR',
                        'base_currency_code' => 'USD',
                    ],
                    'status' => 200,
                    'json' => [
                        'message' => 'Rate comparison with yesterday',
                        'data' => [
                            'rate' => 1.2165,
                            'difference' => 0.0151,
                        ],
                    ],
                ],
            ],
            'Кейс 3 (Неудачный запрос без параметров)' => [
                [
                    'params' => [],
                    'status' => 422,
                    'json' => [
                        'message' => 'Invalid data send',
                        'errors' => [
                            'date' => [
                                'The date field is required.',
                            ],
                            'currency_code' => [
                                'The currency code field is required.',
                            ],
                        ],
                    ],
                ],
            ],
            'Кейс 4 (Неудачный запрос c неверным форматом даты)' => [
                [
                    'params' => [
                        'date' => '01012010',
                        'currency_code' => 'EUR',
                    ],
                    'status' => 422,
                    'json' => [
                        'message' => 'Invalid data send',
                        'errors' => [
                            'date' => [
                                'The date field must be a valid date.'
                            ]
                        ]
                    ],
                ],
            ],
            'Кейс 5 (Неудачный запрос c неизвестным параметром CURRENCY_CODE)' => [
                [
                    'params' => [
                        'date' => '13.07.2005',
                        'currency_code' => 'EURR',
                    ],
                    'status' => 400,
                    'json' => [
                        "message" => "Rate not found."
                    ],
                ],
            ],
            'Кейс 6 (Неудачный запрос c неизвестным параметром BASE_CURRENCY_CODE)' => [
                [
                    'params' => [
                        'date' => '13.07.2005',
                        'currency_code' => 'EUR',
                        'base_currency_code' => 'USDD',
                    ],
                    'status' => 400,
                    'json' => [
                        "message" => "Rate not found."
                    ],
                ],
            ],
        ];
    }
}
