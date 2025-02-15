<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\PembelianController;
use App\Http\Controllers\Api\PenjualanController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\KaryawanController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::prefix('users')->group(function () {
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


            Route::prefix('admin')->group(function () {
                Route::middleware(['auth:sanctum', 'role:Admin'])->group(function () {
                    Route::prefix('karyawan')->group(function () {
                        Route::post('/', [AuthController::class, 'register']);
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
                        Route::get('/', [KategoriController::class, 'index']);
                        Route::get('/{id}', [KategoriController::class, 'show']);
                        Route::post('/', [KategoriController::class, 'store']);
                        Route::put('/{id}', [KategoriController::class, 'update']);
                        Route::delete('/{id}', [KategoriController::class, 'destroy']);
                    });

                    Route::prefix('produk')->group(function () {
                        Route::get('/', [ProdukController::class, 'index']);
                        Route::post('/', [ProdukController::class, 'store']);
                        Route::put('/{id}', [ProdukController::class, 'update']);
                        Route::delete('/{id}', [ProdukController::class, 'destroy']);
                        Route::put('/{id}/tambah-stok', [ProdukController::class, 'tambahStok']);
                        Route::get('/print-pdf', [ProdukController::class, 'printPdf']);
                    });

                    Route::prefix('penjualan')->group(function () {
                        Route::get('/', [PenjualanController::class, 'index']);
                        Route::post('/', [PenjualanController::class, 'store']);
                        Route::get('/print-pdf', [PenjualanController::class, 'printPdf']);
                    });

                    Route::prefix('pembelian')->group(function () {
                        Route::get('/', [PembelianController::class, 'index']);
                        Route::post('/', [PembelianController::class, 'store']);
                        Route::get('pembelian/print-pdf', [PembelianController::class, 'printPdf']);
                    });
                });
            });
        });
    });
});