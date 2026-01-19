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
    // Main webhook endpoint (default bot - backward compatible)
    Route::post('/webhook', [TelegramWebhookController::class, 'handle'])
        ->middleware('throttle:bot-webhook')
        ->name('telegram.webhook');

    // Health check endpoint (default bot)
    Route::get('/webhook/health', [TelegramWebhookController::class, 'health'])
        ->name('telegram.webhook.health');

    // Category-specific webhook endpoints
    Route::post('/webhook/{categorySlug}', [TelegramWebhookController::class, 'handleCategory'])
        ->middleware('throttle:bot-webhook')
        ->name('telegram.webhook.category');

    // Category-specific health check endpoint
    Route::get('/webhook/{categorySlug}/health', [TelegramWebhookController::class, 'healthCategory'])
        ->name('telegram.webhook.category.health');

    // Webhook management endpoints (protected - for admin use)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/webhook/{categorySlug}/set', [TelegramWebhookController::class, 'setWebhook'])
            ->name('telegram.webhook.category.set');

        Route::delete('/webhook/{categorySlug}/delete', [TelegramWebhookController::class, 'deleteWebhook'])
            ->name('telegram.webhook.category.delete');
    });
});
