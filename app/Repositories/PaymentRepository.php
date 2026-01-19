<?php

namespace App\Repositories;

use App\DataTransferObjects\PaymentData;
use App\DataTransferObjects\TripayPaymentResponse;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository
{
    /**
     * Create a new payment record
     */
    public function create(PaymentData $paymentData, TripayPaymentResponse $tripayResponse): Payment
    {
        return Payment::create([
            'user_id' => $paymentData->user->id,
            'category_id' => $paymentData->categoryId,
            'package' => $paymentData->package,
            'package_name' => $paymentData->packageName,
            'package_duration_days' => $paymentData->packageDurationDays,
            'package_price' => $paymentData->packagePrice,
            'amount' => $paymentData->amount,
            'payment_method' => $paymentData->paymentMethod,
            'status' => 'pending',
            'tripay_reference' => $tripayResponse->reference,
            'tripay_merchant_ref' => $paymentData->merchantRef,
            'tripay_payment_method' => $tripayResponse->paymentMethod,
            'tripay_payment_name' => $tripayResponse->paymentName,
            'tripay_pay_url' => $tripayResponse->payUrl,
            'tripay_qr_string' => $tripayResponse->qrString,
            'tripay_checkout_url' => $tripayResponse->checkoutUrl,
            'expired_at' => Carbon::createFromTimestamp($tripayResponse->expiredTime),
        ]);
    }

    /**
     * Find payment by Tripay reference
     */
    public function findByReference(string $reference): ?Payment
    {
        return Payment::where('tripay_reference', $reference)
            ->with('user')
            ->first();
    }

    /**
     * Find existing pending payment that can be reused
     * Only reuse if payment has at least 10 minutes remaining before expiry
     */
    public function findReusablePayment(
        int $userId,
        string $package,
        string $paymentMethod,
        ?int $categoryId = null,
        ?int $amount = null
    ): ?Payment
    {
        $minimumBufferMinutes = 10;

        $query = Payment::where('user_id', $userId)
            ->where('package', $package)
            ->where('payment_method', $paymentMethod)
            ->where('status', 'pending')
            ->where('expired_at', '>', now()->addMinutes($minimumBufferMinutes));

        if ($amount !== null) {
            $query->where('amount', $amount);
        }

        // Filter by category if specified
        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        } else {
            $query->whereNull('category_id');
        }

        return $query->orderByDesc('created_at')->first();
    }

    /**
     * Find payment by merchant reference
     */
    public function findByMerchantRef(string $merchantRef): ?Payment
    {
        return Payment::where('tripay_merchant_ref', $merchantRef)
            ->with('user')
            ->first();
    }

    /**
     * Update payment status
     */
    public function updateStatus(Payment $payment, string $status): bool
    {
        return $payment->update(['status' => $status]);
    }

    /**
     * Get pending payments
     */
    public function getPendingPayments(): Collection
    {
        return Payment::where('status', 'pending')
            ->where('expired_at', '>', now())
            ->with('user')
            ->get();
    }

    /**
     * Get expired pending payments
     */
    public function getExpiredPendingPayments(): Collection
    {
        return Payment::where('status', 'pending')
            ->where('expired_at', '<=', now())
            ->with('user')
            ->get();
    }

    /**
     * Get user's payment history
     */
    public function getUserPayments(int $userId, int $limit = 10): Collection
    {
        return Payment::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get payment statistics
     */
    public function getStatistics(string $period = 'today', ?int $categoryId = null): array
    {
        $query = Payment::query();

        // Filter by category if specified
        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        match ($period) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month),
            'year' => $query->whereYear('created_at', now()->year),
            default => $query,
        };

        return [
            'total' => $query->count(),
            'paid' => (clone $query)->where('status', 'paid')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'expired' => (clone $query)->where('status', 'expired')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'revenue' => (clone $query)->where('status', 'paid')->sum('amount'),
        ];
    }

    /**
     * Get statistics for specific categories (for admin dashboard)
     */
    public function getStatisticsForCategories(array $categoryIds, string $period = 'today'): array
    {
        $query = Payment::query();

        if (!empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        match ($period) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month),
            'year' => $query->whereYear('created_at', now()->year),
            default => $query,
        };

        return [
            'total' => $query->count(),
            'paid' => (clone $query)->where('status', 'paid')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'expired' => (clone $query)->where('status', 'expired')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'revenue' => (clone $query)->where('status', 'paid')->sum('amount'),
        ];
    }
}
