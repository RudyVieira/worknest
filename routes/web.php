<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('spaces.index');
});

// Public routes
Route::get('/spaces', [SpaceController::class, 'index'])->name('spaces.index');
Route::get('/spaces/{space}', [SpaceController::class, 'show'])->name('spaces.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Booking routes
    Route::post('/spaces/{space}/book', [SpaceController::class, 'book'])->name('spaces.book');
    
    // Payment routes
    Route::get('/payment/{reservation}', [PaymentController::class, 'show'])->name('payment.show');
    Route::get('/payment/{reservation}/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/{reservation}/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
    
    // Reservations routes
    Route::get('/my-reservations', [SpaceController::class, 'myReservations'])->name('reservations.index');
    Route::get('/reservations/{reservation}', [SpaceController::class, 'showReservation'])->name('reservations.show');
    Route::post('/reservations/{reservation}/cancel', [SpaceController::class, 'cancelReservation'])->name('reservations.cancel');
});

// Stripe webhook (no auth middleware)
Route::post('/webhook/stripe', [PaymentController::class, 'webhook'])->name('webhook.stripe');

require __DIR__.'/auth.php';
