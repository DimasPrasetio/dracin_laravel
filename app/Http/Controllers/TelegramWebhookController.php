<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Update;
use App\Services\TelegramUpdateProcessor;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected $updateProcessor;

    public function __construct(TelegramUpdateProcessor $updateProcessor)
    {
        $this->updateProcessor = $updateProcessor;
    }

    /**
     * Handle incoming webhook from Telegram
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

            Log::info('Webhook request received', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Get update from request
            $update = Telegram::getWebhookUpdate();

            if (!$update) {
                Log::warning('Webhook received but no update found');
                return response()->json(['status' => 'ok', 'message' => 'No update']);
            }

            // Process the update
            $this->updateProcessor->processUpdate($update);

            Log::info('Webhook processed successfully', [
                'update_id' => $update->update_id ?? 'unknown'
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
     * Health check endpoint for webhook
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
}
