<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\PaymentData;
use App\DataTransferObjects\TripayCallbackData;
use App\Exceptions\TripayException;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\TripayCallbackRequest;
use App\Models\TelegramUser;
use App\Repositories\PaymentRepository;
use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly TripayService $tripayService,
        private readonly PaymentRepository $paymentRepository,
    ) {}

    /**
     * Show landing page with VIP packages
     */
    public function index()
    {
        $packages = $this->tripayService->getPackages();
        $paymentStatus = $this->tripayService->isAvailable();
        return view('frontend.landing', compact('packages', 'paymentStatus'));
    }

    /**
     * Show checkout form
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'package' => 'required|in:1day,3days,7days,30days',
        ]);

        $package = $request->input('package');
        $packageData = $this->tripayService->getPackageDetails($package);

        if (!$packageData) {
            return redirect()->route('landing')
                ->with('error', 'Paket tidak valid');
        }

        // Check payment gateway availability with retry logic
        $paymentStatus = $this->tripayService->isAvailable();
        $channels = [];

        // Only fetch channels if payment gateway is available
        if ($paymentStatus['available']) {
            try {
                // Get available payment channels with automatic retry
                $allChannels = $this->tripayService->getPaymentChannels();

                // Filter only required channels and calculate fees
                $channels = collect($allChannels)->filter(function ($channel) {
                    return in_array($channel['group'], ['Virtual Account', 'E-Wallet', 'Convenience Store']) &&
                           ($channel['code'] === 'QRIS' ||
                            in_array($channel['code'], ['BCAVA', 'BNIVA', 'BRIVA', 'MANDIRIVA', 'PERMATAVA']));
                })->map(function ($channel) use ($packageData) {
                    // Calculate fee for this channel
                    $baseAmount = $packageData['price'];
                    $feeFlat = $channel['fee_customer']['flat'] ?? 0;
                    $feePercent = $channel['fee_customer']['percent'] ?? 0;

                    $feeAmount = $feeFlat + ($baseAmount * $feePercent / 100);
                    $totalAmount = $baseAmount + $feeAmount;

                    $channel['calculated_fee'] = (int) $feeAmount;
                    $channel['total_amount'] = (int) $totalAmount;

                    return $channel;
                })->values()->all();

            } catch (TripayException $e) {
                Log::error('Failed to fetch payment channels for checkout', [
                    'error' => $e->getMessage(),
                    'package' => $package,
                ]);

                // Update payment status to reflect the error
                $paymentStatus = [
                    'available' => false,
                    'reason' => 'channels_fetch_failed',
                    'description' => 'Gagal memuat metode pembayaran. Silakan coba lagi.',
                ];
            }
        }

        return view('frontend.checkout', compact('package', 'packageData', 'channels', 'paymentStatus'));
    }

    /**
     * Retry payment gateway health check (AJAX)
     */
    public function retryHealthCheck()
    {
        try {
            $paymentStatus = $this->tripayService->refreshHealthStatus();

            return response()->json([
                'success' => true,
                'available' => $paymentStatus['available'],
                'reason' => $paymentStatus['reason'] ?? null,
                'description' => $paymentStatus['description'],
            ]);

        } catch (\Exception $e) {
            Log::error('Health check retry failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'available' => false,
                'reason' => 'retry_failed',
                'description' => 'Gagal memeriksa status. Silakan refresh halaman.',
            ], 500);
        }
    }

    /**
     * Process checkout and create payment
     */
    public function processCheckout(CheckoutRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                // Find or create telegram user
                $telegramUser = $this->findOrCreateTelegramUser($request->validated());

                // Get package details
                $packageData = $this->tripayService->getPackageDetails($request->package);

                if (!$packageData) {
                    throw TripayException::invalidPackage($request->package);
                }

                // Create payment data DTO
                $paymentData = PaymentData::fromRequest(
                    $request->validated(),
                    $telegramUser,
                    $packageData['price']
                );

                // Create payment via Tripay
                $result = $this->tripayService->createPayment($paymentData);

                if (!$result['success']) {
                    return redirect()->back()
                        ->with('error', 'Gagal membuat pembayaran. Silakan coba lagi.')
                        ->withInput();
                }

                // Redirect to payment page
                return redirect()->route('payment.show', [
                    'reference' => $result['reference']
                ]);
            });

        } catch (TripayException $e) {
            Log::error('Checkout process failed with Tripay exception', [
                'error' => $e->getMessage(),
                'telegram_user_id' => $request->telegram_user_id,
            ]);

            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Checkout process failed with unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Show payment page with QRIS or Virtual Account
     */
    public function showPayment(string $reference)
    {
        try {
            $payment = $this->paymentRepository->findByReference($reference);

            if (!$payment) {
                return redirect()->route('landing')
                    ->with('error', 'Pembayaran tidak ditemukan');
            }

            // Check if payment is still pending, if yes update from Tripay
            if ($payment->status === 'pending') {
                try {
                    $tripayData = $this->tripayService->checkPaymentStatus($reference);
                    $this->updatePaymentStatusFromTripay($payment, $tripayData);
                } catch (\Exception $e) {
                    Log::warning('Failed to check payment status', [
                        'reference' => $reference,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return view('frontend.payment', compact('payment'));

        } catch (\Exception $e) {
            Log::error('Failed to show payment page', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('landing')
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    /**
     * Check payment status (AJAX)
     */
    public function checkStatus(string $reference)
    {
        try {
            $payment = $this->paymentRepository->findByReference($reference);

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran tidak ditemukan'
                ], 404);
            }

            // Check from Tripay
            try {
                $tripayData = $this->tripayService->checkPaymentStatus($reference);
                $this->updatePaymentStatusFromTripay($payment, $tripayData);
            } catch (\Exception $e) {
                Log::warning('Failed to check status via API', [
                    'reference' => $reference,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $payment->status,
                'payment' => $payment->load('telegramUser'),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to check payment status', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa status pembayaran'
            ], 500);
        }
    }

    /**
     * Handle Tripay callback webhook
     */
    public function callback(TripayCallbackRequest $request)
    {
        try {
            $callbackData = TripayCallbackData::fromRequest($request->validated());

            $this->tripayService->handleCallback($callbackData);

            return response()->json(['success' => true]);

        } catch (TripayException $e) {
            Log::error('Callback handling failed', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

        } catch (\Exception $e) {
            Log::critical('Callback handling failed critically', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Find or create telegram user
     */
    private function findOrCreateTelegramUser(array $data): TelegramUser
    {
        $telegramUser = TelegramUser::where('telegram_user_id', $data['telegram_user_id'])->first();

        if (!$telegramUser) {
            $telegramUser = TelegramUser::create([
                'telegram_user_id' => $data['telegram_user_id'],
                'username' => $data['username'] ?? null,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
            ]);

            Log::info('New Telegram user created during checkout', [
                'telegram_user_id' => $data['telegram_user_id'],
            ]);
        } else {
            // Update user data
            $telegramUser->update([
                'username' => $data['username'] ?? $telegramUser->username,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? $telegramUser->last_name,
            ]);
        }

        return $telegramUser;
    }

    /**
     * Update payment status from Tripay data
     */
    private function updatePaymentStatusFromTripay($payment, array $tripayData): void
    {
        $tripayStatus = $tripayData['status'] ?? 'UNPAID';
        $newStatus = $this->mapTripayStatus($tripayStatus);

        if ($payment->status !== $newStatus) {
            $oldStatus = $payment->status;
            $this->paymentRepository->updateStatus($payment, $newStatus);
            $payment->refresh();

            Log::info('Payment status updated from Tripay API', [
                'payment_id' => $payment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            // Dispatch event based on status
            $this->dispatchPaymentEvent($payment, $newStatus);
        }
    }

    /**
     * Map Tripay status to our status
     */
    private function mapTripayStatus(string $tripayStatus): string
    {
        return match ($tripayStatus) {
            'PAID' => 'paid',
            'EXPIRED' => 'expired',
            'FAILED', 'REFUND' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * Dispatch payment event based on status
     */
    private function dispatchPaymentEvent($payment, string $status): void
    {
        match ($status) {
            'paid' => event(new \App\Events\PaymentPaid($payment)),
            'expired' => event(new \App\Events\PaymentExpired($payment)),
            'cancelled' => event(new \App\Events\PaymentFailed($payment)),
            default => null,
        };
    }
}
