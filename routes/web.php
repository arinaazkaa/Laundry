<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PickupDeliveryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Publik
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Tracking pelanggan via QR Code. Tanpa login, dibatasi laju akses supaya token tidak bisa ditebak massal
Route::get('/track/{token}', [TrackingController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('tracking.show');

/*
|--------------------------------------------------------------------------
| Wajib login
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Halaman awal: pemilik melihat dashboard, role lain diarahkan ke halaman kerjanya (lihat DashboardController)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |----------------------------------------------------------------------
    | Pemilik + Kasir + Pegawai
    |----------------------------------------------------------------------
    */
    Route::middleware('role:pemilik|kasir|pegawai')->group(function () {
        Route::get('/process-queue', [TransactionController::class, 'processQueue'])->name('process.queue');
        Route::patch('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.updateStatus');
    });

    /*
    |----------------------------------------------------------------------
    | Pemilik + Kasir
    |----------------------------------------------------------------------
    */
    Route::middleware('role:pemilik|kasir')->group(function () {

        // Transaksi
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::post('/transactions/preview', [TransactionController::class, 'preview'])->name('transactions.preview');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::get('/transactions/{transaction}/nota', [TransactionController::class, 'printNota'])->name('transactions.nota');
        Route::get('/transactions/{transaction}/nota/download', [TransactionController::class, 'downloadNota'])->name('transactions.nota.download');

        Route::get('/transactions-status/processing', [TransactionController::class, 'processing'])->name('transactions.processing');
        Route::get('/transactions-status/ready', [TransactionController::class, 'ready'])->name('transactions.ready');
        Route::get('/transactions-status/history', [TransactionController::class, 'history'])->name('transactions.history');

        // Pembayaran
        Route::post('/transactions/{transaction}/payments', [PaymentController::class, 'store'])->name('payments.store');

        // Pelanggan (hapus dan jadikan member hanya pemilik, lihat grup bawah)
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');

        // Layanan (kasir hanya melihat)
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');

        // Antar-Jemput
        Route::get('/pickups', [PickupDeliveryController::class, 'index'])->name('pickups.index');
        Route::get('/pickups/pickup', [PickupDeliveryController::class, 'pickupOnly'])->name('pickups.pickup');
        Route::get('/pickups/delivery', [PickupDeliveryController::class, 'deliveryOnly'])->name('pickups.delivery');
        Route::get('/pickups/{pickup}', [PickupDeliveryController::class, 'show'])->name('pickups.show');
        Route::patch('/pickups/{pickup}/status', [PickupDeliveryController::class, 'updateStatus'])->name('pickups.updateStatus');
    });

    /*
    |----------------------------------------------------------------------
    | Khusus Pemilik
    |----------------------------------------------------------------------
    */
    Route::middleware('role:pemilik')->group(function () {

        // Pembatalan transaksi
        // Route::patch('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

        // Pelanggan
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::patch('/customers/{customer}/promote-member', [CustomerController::class, 'promoteMember'])->name('customers.promoteMember');

        // Layanan
        Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
        Route::patch('/services/{service}/toggle', [ServiceController::class, 'toggleActive'])->name('services.toggle');

        // Promo
        Route::get('/promos', [PromoController::class, 'index'])->name('promos.index');
        Route::get('/promos/active', [PromoController::class, 'active'])->name('promos.active');
        Route::get('/promos/history', [PromoController::class, 'history'])->name('promos.history');
        Route::get('/promos/create', [PromoController::class, 'create'])->name('promos.create');
        Route::post('/promos', [PromoController::class, 'store'])->name('promos.store');
        Route::get('/promos/{promo}/edit', [PromoController::class, 'edit'])->name('promos.edit');
        Route::put('/promos/{promo}', [PromoController::class, 'update'])->name('promos.update');
        Route::patch('/promos/{promo}/toggle', [PromoController::class, 'toggleActive'])->name('promos.toggle');
        Route::delete('/promos/{promo}', [PromoController::class, 'destroy'])->name('promos.destroy');

        // Pengeluaran
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        // Inventori
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/logs', [InventoryController::class, 'logs'])->name('inventory.logs');
        Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::get('/inventory/{item}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
        Route::put('/inventory/{item}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::post('/inventory/{item}/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stockIn');
        Route::post('/inventory/{item}/stock-out', [InventoryController::class, 'stockOut'])->name('inventory.stockOut');
        Route::delete('/inventory/{item}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

        // Laporan
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/receivables', [ReportController::class, 'receivables'])->name('reports.receivables');

        // Pengguna
        Route::resource('users', UserController::class)->except(['show']);

        // Pengaturan
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';