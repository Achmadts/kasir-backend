<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckJwtToken;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\RedirectIfAuthenticatedApi;

Route::post('/authenticate', [LoginController::class, 'index'])->name('authenticate')->middleware(RedirectIfAuthenticatedApi::class);
Route::get('/login', [LoginController::class, 'getUser'])->name('login')->middleware(RedirectIfAuthenticatedApi::class);
Route::post('/refresh-token', [LoginController::class, 'refreshAccessToken'])->name("refreshToken");

Route::group(['middleware' => [CheckJwtToken::class, 'auth:api']], function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name("logout");
    Route::get('/user', [LoginController::class, 'getUser'])->name("user");
    Route::get('/dashboard', [DashboardController::class, 'index'])->name("dashboard");
});