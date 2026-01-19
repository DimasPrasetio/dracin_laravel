<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Update;
use App\Services\TelegramUpdateProcessor;
use App\Services\TelegramService;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected $updateProcessor;
    protected $telegramService;

    public function __construct(TelegramUpdateProcessor $updateProcessor, TelegramService $telegramService)
    {
        $this->updateProcessor = $updateProcessor;
        $this->telegramService = $telegramService;
    }

    /**
     * Handle incoming webhook from Telegram (default bot - backward compatible)
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            if (config('telegram.mode') !== 'webhook') {
                Log::warning('Webhook received while mode is not webhook', [
                    'mode' => config('telegram.mode'),
                    'ip' => $request->ip(),
                ]);
                return response()->json(['status' => 'ignored', 'message' => 'Webhook disabled'], 200);
            }

            $secretToken = config('telegram.webhook_secret_token');
            if (!empty($secretToken)) {
                $incomingToken = $request->header('X-Telegram-Bot-Api-Secret-Token');
                if (!$incomingToken || !hash_equals($secretToken, $incomingToken)) {
                    Log::warning('Invalid webhook secret token', [
                        'ip' => $request->ip(),
                    ]);
                    return response()->json(['status' => 'unauthorized'], 401);
                }
            }

            Log::info('Webhook request received (default bot)', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Get update from request
            $update = Telegram::getWebhookUpdate();

            if (!$update) {
                Log::warning('Webhook received but no update found');
                return response()->json(['status' => 'ok', 'message' => 'No update']);
            }

            // Get default category for backward compatibility
            $defaultCategory = Category::getDefault();

            // Process the update with category context
            $this->updateProcessor->processUpdate($update, $defaultCategory);

            Log::info('Webhook processed successfully', [
                'update_id' => $update->update_id ?? 'unknown',
                'category' => 'default'
            ]);

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_body' => $request->getContent(),
            ]);

            // Return 200 to prevent Telegram from retrying
            return response()->json([
                'status' => 'error',
                'message' => 'Internal error'
            ], 200);
        }
    }

    /**
     * Handle incoming webhook from Telegram for specific category
     */
    public function handleCategory(Request $request, string $categorySlug): JsonResponse
    {
        try {
            // Find category by slug
            $category = Category::where('slug', $categorySlug)
                ->where('is_active', true)
                ->first();

            if (!$category) {
                Log::warning('Webhook received for unknown category', [
                    'category_slug' => $categorySlug,
                    'ip' => $request->ip(),
                ]);
                return response()->json(['status' => 'error', 'message' => 'Category not found'], 404);
            }

            // Verify webhook secret token
            if (!empty($category->webhook_secret)) {
                $incomingToken = $request->header('X-Telegram-Bot-Api-Secret-Token');
                if (!$incomingToken || !hash_equals($category->webhook_secret, $incomingToken)) {
                    Log::warning('Invalid webhook secret token for category', [
                        'category' => $category->name,
                        'ip' => $request->ip(),
                    ]);
                    return response()->json(['status' => 'unauthorized'], 401);
                }
            }

            Log::info('Webhook request received for category', [
                'category' => $category->name,
                'category_slug' => $categorySlug,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Create API instance for this category's bot
            $bot = new Api($category->bot_token);

            // Parse update from request body
            $updateData = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Webhook received invalid JSON payload', [
                    'category' => $category->name,
                    'error' => json_last_error_msg(),
                ]);
                return response()->json(['status' => 'bad_request', 'message' => 'Invalid JSON'], 400);
            }

            if ($updateData === null) {
                Log::warning('Webhook received but no update data found', [
                    'category' => $category->name
                ]);
                return response()->json(['status' => 'ok', 'message' => 'No update']);
            }

            // Create Update object
            $update = new Update($updateData);

            // Set category context in telegram service
            $this->telegramService->setCategory($category);

            // Process the update with category context
            $this->updateProcessor->processUpdate($update, $category);

            Log::info('Webhook processed successfully', [
                'update_id' => $update->update_id ?? 'unknown',
                'category' => $category->name
            ]);

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Webhook processing error for category', [
                'category_slug' => $categorySlug,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_body' => $request->getContent(),
            ]);

            // Return 200 to prevent Telegram from retrying
            return response()->json([
                'status' => 'error',
                'message' => 'Internal error'
            ], 200);
        }
    }

    /**
     * Health check endpoint for webhook (default bot)
     */
    public function health(): JsonResponse
    {
        try {
            $webhookInfo = Telegram::getWebhookInfo();

            return response()->json([
                'status' => 'ok',
                'mode' => 'webhook',
                'webhook_url' => $webhookInfo->url ?? null,
                'pending_update_count' => $webhookInfo->pending_update_count ?? 0,
                'last_error_date' => $webhookInfo->last_error_date ?? null,
                'last_error_message' => $webhookInfo->last_error_message ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Health check endpoint for specific category's webhook
     */
    public function healthCategory(string $categorySlug): JsonResponse
    {
        try {
            $category = Category::where('slug', $categorySlug)->first();

            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found',
                ], 404);
            }

            $webhookInfo = $this->telegramService->getWebhookInfo($category);

            return response()->json([
                'status' => 'ok',
                'mode' => 'webhook',
                'category' => $category->name,
                'bot_username' => $category->bot_username,
                'webhook_url' => $webhookInfo['url'] ?? null,
                'pending_update_count' => $webhookInfo['pending_update_count'] ?? 0,
                'last_error_date' => $webhookInfo['last_error_date'] ?? null,
                'last_error_message' => $webhookInfo['last_error_message'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set webhook for a specific category (admin action)
     */
    public function setWebhook(Request $request, string $categorySlug): JsonResponse
    {
        try {
            $category = Category::where('slug', $categorySlug)->first();

            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found',
                ], 404);
            }

            $success = $this->telegramService->setWebhook($category);

            if ($success) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'Webhook set successfully',
                    'webhook_url' => $category->webhook_url,
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to set webhook',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete webhook for a specific category (admin action)
     */
    public function deleteWebhook(Request $request, string $categorySlug): JsonResponse
    {
        try {
            $category = Category::where('slug', $categorySlug)->first();

            if (!$category) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category not found',
                ], 404);
            }

            $success = $this->telegramService->deleteWebhook($category);

            if ($success) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'Webhook deleted successfully',
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete webhook',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
