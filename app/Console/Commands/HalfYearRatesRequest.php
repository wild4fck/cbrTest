<?php

namespace App\Console\Commands;

use Faker\Provider\Uuid;
use Illuminate\Console\Command;
use App\Jobs\RateForHalfYearJob;

class HalfYearRatesRequest extends Command
{
    protected $signature = 'rate:request {currencyCode} {baseCurrencyCode?}';
    protected $description = 'Запуск запроса данных за пол года';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info("Запуск запроса");
        $uuid = Uuid::uuid();
        $currencyCode = $this->argument('currencyCode');
        $baseCurrencyCode = $this->argument('baseCurrencyCode');
        
        RateForHalfYearJob::dispatch($uuid, $currencyCode, $baseCurrencyCode);
    
        $this->info("Запрос произведён");
        $this->info("Идентификатор запроса: $uuid");
        $this->info("Для проверки результат запустите команду: php artisan rate:check-result $uuid");
    }
}
