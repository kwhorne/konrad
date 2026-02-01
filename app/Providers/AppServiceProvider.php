<?php

namespace App\Providers;

use App\Listeners\StripeEventSubscriber;
use App\Models\Company;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cashier::useCustomerModel(Company::class);

        Event::subscribe(StripeEventSubscriber::class);

        Order::observe(OrderObserver::class);
    }
}
