<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class HalfYearRatesCheckResult extends Command
{
    protected $signature = 'rate:check-result {uuid}';
    protected $description = 'Проверка результата запроса данных за пол года';
    
    /**
     * Execute the console command.
     *
     * @throws \JsonException
     */
    public function handle(): void
    {
        $uuid = $this->argument('uuid');
        $data = Redis::lrange('rateHalfYear', 0, -1);
    
        $result = array_filter($data, static function ($json) use ($uuid) {
            $item = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return ($item && $item['uuid'] === $uuid);
        });
    
        if (empty($result)) {
            $this->info('По данному запросу нет ответа');
            return;
        }
     
        dd(json_decode($result[0], false, 512, JSON_THROW_ON_ERROR));
    }
}
