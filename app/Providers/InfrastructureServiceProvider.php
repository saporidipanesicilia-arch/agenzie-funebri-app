<?php

namespace App\Providers;

use App\Domain\Repositories\DeceasedRepositoryInterface;
use App\Domain\Repositories\FuneralRepositoryInterface;
use App\Domain\Services\DocumentStorageServiceInterface;
use App\Domain\Services\OCRServiceInterface;
use App\Infrastructure\Repositories\EloquentDeceasedRepository;
use App\Infrastructure\Repositories\EloquentFuneralRepository;
use App\Infrastructure\Services\LocalDocumentStorageService;
use App\Infrastructure\Services\PlaceholderOCRService;
use Illuminate\Support\ServiceProvider;

class InfrastructureServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repositories
        $this->app->bind(FuneralRepositoryInterface::class, EloquentFuneralRepository::class);
        $this->app->bind(DeceasedRepositoryInterface::class, EloquentDeceasedRepository::class);
        $this->app->bind(\App\Domain\Repositories\ProductRepositoryInterface::class, \App\Infrastructure\Repositories\EloquentProductRepository::class);

        // Services
        $this->app->bind(DocumentStorageServiceInterface::class, LocalDocumentStorageService::class);
        $this->app->bind(OCRServiceInterface::class, PlaceholderOCRService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
