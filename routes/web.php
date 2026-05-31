<?php

use App\Http\Controllers\Web\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Web\Admin\ImportLogController;
use App\Http\Controllers\Web\Admin\JenisBarangController;
use App\Http\Controllers\Web\Admin\LandingPageController;
use App\Http\Controllers\Web\Admin\LogController;
use App\Http\Controllers\Web\Admin\PelakuUsahaAccountController;
use App\Http\Controllers\Web\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Web\Admin\ProductImageManagementController;
use App\Http\Controllers\Web\Admin\ProductImageController;
use App\Http\Controllers\Web\Admin\ProductImportController;
use App\Http\Controllers\Web\Admin\ProductVerificationController;
use App\Http\Controllers\Web\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Web\Public\HomeController;
use App\Http\Controllers\Web\Public\ProductController as PublicProductController;
use App\Http\Controllers\Web\Public\UmkmController;
use App\Http\Controllers\Web\SuperAdmin\AuditTrailController;
use App\Http\Controllers\Web\SuperAdmin\SystemSettingController;
use App\Http\Controllers\Web\SuperAdmin\UserManagementController;
use App\Http\Controllers\Web\User\AccountController;
use App\Http\Controllers\Web\User\DashboardController as UserDashboardController;
use App\Http\Controllers\Web\User\ProductSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [PublicProductController::class, 'index'])->name('index');
    Route::get('/{produk}', [PublicProductController::class, 'show'])->name('show');
});

Route::prefix('umkm')->name('umkm.')->group(function () {
    Route::get('/', [UmkmController::class, 'index'])->name('index');
    Route::get('/{namaPelakuUsaha}', [UmkmController::class, 'show'])->name('show');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::redirect('/register', '/login')->name('register');

Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // Pengaturan akun: identitas NIB tampil read-only, user hanya dapat mengubah password
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::patch('/account/password', [AccountController::class, 'updatePassword'])->name('account.update-password');

    // Konfigurasi produk: harga, deskripsi tampilan, dan ganti gambar
    Route::prefix('products/setting')->name('products.setting.')->group(function () {
        Route::get('/', [ProductSettingController::class, 'index'])->name('index');
        Route::get('/{id}/edit', [ProductSettingController::class, 'edit'])->name('edit');
        Route::patch('/{id}', [ProductSettingController::class, 'update'])->name('update');
        Route::post('/{id}/gambar', [ProductSettingController::class, 'uploadGambar'])->name('upload-gambar');
    });
});

Route::middleware(['auth', 'role:admin,super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.alias');

        Route::resource('products', AdminProductController::class)
            ->parameters(['products' => 'produk']);
        Route::post('/products/import/rekap-pirt', [ProductImportController::class, 'rekapPirt'])->name('products.import.rekap-pirt');
        Route::post('/products/{produk}/images', [ProductImageController::class, 'store'])->name('products.images.store');
        Route::delete('/products/images/{gambarProduk}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
        Route::get('/product-images', [ProductImageManagementController::class, 'index'])->name('product-images.index');
        Route::post('/product-images/{produk}', [ProductImageManagementController::class, 'update'])->name('product-images.update');

        Route::get('/verifications', [ProductVerificationController::class, 'index'])->name('verifications.index');
        Route::post('/verifications/import', [ProductVerificationController::class, 'import'])->name('verifications.import');
        Route::get('/verifications/{produk}', [ProductVerificationController::class, 'show'])->name('verifications.show');

        Route::get('/jenis-barang/perlu-review', [JenisBarangController::class, 'review'])->name('jenis-barang.review');
        Route::post('/jenis-barang/sinkronkan', [JenisBarangController::class, 'sync'])->name('jenis-barang.sync');
        Route::resource('jenis-barang', JenisBarangController::class)
            ->parameters(['jenis-barang' => 'jenisBarang'])
            ->except(['show']);

        Route::resource('landing-page', LandingPageController::class)
            ->parameters(['landing-page' => 'landingPage'])
            ->only(['index', 'update']);

        Route::get('/pelaku-usaha', [PelakuUsahaAccountController::class, 'index'])->name('pelaku-usaha.index');
        Route::get('/pelaku-usaha/{user}/edit', [PelakuUsahaAccountController::class, 'edit'])->name('pelaku-usaha.edit');
        Route::patch('/pelaku-usaha/{user}', [PelakuUsahaAccountController::class, 'update'])->name('pelaku-usaha.update');

        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
        Route::get('/import-logs', [ImportLogController::class, 'index'])->name('import-logs.index');
    });

Route::middleware(['auth', 'role:super_admin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {
        Route::resource('users', UserManagementController::class)->except(['show']);
        Route::resource('settings', SystemSettingController::class)->only(['index', 'update']);
        Route::get('/audit-trails', [AuditTrailController::class, 'index'])->name('audit-trails.index');
    });
