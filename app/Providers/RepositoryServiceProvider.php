<?php

namespace App\Providers;

use App\Repositories\GigRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\GigRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(GigRepositoryInterface::class, GigRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
