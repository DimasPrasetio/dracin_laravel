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
        return DB::transaction(function () use ($payment, $newStatus) {
            $lockedPayment = Payment::whereKey($payment->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedPayment) {
                return $payment;
            }

            if ($lockedPayment->status === $newStatus) {
                return $lockedPayment;
            }

            $oldStatus = $lockedPayment->status;

            $lockedPayment->update(['status' => $newStatus]);
            $lockedPayment->refresh();

            PaymentStatusCache::forget($lockedPayment->tripay_reference, $lockedPayment->tripay_merchant_ref);

            DB::afterCommit(function () use ($lockedPayment, $newStatus) {
                $this->dispatchEvent($lockedPayment, $newStatus);
            });

            Log::info('Payment status transitioned', [
                'payment_id' => $payment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return $lockedPayment;
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
