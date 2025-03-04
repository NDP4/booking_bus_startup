<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/payment/checkout/{booking}', [PaymentController::class, 'checkout'])
        ->name('payment.checkout');

    // Update success route to accept parameters
    Route::get('/payment/success', [PaymentController::class, 'success'])
        ->name('payment.success');
    Route::get('/payment/pending', [PaymentController::class, 'pending'])
        ->name('payment.pending');
    Route::get('/payment/error', [PaymentController::class, 'error'])
        ->name('payment.error');
    Route::get('/payment/cancelled', [PaymentController::class, 'cancelled'])
        ->name('payment.cancelled');
});

Route::post('payment/notification', [PaymentNotificationController::class, 'handle'])
    ->name('payment.notification');
