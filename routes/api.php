<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\CheckoutController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Telegram Webhook Routes
Route::prefix('telegram')->group(function () {
    // Main webhook endpoint
    Route::post('/webhook', [TelegramWebhookController::class, 'handle'])
        ->name('telegram.webhook');

    // Health check endpoint
    Route::get('/webhook/health', [TelegramWebhookController::class, 'health'])
        ->name('telegram.webhook.health');
});
