<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TelegramUserController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\CheckoutController;

// Public Routes - Landing Page & Payment
Route::get('/', [CheckoutController::class, 'index'])->name('landing');
Route::get('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
Route::post('/checkout/process', [CheckoutController::class, 'processCheckout'])->name('checkout.process');
Route::get('/payment/{reference}', [CheckoutController::class, 'showPayment'])->name('payment.show');
Route::get('/payment/{reference}/status', [CheckoutController::class, 'checkStatus'])->name('payment.status');
Route::post('/payment/health-check', [CheckoutController::class, 'retryHealthCheck'])->name('payment.health-check');

// Static Pages
Route::get('/privacy', function () {
    return view('frontend.privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('frontend.terms');
})->name('terms');

Route::get('/about', function () {
    return view('frontend.about');
})->name('about');

Route::get('/contact', function () {
    return view('frontend.contact');
})->name('contact');

// Tripay Callback - Protected by middleware
Route::post('/payment/callback', [CheckoutController::class, 'callback'])
    ->middleware(\App\Http\Middleware\VerifyTripayCallback::class)
    ->name('payment.callback');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Admin dashboard
    Route::get('/admin', function () {
        return redirect()->route('movies.index');
    })->name('dashboard');

    // Admin Only Routes
    Route::middleware('admin')->group(function () {
        // Movie Management
        Route::get('/movies/data', [MovieController::class, 'data'])->name('movies.data');
        Route::post('/movies/{movie}/update-vip', [MovieController::class, 'updateVip'])->name('movies.updateVip');
        Route::resource('movies', MovieController::class);

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Telegram Users
        Route::get('/telegram-users', [TelegramUserController::class, 'index'])->name('telegram-users.index');
        Route::get('/telegram-users/data', [TelegramUserController::class, 'data'])->name('telegram-users.data');
        Route::post('/telegram-users/{telegramUser}/update-vip', [TelegramUserController::class, 'updateVip'])->name('telegram-users.update-vip');

        // Payments
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/data', [PaymentController::class, 'data'])->name('payments.data');
        Route::post('/payments/{payment}/update-status', [PaymentController::class, 'updateStatus'])->name('payments.update-status');

        // User Management Routes (Web Admin)
        Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
        Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
        Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);
    });
});
