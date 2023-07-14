<?php

namespace App\Jobs;

use Rate;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RateForHalfYearJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected string $uuid;
    protected string $currencyCharCode;
    protected ?string $baseCurrencyCharCode;
    
    /**
     * Create a new job instance.
     */
    public function __construct(string $uuid, string $currencyCharCode, ?string $baseCurrencyCharCode)
    {
        $this->uuid = $uuid;
        $this->currencyCharCode = $currencyCharCode;
        $this->baseCurrencyCharCode = $baseCurrencyCharCode;
    }
    
    /**
     * Execute the job.
     *
     * @throws \JsonException|\RedisException
     */
    public function handle(): void
    {
        $data = Rate::getComparisonWithYesterdayForHalfYear($this->currencyCharCode, $this->baseCurrencyCharCode);
        
        Redis::lpush('rateHalfYear', json_encode([
            'uuid' => $this->uuid,
            'data' => $data,
        ], JSON_THROW_ON_ERROR));
    }
}
