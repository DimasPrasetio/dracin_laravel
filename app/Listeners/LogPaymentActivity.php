<?php

namespace App\Listeners;

use App\Events\PaymentCreated;
use App\Events\PaymentExpired;
use App\Events\PaymentFailed;
use App\Events\PaymentPaid;
use Illuminate\Support\Facades\Log;

class LogPaymentActivity
{
    public function handleCreated(PaymentCreated $event): void
    {
        Log::info('Payment created', [
            'payment_id' => $event->payment->id,
            'reference' => $event->payment->tripay_reference,
            'amount' => $event->payment->amount,
            'package' => $event->payment->package,
            'telegram_id' => $event->payment->user?->telegram_id,
            'payment_method' => $event->payment->payment_method,
        ]);
    }

    public function handlePaid(PaymentPaid $event): void
    {
        Log::info('Payment successfully paid', [
            'payment_id' => $event->payment->id,
            'reference' => $event->payment->tripay_reference,
            'amount' => $event->payment->amount,
            'telegram_id' => $event->payment->user?->telegram_id,
        ]);
    }

    public function handleExpired(PaymentExpired $event): void
    {
        Log::warning('Payment expired', [
            'payment_id' => $event->payment->id,
            'reference' => $event->payment->tripay_reference,
            'amount' => $event->payment->amount,
            'telegram_id' => $event->payment->user?->telegram_id,
        ]);
    }

    public function handleFailed(PaymentFailed $event): void
    {
        Log::warning('Payment failed/cancelled', [
            'payment_id' => $event->payment->id,
            'reference' => $event->payment->tripay_reference,
            'amount' => $event->payment->amount,
            'telegram_id' => $event->payment->user?->telegram_id,
        ]);
    }
}
