<?php

namespace App\Providers;

use App\Services\ApplicationService;
use App\Services\ReceiptService;
use App\Services\SubscriptionsService;
use App\Services\VerifyService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        $this->app->bind(SubscriptionsService::class, function() {
           return new SubscriptionsService(
                new ReceiptService(),
                new VerifyService(),
                new ApplicationService()
           );
        });

        $this->app->bind(ApplicationService::class, function() {
           return new ApplicationService();
        });
    }
}
