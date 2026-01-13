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

            $this->vipService->activateVip(
                $payment->telegramUser,
                $payment->package
            );

            $duration = Payment::getPackageDuration($payment->package);

            Log::info('VIP activated successfully', [
                'payment_id' => $payment->id,
                'telegram_user_id' => $payment->telegramUser->telegram_user_id,
                'package' => $payment->package,
                'duration' => $duration,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to activate VIP', [
                'payment_id' => $event->payment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

}
