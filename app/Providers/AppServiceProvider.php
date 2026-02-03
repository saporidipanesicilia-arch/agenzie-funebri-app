<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repository bindings for Clean Architecture
        $this->app->bind(
            \App\Domain\Repositories\FuneralRepositoryInterface::class,
            \App\Infrastructure\Repositories\EloquentFuneralRepository::class
        );

        $this->app->bind(
            \App\Domain\Repositories\DeceasedRepositoryInterface::class,
            \App\Infrastructure\Repositories\EloquentDeceasedRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
