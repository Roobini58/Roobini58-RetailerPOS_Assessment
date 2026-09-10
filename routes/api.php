<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {

    // Product endpoints (/api/product, /api/product/low-stock)
    Route::prefix('product')->name('product.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/low-stock', [ProductController::class, 'lowStock'])->name('low-stock');
    });

    // Order endpoints (/api/order, /api/order/{id})
    Route::prefix('order')->name('order.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{id}', [OrderController::class, 'show'])->name('show');
    });

    // Customer endpoints (/api/customer/{email}, /api/customer/{email}/orders)
    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('/{email}', [CustomerController::class, 'show'])->name('show');
        Route::get('/{email}/orders', [CustomerController::class, 'orders'])->name('orders');
    });

});
