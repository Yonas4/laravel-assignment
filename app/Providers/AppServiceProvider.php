<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Repositories\Eloquent\UserRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\PaymentTransactionRepositoryInterface::class,
            \App\Repositories\Eloquent\PaymentTransactionRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\SubscriptionRepositoryInterface::class,
            \App\Repositories\Eloquent\SubscriptionRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\ServiceRepositoryInterface::class,
            \App\Repositories\Eloquent\ServiceRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\BookingRepositoryInterface::class,
            \App\Repositories\Eloquent\BookingRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\CartRepositoryInterface::class,
            \App\Repositories\Eloquent\CartRepository::class
        );
        $this->app->bind(
            \App\Repositories\Contracts\PackageRepositoryInterface::class,
            \App\Repositories\Eloquent\PackageRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
