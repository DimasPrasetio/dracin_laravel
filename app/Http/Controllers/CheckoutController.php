<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\PaymentData;
use App\DataTransferObjects\TripayCallbackData;
use App\Exceptions\TripayException;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\TripayCallbackRequest;
use App\Models\Category;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\PaymentRepository;
use App\Services\PaymentStatusService;
use App\Services\TripayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly TripayService $tripayService,
        private readonly PaymentRepository $paymentRepository,
        private readonly PaymentStatusService $paymentStatusService,
    ) {}

    /**
     * Show landing page with VIP packages
     */
    public function index()
    {
        $category = Category::getDefault();
        $packages = $this->tripayService->getPackages($category?->id);
        $paymentStatus = $this->tripayService->isAvailable();
        return view('frontend.landing', compact('packages', 'paymentStatus'));
    }

    /**
     * Show checkout form
     */
    public function checkout(Request $request)
    {
        $category = Category::getDefault();
        $packageCodes = app(\App\Services\VipService::class)->getPackageCodes($category?->id);

        $request->validate([
            'package' => [
                'required',
                'string',
                Rule::in($packageCodes),
            ],
        ]);

        $package = $request->input('package');
        $packageData = $this->tripayService->getPackageDetails($package, $category?->id);

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
                // Find or create user from Telegram details
                $user = $this->findOrCreateUser($request->validated());
                $category = Category::getDefault();

                if ($category && $user->isVipForCategory($category->id)) {
                    $vipUntil = $user->getVipExpiryForCategory($category->id);
                    $expiryText = $vipUntil?->format('d M Y H:i') ?? 'N/A';
                    return redirect()->back()
                        ->with('error', 'Anda masih dalam layanan VIP sampai ' .
                               $expiryText .
                               '. Pembelian baru dapat dilakukan setelah VIP expired.')
                        ->withInput();
                }

                // Get package details
                $packageData = $this->tripayService->getPackageDetails($request->package, $category?->id);

                if (!$packageData) {
                    throw TripayException::invalidPackage($request->package);
                }

                // Reuse pending payment if available to avoid duplicates
                $existingPayment = $this->paymentRepository->findReusablePayment(
                    $user->id,
                    $request->package,
                    $request->payment_method,
                    $category?->id,
                    (int) ($packageData['price'] ?? 0)
                );

                if ($existingPayment && $existingPayment->tripay_reference) {
                    return redirect()->route('payment.show', [
                        'reference' => $existingPayment->tripay_reference,
                    ])->with('info', 'Pembayaran sebelumnya masih aktif. Gunakan link yang sama.');
                }

                // Create payment data DTO
                $paymentData = PaymentData::fromRequest(
                    array_merge($request->validated(), [
                        'category_id' => $category?->id,
                    ]),
                    $user,
                    (int) ($packageData['price'] ?? 0),
                    $packageData
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
                'telegram_id' => $request->telegram_user_id,
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
                    $payment = $this->updatePaymentStatusFromTripay($payment, $tripayData);
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
                $payment = $this->updatePaymentStatusFromTripay($payment, $tripayData);
            } catch (\Exception $e) {
                Log::warning('Failed to check status via API', [
                    'reference' => $reference,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $payment->status,
                'payment' => $payment->load('user'),
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
            // Check if this is a test callback from Tripay dashboard
            $isTestCallback = $this->isTestCallback($request);

            if ($isTestCallback) {
                Log::info('Tripay test callback processed successfully', [
                    'note' => $request->input('note'),
                    'status' => $request->input('status'),
                    'payment_method' => $request->input('payment_method'),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Test callback received successfully'
                ]);
            }

            // Process real callback
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
     * Check if this is a test callback from Tripay dashboard
     */
    private function isTestCallback($request): bool
    {
        $reference = $request->input('reference');
        $merchantRef = $request->input('merchant_ref');
        $note = $request->input('note', '');

        return (
            is_null($reference) &&
            is_null($merchantRef) &&
            (str_contains($note, 'Test') || str_contains($note, 'test'))
        );
    }

    /**
     * Find or create telegram user
     */
    private function findOrCreateUser(array $data): User
    {
        $telegramId = (int) $data['telegram_user_id'];

        $user = User::where('telegram_id', $telegramId)->first();

        if (!$user) {
            $user = User::create([
                'telegram_id' => $telegramId,
                'username' => $data['username'] ?? null,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'name' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))
                    ?: ($data['username'] ?? 'User ' . $telegramId),
                'role' => User::ROLE_USER,
            ]);

            Log::info('New Telegram user created during checkout', [
                'telegram_id' => $telegramId,
            ]);
        } else {
            $user->update([
                'username' => $data['username'] ?? $user->username,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? $user->last_name,
                'name' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''))
                    ?: ($data['username'] ?? $user->name),
            ]);
        }

        return $user;
    }

    /**
     * Update payment status from Tripay data
     */
    private function updatePaymentStatusFromTripay(Payment $payment, array $tripayData): Payment
    {
        $tripayStatus = $tripayData['status'] ?? 'UNPAID';
        $newStatus = $this->mapTripayStatus($tripayStatus);

        if ($payment->status !== $newStatus) {
            $oldStatus = $payment->status;
            $payment = $this->paymentStatusService->transition($payment, $newStatus);

            Log::info('Payment status updated from Tripay API', [
                'payment_id' => $payment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        }

        return $payment;
    }

    /**
     * Check VIP status for user (AJAX endpoint)
     */
    public function checkVipStatus(Request $request)
    {
        $request->validate([
            'telegram_user_id' => 'required|numeric|digits_between:1,20',
        ]);

        $user = User::where('telegram_id', $request->telegram_user_id)->first();
        $category = Category::getDefault();

        if ($user && $category && $user->isVipForCategory($category->id)) {
            $vipUntil = $user->getVipExpiryForCategory($category->id);
            return response()->json([
                'is_vip' => true,
                'vip_until' => $vipUntil?->format('d M Y H:i'),
                'message' => 'Anda masih dalam layanan VIP sampai ' . ($vipUntil?->format('d M Y H:i') ?? 'N/A'),
            ]);
        }

        return response()->json([
            'is_vip' => false,
            'message' => 'Anda dapat melanjutkan pembelian VIP',
        ]);
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

}
