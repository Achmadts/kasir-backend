<?php

namespace App\Providers;

use App\Repositories\UserRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\Interfaces\ProductRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
