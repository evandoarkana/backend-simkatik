<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\PembelianController;
use App\Http\Controllers\Api\PenjualanController;
use App\Http\Controllers\Api\ProdukController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::prefix('users')->group(function () {
        // Public routes
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        // Password reset 
        Route::middleware(['throttle:6,1'])->group(function () {
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
            Route::post('/verify-verification-code', [AuthController::class, 'verifyVerificationCode']);
            Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode']);
            Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        });

        Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
            ->name('verification.verify');

        // PDF routes
        Route::get('produk/print-pdf', [ProdukController::class, 'printPdf']);
        Route::get('penjualan/print-pdf', [PenjualanController::class, 'printPdf']);
        Route::get('pembelian/print-pdf', [PembelianController::class, 'printPdf']);

        // Protected routes
        Route::middleware('auth:sanctum')->group(function () {
            // Auth routes
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/update-profile-picture', [AuthController::class, 'updateProfilePicture']);
            Route::get('/profile', [AuthController::class, 'getProfile']);

            // Password change routes
            Route::post('/verify-old-password-v2', [AuthController::class, 'verifyOldPasswordV2']);
            Route::post('/reset-password-v2', [AuthController::class, 'resetPasswordV2'])
                ->middleware(['password.verified']);

            // Dashboard routes
            Route::prefix('dashboard')->group(function () {
                Route::get('/laba-bulanan', [DashboardController::class, 'getLabaBersihBulanan']);
                Route::get('/laba-tahunan', [DashboardController::class, 'getLabaBersihTahunan']);
                Route::get('/total-produk', [DashboardController::class, 'getTotalProduk']);
                Route::get('/total-terjual', [DashboardController::class, 'getTotalProdukTerjual']);
                Route::get('/all', [DashboardController::class, 'getSemuaStatistik']);
            });

            // Kategori routes
            Route::get('kategori', [KategoriController::class, 'index']);
            Route::post('kategori', [KategoriController::class, 'store']);
            Route::put('kategori/{id}', [KategoriController::class, 'update']);
            Route::delete('kategori/{id}', [KategoriController::class, 'destroy']);

            // Produk routes
            Route::apiResource('produk', ProdukController::class);
            Route::put('produk/{id}/tambah-stok', [ProdukController::class, 'tambahStok']);

            // Penjualan & Pembelian routes
            Route::get('penjualan', [PenjualanController::class, 'index']);
            Route::post('penjualan', [PenjualanController::class, 'store']);
            Route::get('pembelian', [PembelianController::class, 'index']);
        });
    });
});