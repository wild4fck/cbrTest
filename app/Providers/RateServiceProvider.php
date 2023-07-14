<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\RateService\RateService;

class RateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->bind('rate', function () {
            $clientClass = config('services.rate.client');
            return new RateService(new $clientClass);
        });
    }
}
