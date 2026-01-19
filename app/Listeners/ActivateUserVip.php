<?php

namespace App\Listeners;

use App\Events\PaymentPaid;
use App\Models\Payment;
use App\Services\VipService;
use Illuminate\Support\Facades\Log;

class ActivateUserVip
{
    public function __construct(
        private readonly VipService $vipService
    ) {}

    public function handle(PaymentPaid $event): void
    {
        try {
            $payment = $event->payment;
            $categoryId = $payment->category_id ?? \App\Models\Category::getDefault()?->id;
            if (!$categoryId) {
                $exception = new \RuntimeException('Cannot activate VIP: category is missing.');
                Log::error('Skipping VIP activation: missing category', [
                    'payment_id' => $payment->id,
                    'telegram_id' => $payment->user?->telegram_id,
                ]);
                report($exception);
                return;
            }

            if ($payment->user && $payment->user->isVipForCategory($categoryId)) {
                Log::info('Skipping VIP activation: user already active', [
                    'payment_id' => $payment->id,
                    'telegram_id' => $payment->user?->telegram_id,
                    'category_id' => $categoryId,
                ]);
                return;
            }
            $duration = $payment->package_duration_days
                ?? Payment::getPackageDuration($payment->package, $categoryId);

            // Activate VIP with category context if available
            $this->vipService->activateVipForCategory(
                $payment->user,
                $categoryId,
                $duration
            );

            Log::info('VIP activated successfully', [
                'payment_id' => $payment->id,
                'telegram_id' => $payment->user?->telegram_id,
                'package' => $payment->package,
                'duration' => $duration,
                'category_id' => $categoryId,
                'category_name' => $payment->category?->name,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to activate VIP', [
                'payment_id' => $event->payment->id,
                'category_id' => $event->payment->category_id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
