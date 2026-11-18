<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('storefront');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout');

Route::middleware('signed')->group(function (): void {
    Route::get('/orders/{order}', [CheckoutController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/status', [CheckoutController::class, 'status'])->name('orders.status');
    Route::get('/orders/{order}/receipt', [CheckoutController::class, 'receipt'])->name('orders.receipt');
    Route::get('/print-receipt/{order}', [CheckoutController::class, 'printReceipt'])
        ->name('orders.print-receipt');
});
