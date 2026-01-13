<?php

namespace App\Services;

use App\Events\PaymentExpired;
use App\Events\PaymentFailed;
use App\Events\PaymentPaid;
use App\Models\Payment;
use App\Support\PaymentStatusCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentStatusService
{
    /**
     * Update payment status atomically and dispatch events after commit.
     */
    public function transition(Payment $payment, string $newStatus): Payment
    {
        if ($payment->status === $newStatus) {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $newStatus) {
            $oldStatus = $payment->status;

            $payment->update(['status' => $newStatus]);
            $payment->refresh();

            PaymentStatusCache::forget($payment->tripay_reference, $payment->tripay_merchant_ref);

            DB::afterCommit(function () use ($payment, $newStatus) {
                $this->dispatchEvent($payment, $newStatus);
            });

            Log::info('Payment status transitioned', [
                'payment_id' => $payment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return $payment;
        });
    }

    /**
     * Clear cached Tripay statuses for given identifiers.
     */
    public function clearStatusCache(?string $reference, ?string $merchantRef = null): void
    {
        PaymentStatusCache::forget($reference, $merchantRef);
    }

    private function dispatchEvent(Payment $payment, string $status): void
    {
        match ($status) {
            'paid' => event(new PaymentPaid($payment)),
            'expired' => event(new PaymentExpired($payment)),
            'cancelled' => event(new PaymentFailed($payment)),
            default => null,
        };
    }
}
