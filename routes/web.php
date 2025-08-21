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
use App\Http\Controllers\ReceivableController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PriceAdjustmentController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\PermissionController; // [BARU] Import PermissionController
use App\Http\Controllers\SettingsController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rute yang bisa diakses SEMUA peran (setelah login)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

// [MODIFIKASI V2.0.0] Ganti semua middleware 'role' menjadi 'can'
Route::middleware(['auth'])->group(function () {
    // Manajemen Pengguna & Peran (Hanya Admin)
    Route::resource('roles', RoleController::class)->middleware('can:role-view');
    Route::resource('users', UserController::class)->middleware('can:user-view');
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index')->middleware('can:role-edit');
    Route::post('permissions', [PermissionController::class, 'update'])->name('permissions.update')->middleware('can:role-edit');

    // Hapus & Pulihkan Transaksi
    Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy')->middleware('can:transaction-delete-permanent');
    Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy')->middleware('can:transaction-delete-permanent');
    Route::post('purchases/{id}/restore', [PurchaseController::class, 'restore'])->name('purchases.restore')->middleware('can:transaction-restore');
    Route::post('sales/{id}/restore', [SaleController::class, 'restore'])->name('sales.restore')->middleware('can:transaction-restore');

    // Master Data & Produk
    Route::resource('product-categories', ProductCategoryController::class)->middleware('can:product-view');
    Route::resource('product-types', ProductTypeController::class)->middleware('can:product-view');
    Route::resource('products', ProductController::class)->middleware('can:product-view');
    Route::resource('suppliers', SupplierController::class)->middleware('can:product-view');
    Route::resource('customers', CustomerController::class)->middleware('can:product-view');
    Route::resource('expense-categories', ExpenseCategoryController::class)->middleware('can:finance-crud-expense');

    // Laporan
    Route::get('reports/sales', [ReportController::class, 'salesReport'])->name('reports.sales')->middleware('can:report-view-all');
    Route::get('reports/purchases', [ReportController::class, 'purchasesReport'])->name('reports.purchases')->middleware('can:report-view-all');
    Route::get('reports/stock', [ReportController::class, 'stockReport'])->name('reports.stock')->middleware('can:report-view-all');
    Route::get('reports/profit-loss', [ReportController::class, 'profitAndLossReport'])->name('reports.profit-loss')->middleware('can:report-view-all');
    Route::get('reports/deposits', [ReportController::class, 'depositReport'])->name('reports.deposits')->middleware('can:report-view-all');

    // Ekspor
    Route::get('reports/sales/export-csv', [ReportController::class, 'exportSalesCsv'])->name('reports.sales.export.csv')->middleware('can:report-view-all');
    Route::get('reports/sales/export-pdf', [ReportController::class, 'exportSalesPdf'])->name('reports.sales.export.pdf')->middleware('can:report-view-all');
    Route::get('reports/purchases/export-csv', [ReportController::class, 'exportPurchasesCsv'])->name('reports.purchases.export.csv')->middleware('can:report-view-all');
    Route::get('reports/purchases/export-pdf', [ReportController::class, 'exportPurchasesPdf'])->name('reports.purchases.export.pdf')->middleware('can:report-view-all');
    Route::get('reports/stock/export', [ReportController::class, 'exportStock'])->name('reports.stock.export')->middleware('can:report-view-all');
    Route::get('reports/deposits/export-csv', [ReportController::class, 'exportDepositsCsv'])->name('reports.deposits.export.csv')->middleware('can:report-view-all');
    Route::get('reports/deposits/export-pdf', [ReportController::class, 'exportDepositsPdf'])->name('reports.deposits.export.pdf')->middleware('can:report-view-all');
    Route::get('reports/profit-loss/export-csv', [ReportController::class, 'exportProfitAndLossCsv'])->name('reports.profit-loss.export.csv')->middleware('can:report-view-all');
    Route::get('reports/profit-loss/export-pdf', [ReportController::class, 'exportProfitAndLossPdf'])->name('reports.profit-loss.export.pdf')->middleware('can:report-view-all');
    
    // Manajemen Keuangan
    Route::get('receivables', [ReceivableController::class, 'index'])->name('receivables.index')->middleware('can:finance-view');
    Route::get('receivables/{sale}/manage', [ReceivableController::class, 'manage'])->name('receivables.manage')->middleware('can:finance-manage-payment');
    Route::post('receivables/{sale}/payments', [ReceivableController::class, 'storePayment'])->name('receivables.payments.store')->middleware('can:finance-manage-payment');

    Route::get('debts', [DebtController::class, 'index'])->name('debts.index')->middleware('can:finance-view');
    Route::get('debts/{purchase}/manage', [DebtController::class, 'manage'])->name('debts.manage')->middleware('can:finance-manage-payment');
    Route::post('debts/{purchase}/payments', [DebtController::class, 'storePayment'])->name('debts.payments.store')->middleware('can:finance-manage-payment');

    Route::resource('expenses', ExpenseController::class)->middleware('can:finance-crud-expense');

    // Penyesuaian
    Route::get('price-adjustments', [PriceAdjustmentController::class, 'index'])->name('price-adjustments.index')->middleware('can:adjustment-price');
    Route::post('price-adjustments', [PriceAdjustmentController::class, 'store'])->name('price-adjustments.store')->middleware('can:adjustment-price');
    Route::get('stock-adjustments', [StockAdjustmentController::class, 'index'])->name('stock-adjustments.index')->middleware('can:adjustment-stock');
    Route::post('stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store')->middleware('can:adjustment-stock');

    // Pengaturan Sistem
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('can:role-edit'); // Menggunakan permission yang sama dengan Hak Akses
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update')->middleware('can:role-edit');

    // Transaksi
    Route::delete('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel')->middleware('can:transaction-cancel');
    Route::resource('purchases', PurchaseController::class)->except(['destroy'])->middleware('can:transaction-view');
    Route::delete('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel')->middleware('can:transaction-cancel');
    Route::resource('sales', SaleController::class)->except(['destroy'])->middleware('can:transaction-view');

    // API
    Route::get('/api/products/search', [ProductController::class, 'search'])->name('api.products.search')->middleware('can:transaction-create');
    Route::get('/api/products/gallery', [ProductController::class, 'gallery'])->name('api.products.gallery')->middleware('can:transaction-create');
    Route::get('/api/reports/sale-details/{id}', [ReportController::class, 'getSaleDetails'])->name('api.reports.sale-details')->middleware('can:report-view-all');
    Route::get('/api/reports/purchase-details/{id}', [ReportController::class, 'getPurchaseDetails'])->name('api.reports.purchase-details')->middleware('can:report-view-all');
    
    // Rute Cetak
    Route::get('sales/{id}/print-thermal', [SaleController::class, 'printThermal'])->name('sales.printThermal')->middleware('can:transaction-view');
    Route::get('sales/{id}/download-pdf', [SaleController::class, 'downloadPDF'])->name('sales.downloadPDF')->middleware('can:transaction-view');
});

// Rute Profil Pengguna
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';