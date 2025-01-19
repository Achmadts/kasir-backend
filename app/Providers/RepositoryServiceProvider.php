<?php

namespace App\Providers;

use App\Interfaces\{
    PembelianRepositoryInterface,
    KategoriRepositoryInterface,
    PenjualanRepositoryInterface,
    UserRepositoryInterface,
    ProductRepositoryInterface
};
use App\Repositories\{
    KategoriRepository,
    PembelianRepository,
    PenjualanRepository,
    UserRepository,
    ProductRepository
};
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(KategoriRepositoryInterface::class, KategoriRepository::class);
        $this->app->bind(PenjualanRepositoryInterface::class, PenjualanRepository::class);
        $this->app->bind(PembelianRepositoryInterface::class, PembelianRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
