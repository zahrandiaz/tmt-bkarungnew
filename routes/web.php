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
use App\Http\Controllers\SaleController; // <-- [BARU] Tambahkan ini

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:Admin'])->group(function () {
    // Master Data
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('product-categories', ProductCategoryController::class);
    Route::resource('product-types', ProductTypeController::class);
    Route::resource('products', ProductController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('customers', CustomerController::class);
    
    // Transaksi Pembelian
    Route::delete('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
    Route::resource('purchases', PurchaseController::class);

    // [BARU] Transaksi Penjualan
    // Rute 'cancel' untuk soft delete akan kita gunakan nanti
    Route::delete('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    Route::resource('sales', SaleController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';