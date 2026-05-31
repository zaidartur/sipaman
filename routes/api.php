<?php

use App\Http\Controllers\Api\Admin\LandingPageController as AdminLandingPageController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\ProductImageController as AdminProductImageController;
use App\Http\Controllers\Api\Admin\ProductImportController as AdminProductImportController;
use App\Http\Controllers\Api\Admin\ProductVerificationController as AdminProductVerificationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProdukController as PublicProductApiController;
use App\Http\Controllers\Api\SuperAdmin\AuditTrailController as SuperAdminAuditTrailController;
use App\Http\Controllers\Api\SuperAdmin\SystemSettingController as SuperAdminSystemSettingController;
use App\Http\Controllers\Api\SuperAdmin\UserManagementController as SuperAdminUserManagementController;
use App\Http\Controllers\Api\User\DashboardController as UserDashboardApiController;
use App\Http\Controllers\Api\User\ProductController as UserProductController;
use App\Http\Controllers\Api\User\ProductImageController as UserProductImageController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login');
});

Route::middleware('auth:sanctum')->prefix('auth')->name('api.auth.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('me', [AuthController::class, 'me'])->name('me');
    Route::post('update-profile', [AuthController::class, 'updateProfile'])->name('update-profile');
});

Route::middleware('throttle:60,1')->prefix('produk')->name('api.produk.')->group(function () {
    Route::get('/', [PublicProductApiController::class, 'index'])->name('index');
    Route::get('/filter', [PublicProductApiController::class, 'filter'])->name('filter');
    Route::get('/{produk}', [PublicProductApiController::class, 'show'])->name('show');
});

Route::get('landing-page', [AdminLandingPageController::class, 'publicIndex'])->name('api.landing-page.public');

Route::middleware(['auth:sanctum', 'role:user'])->prefix('user')->name('api.user.')->group(function () {
    Route::get('/dashboard', [UserDashboardApiController::class, 'show'])->name('dashboard');

    Route::prefix('produk')->name('produk.')->group(function () {
        Route::get('/', [UserProductController::class, 'index'])->name('index');
        Route::get('/{produk}', [UserProductController::class, 'show'])->name('show');
        Route::patch('/{produk}', [UserProductController::class, 'update'])->name('update');
        Route::post('/{produk}/image', [UserProductImageController::class, 'store'])->name('replace-image');
    });
});

Route::middleware(['auth:sanctum', 'role:admin,super_admin'])->prefix('admin')->name('api.admin.')->group(function () {
    Route::apiResource('produk', AdminProductController::class)->parameters(['produk' => 'produk']);

    Route::post('/produk/import/rekap-pirt', [AdminProductImportController::class, 'rekapPirt'])->name('produk.import-rekap-pirt');
    Route::post('/produk/import/status-komitmen', [AdminProductVerificationController::class, 'import'])->name('produk.import-status-komitmen');

    Route::post('/produk/{produk}/images', [AdminProductImageController::class, 'store'])->name('produk.images.store');
    Route::delete('/produk/images/{gambarProduk}', [AdminProductImageController::class, 'destroy'])->name('produk.images.destroy');

    Route::prefix('landing')->name('landing.')->group(function () {
        Route::get('/', [AdminLandingPageController::class, 'index'])->name('index');
        Route::put('/{section}', [AdminLandingPageController::class, 'update'])->name('update');
    });
});

Route::middleware(['auth:sanctum', 'role:super_admin'])->prefix('super-admin')->name('api.super-admin.')->group(function () {
    Route::apiResource('users', SuperAdminUserManagementController::class);
    Route::apiResource('settings', SuperAdminSystemSettingController::class)->only(['index', 'update']);
    Route::get('/audit-trails', [SuperAdminAuditTrailController::class, 'auditTrails'])->name('audit-trails');
    Route::get('/activity-logs', [SuperAdminAuditTrailController::class, 'activityLogs'])->name('activity-logs');
});
