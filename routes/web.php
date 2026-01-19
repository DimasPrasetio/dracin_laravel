<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TelegramUserController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\BotAdminController;
use App\Http\Controllers\Admin\ViewLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\VipPackageController;
use App\Http\Controllers\CheckoutController;

// Public Routes - Landing Page & Payment
Route::get('/', [CheckoutController::class, 'index'])->name('landing');
Route::get('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
Route::post('/checkout/process', [CheckoutController::class, 'processCheckout'])
    ->middleware('throttle:payment-create')
    ->name('checkout.process');
Route::get('/payment/{reference}', [CheckoutController::class, 'showPayment'])->name('payment.show');
Route::get('/payment/{reference}/status', [CheckoutController::class, 'checkStatus'])
    ->middleware('throttle:payment-status')
    ->name('payment.status');
Route::post('/payment/health-check', [CheckoutController::class, 'retryHealthCheck'])->name('payment.health-check');
Route::post('/check-vip-status', [CheckoutController::class, 'checkVipStatus'])
    ->middleware('throttle:10,1')
    ->name('checkout.check-vip');

// Tripay Payment Callback
Route::post('/payment/callback', [CheckoutController::class, 'callback'])
    ->middleware(\App\Http\Middleware\VerifyTripayCallback::class)
    ->name('payment.callback');

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

    // Staff Routes (Admin + Moderator)
    Route::middleware('admin')->group(function () {
        // Movie Management - Staff can view and add, only admin can edit/delete
        Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
        Route::get('/movies/data', [MovieController::class, 'data'])->name('movies.data');
        Route::get('/movies/create', [MovieController::class, 'create'])->name('movies.create');
        Route::post('/movies', [MovieController::class, 'store'])->name('movies.store');

        // Movie Transactions/Analytics - Must be before {movie} routes
        Route::middleware('admin.only')->group(function () {
            Route::get('/movies/transactions', [MovieController::class, 'transactions'])->name('movies.transactions');
            Route::get('/movies/transactions/data', [MovieController::class, 'transactionsData'])->name('movies.transactions.data');
            Route::get('/movies/{movie}/transactions', [MovieController::class, 'transactionDetails'])->name('movies.transaction-details');
        });

        // Admin Only Movie Routes
        Route::middleware('admin.only')->group(function () {
            Route::get('/movies/{movie}', [MovieController::class, 'show'])->name('movies.show');
            Route::get('/movies/{movie}/edit', [MovieController::class, 'edit'])->name('movies.edit');
            Route::put('/movies/{movie}', [MovieController::class, 'update'])->name('movies.update');
            Route::delete('/movies/{movie}', [MovieController::class, 'destroy'])->name('movies.destroy');
            Route::post('/movies/{movie}/update-vip', [MovieController::class, 'updateVip'])->name('movies.updateVip');
        });
    });

    // Admin Only Routes
    Route::middleware(['admin', 'admin.only'])->group(function () {
        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Bot Users (Telegram Users)
        Route::get('/telegram-users', [TelegramUserController::class, 'index'])->name('telegram-users.index');
        Route::get('/telegram-users/data', [TelegramUserController::class, 'data'])->name('telegram-users.data');
        Route::post('/telegram-users/{user}/update-vip', [TelegramUserController::class, 'updateVip'])->name('telegram-users.update-vip');

        // Payments
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/data', [PaymentController::class, 'data'])->name('payments.data');
        Route::post('/payments/{payment}/update-status', [PaymentController::class, 'updateStatus'])->name('payments.update-status');

        // Bot Admin Management
        Route::get('/bot-admins', [BotAdminController::class, 'index'])->name('bot-admins.index');
        Route::get('/bot-admins/data', [BotAdminController::class, 'data'])->name('bot-admins.data');
        Route::get('/bot-admins/stats', [BotAdminController::class, 'stats'])->name('bot-admins.stats');
        Route::post('/bot-admins/{user}/toggle-admin', [BotAdminController::class, 'toggleAdmin'])->name('bot-admins.toggle-admin');
        Route::post('/bot-admins/{user}/set-role', [BotAdminController::class, 'setRole'])->name('bot-admins.set-role');

        // User Management Routes (Web Admin & Moderator Management)
        Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
        Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
        Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);

        // View Logs Analytics
        Route::get('/view-logs', [ViewLogController::class, 'index'])->name('view-logs.index');
        Route::get('/view-logs/analytics', [ViewLogController::class, 'analytics'])->name('view-logs.analytics');
        Route::get('/view-logs/data', [ViewLogController::class, 'data'])->name('view-logs.data');
        Route::get('/view-logs/user/{telegramUserId}', [ViewLogController::class, 'userHistory'])->name('view-logs.user-history');

        // Category Management
        Route::prefix('categories')->name('admin.categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('/create', [CategoryController::class, 'create'])->name('create');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::get('/{category}', [CategoryController::class, 'show'])->name('show')->middleware('category.access');
            Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit')->middleware('category.admin');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('update')->middleware('category.admin');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');

            // Category webhook management
            Route::post('/{category}/regenerate-webhook', [CategoryController::class, 'regenerateWebhookSecret'])
                ->name('regenerate-webhook')
                ->middleware('category.admin');

            Route::post('/{category}/set-webhook', [CategoryController::class, 'setWebhook'])
                ->name('set-webhook')
                ->middleware('category.admin');

            Route::get('/{category}/webhook-status', [CategoryController::class, 'webhookStatus'])
                ->name('webhook-status')
                ->middleware('category.admin');

            // Category admin management
            Route::post('/{category}/admins', [CategoryController::class, 'addAdmin'])
                ->name('add-admin')
                ->middleware('category.admin');
            Route::put('/{category}/admins/{categoryAdmin}', [CategoryController::class, 'updateAdminRole'])
                ->name('update-admin-role')
                ->middleware('category.admin');
            Route::delete('/{category}/admins/{categoryAdmin}', [CategoryController::class, 'removeAdmin'])
                ->name('remove-admin')
                ->middleware('category.admin');

            // AJAX endpoints for admin selection
            Route::get('/{category}/available-users', [CategoryController::class, 'getAvailableUsers'])
                ->name('available-users')
                ->middleware('category.admin');
        });

        // VIP Packages Management (Super Admin)
        Route::get('/vip-packages', [VipPackageController::class, 'index'])->name('vip-packages.index');
        Route::get('/vip-packages/create', [VipPackageController::class, 'create'])->name('vip-packages.create');
        Route::post('/vip-packages', [VipPackageController::class, 'store'])->name('vip-packages.store');
        Route::get('/vip-packages/{vipPackage}/edit', [VipPackageController::class, 'edit'])->name('vip-packages.edit');
        Route::put('/vip-packages/{vipPackage}', [VipPackageController::class, 'update'])->name('vip-packages.update');
        Route::delete('/vip-packages/{vipPackage}', [VipPackageController::class, 'destroy'])->name('vip-packages.destroy');
    });
});
