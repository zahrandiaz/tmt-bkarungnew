<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rute yang bisa diakses SEMUA peran (setelah login)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

// Rute HANYA untuk ADMIN
Route::middleware(['auth', 'role:Admin'])->group(function () {
    // Manajemen Peran & Pengguna
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);

    // Hapus Permanen Transaksi (Hard Delete)
    Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');

    // [BARU] Pulihkan Transaksi (Restore)
    Route::post('purchases/{id}/restore', [PurchaseController::class, 'restore'])->name('purchases.restore');
    Route::post('sales/{id}/restore', [SaleController::class, 'restore'])->name('sales.restore');
});

// Rute untuk ADMIN dan MANAGER
Route::middleware(['auth', 'role:Admin|Manager'])->group(function () {
    // Master Data
    Route::resource('product-categories', ProductCategoryController::class);
    Route::resource('product-types', ProductTypeController::class);
    Route::resource('products', ProductController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);
    
    // Laporan
    Route::get('reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales');
    Route::get('reports/purchases', [ReportController::class, 'purchasesReport'])->name('reports.purchases');
    Route::get('reports/stock', [ReportController::class, 'stockReport'])->name('reports.stock');
    Route::get('reports/profit-loss', [ReportController::class, 'profitAndLossReport'])->name('reports.profit-loss');

    // Ekspor
    Route::get('reports/sales/export', [ReportController::class, 'exportSales'])->name('reports.sales.export');
    Route::get('reports/purchases/export', [ReportController::class, 'exportPurchases'])->name('reports.purchases.export');
    Route::get('reports/stock/export', [ReportController::class, 'exportStock'])->name('reports.stock.export');
});

// Rute untuk ADMIN, MANAGER, dan STAF
Route::middleware(['auth', 'role:Admin|Manager|Staf'])->group(function () {
    // Transaksi Pembelian (tanpa hard delete)
    Route::delete('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
    Route::resource('purchases', PurchaseController::class)->except(['destroy']);

    // Transaksi Penjualan (tanpa hard delete)
    Route::delete('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    Route::resource('sales', SaleController::class)->except(['destroy']);
});


// Rute Profil Pengguna (bisa diakses semua setelah login)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';