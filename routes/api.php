<?php

use App\Http\Controllers\{
    RegisterController,
    SocialLoginController,
    LoginController,
    UserController,
    DashboardController,
    KategoriController,
    PembelianController,
    PenjualanController,
    ProdukController
};
use App\Http\Middleware\{CheckJwtToken, RedirectIfAuthenticatedApi};
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'index'])->name('register')->middleware(RedirectIfAuthenticatedApi::class);
Route::post('/authenticate', [LoginController::class, 'index'])->name('authenticate')->middleware(RedirectIfAuthenticatedApi::class);
Route::post('auth/google', [SocialLoginController::class, 'login']);
Route::get('auth/google/callback', [SocialLoginController::class, 'handleGoogleCallback']);
Route::get('/login', [LoginController::class, 'getUser'])->name('login')->middleware(RedirectIfAuthenticatedApi::class);
Route::post('/refresh-token', [LoginController::class, 'refreshAccessToken'])->name("refreshToken");

Route::group(['middleware' => [CheckJwtToken::class, 'auth:api']], function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name("logout");
    Route::get('/user', [LoginController::class, 'getUser'])->name("user");
    Route::get('/dashboard', [DashboardController::class, 'index'])->name("dashboard");

    Route::resource('users', UserController::class);
    Route::resource('kategori', KategoriController::class);
    Route::resource('produk', ProdukController::class);
    Route::resource('penjualan', PenjualanController::class);
    Route::resource('pembelian', PembelianController::class);

    Route::get('/sales-purchases', [PenjualanController::class, 'getSalesPurchases']);
    Route::get('kategori-export', [KategoriController::class, 'export'])->name('kategori.export');
    Route::get('produk-export', [ProdukController::class, 'export'])->name('produk.export');
    Route::get('penjualan-export', [PenjualanController::class, 'export'])->name('penjualan.export');
    Route::get('pembelian-export', [PembelianController::class, 'export'])->name('pembelian.export');
    Route::get('cashier-export', [UserController::class, 'export'])->name('user.export');
});