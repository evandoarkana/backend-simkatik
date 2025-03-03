<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\PembelianController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\KaryawanController;
use App\Http\Controllers\Api\MetodePembayaranController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\TransaksiItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->name('verification.verify');

    Route::middleware(['throttle:6,1'])->group(function () {
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/verify-verification-code', [AuthController::class, 'verifyVerificationCode']);
        Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/update-profile-picture', [AuthController::class, 'updateProfilePicture']);
        Route::get('/profile', [AuthController::class, 'getProfile']);

        Route::post('/change-password', [AuthController::class, 'resetPasswordV2']);
    });
});

Route::prefix('users')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('admin')->group(function () {
            Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
                Route::prefix('karyawan')->group(function () {
                    Route::get('/', [KaryawanController::class, 'index']);
                    Route::post('/', [KaryawanController::class, 'store']);
                    Route::put('/{id}', [KaryawanController::class, 'update']);
                    Route::get('/{id}', [KaryawanController::class, 'show']);
                    Route::delete('/{id}', [KaryawanController::class, 'destroy']);
                });

                Route::prefix('dashboard')->group(function () {
                    Route::get('/laba-bulanan', [DashboardController::class, 'getLabaBersihBulanan']);
                    Route::get('/laba-tahunan', [DashboardController::class, 'getLabaBersihTahunan']);
                    Route::get('/total-produk', [DashboardController::class, 'getTotalProduk']);
                    Route::get('/total-terjual', [DashboardController::class, 'getTotalProdukTerjual']);
                    Route::get('/all', [DashboardController::class, 'getSemuaStatistik']);
                });

                Route::prefix('kategori')->group(function () {
                    Route::post('/', [KategoriController::class, 'index']);
                    Route::get('/{id}', [KategoriController::class, 'show']);
                    Route::post('/', [KategoriController::class, 'store']);
                    Route::put('/{id}', [KategoriController::class, 'update']);
                    Route::delete('/{id}', [KategoriController::class, 'destroy']);
                });

                Route::prefix('produk')->group(function () {
                    Route::get('/', [ProdukController::class, 'index']);
                    Route::put('/{id}', [ProdukController::class, 'update']);
                    Route::delete('/{id}', [ProdukController::class, 'destroy']);
                    Route::post('/{id}/restore', [ProdukController::class, 'restore']);
                    Route::get('/print-pdf', [ProdukController::class, 'printPdf']);
                });

                Route::prefix('pembelian')->group(function () {
                    Route::get('/', [PembelianController::class, 'index']);
                    Route::post('/', [PembelianController::class, 'store']);
                    Route::get('pembelian/print-pdf', [PembelianController::class, 'printPdf']);
                    Route::put('/{id}/tambah-stok', [PembelianController::class, 'tambahStok']);
                });

                Route::prefix('metode-pembayaran')->group(function () {
                    Route::get('/', [MetodePembayaranController::class, 'index']);
                    Route::post('/', [MetodePembayaranController::class, 'store']);
                });

                Route::prefix('transaksi')->group(function () {
                    Route::get('/', [TransaksiController::class, 'index']);
                    Route::post('/', [TransaksiController::class, 'store']);
                });

                Route::prefix('transaksi-item')->group(function () {
                    Route::get('/', [TransaksiItemController::class, 'index']);
                    Route::delete('/{id}', [TransaksiItemController::class, 'destroy']);
                });
            });
        });
    });
});