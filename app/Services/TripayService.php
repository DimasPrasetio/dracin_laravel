<?php

namespace App\Services;

use App\DataTransferObjects\PaymentData;
use App\DataTransferObjects\TripayCallbackData;
use App\DataTransferObjects\TripayPaymentResponse;
use App\Events\PaymentCreated;
use App\Events\PaymentExpired;
use App\Events\PaymentFailed;
use App\Events\PaymentPaid;
use App\Exceptions\TripayException;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const HEALTH_CHECK_CACHE_TTL = 60; // 60 seconds
    private const MAX_RETRIES = 2;
    private const INITIAL_RETRY_DELAY_MS = 500;

    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly TripayCircuitBreaker $circuitBreaker,
    ) {
    }

    /**
     * Get available payment channels from Tripay with retry mechanism
     */
    public function getPaymentChannels(): array
    {
        return $this->executeWithRetry(function () {
            $this->validateConfiguration();

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get($this->getApiUrl() . '/merchant/payment-channel');

            if (!$response->successful()) {
                throw TripayException::channelsFetchFailed(
                    $response->json('message', 'Unknown error')
                );
            }

            $data = $response->json();

            Log::info('Tripay payment channels fetched successfully', [
                'count' => count($data['data'] ?? []),
            ]);

            return $data['data'] ?? [];
        }, 'fetch payment channels');
    }

    /**
     * Create payment transaction
     */
    public function createPayment(PaymentData $paymentData, ?int $expiresInSeconds = null): array
    {
        try {
            $this->validateConfiguration();
            $packageData = $this->getPackageDetails($paymentData->package);

            if (!$packageData) {
                throw TripayException::invalidPackage($paymentData->package);
            }

            $signature = $this->generateSignature(
                $paymentData->merchantRef,
                $paymentData->amount
            );

            $payload = $this->buildPaymentPayload($paymentData, $signature, $expiresInSeconds);

            $response = Http::withHeaders($this->getHeaders())
                ->timeout(15)
                ->post($this->getApiUrl() . '/transaction/create', $payload);

            if (!$response->successful() || !$response->json('success')) {
                throw TripayException::paymentCreationFailed(
                    $response->json('message', 'Unknown error')
                );
            }

            $tripayData = $response->json('data');
            $tripayResponse = TripayPaymentResponse::fromApiResponse($tripayData);

            // Save to database
            $payment = $this->paymentRepository->create($paymentData, $tripayResponse);

            // Dispatch event
            event(new PaymentCreated($payment));

            Log::info('Payment created successfully', [
                'payment_id' => $payment->id,
                'reference' => $payment->tripay_reference,
                'amount' => $payment->amount,
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'reference' => $tripayResponse->reference,
                'pay_url' => $tripayResponse->payUrl,
                'checkout_url' => $tripayResponse->checkoutUrl,
                'qr_string' => $tripayResponse->qrString,
            ];

        } catch (TripayException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to create payment', [
                'error' => $e->getMessage(),
                'package' => $paymentData->package,
            ]);

            throw TripayException::paymentCreationFailed($e->getMessage());
        }
    }

    /**
     * Handle Tripay webhook callback
     */
    public function handleCallback(TripayCallbackData $callbackData): bool
    {
        try {
            $this->validateConfiguration();
            // Find payment
            $payment = $this->paymentRepository->findByReference($callbackData->reference);

            if (!$payment) {
                throw TripayException::paymentNotFound($callbackData->reference);
            }

            $newStatus = $callbackData->getMappedStatus();
            $oldStatus = $payment->status;

            // Only update if status changed
            if ($oldStatus === $newStatus) {
                Log::info('Payment status unchanged', [
                    'payment_id' => $payment->id,
                    'status' => $newStatus,
                ]);
                return true;
            }

            // Update status
            $this->paymentRepository->updateStatus($payment, $newStatus);

            // Refresh model to get updated data
            $payment->refresh();

            // Dispatch appropriate event
            $this->dispatchPaymentEvent($payment, $newStatus);

            Log::info('Payment callback processed successfully', [
                'payment_id' => $payment->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return true;

        } catch (TripayException $e) {
            Log::error('Tripay callback processing failed', [
                'reference' => $callbackData->reference,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error in callback processing', [
                'reference' => $callbackData->reference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw TripayException::callbackProcessingFailed($e->getMessage());
        }
    }

    /**
     * Check payment status from Tripay API
     */
    public function checkPaymentStatus(string $reference): array
    {
        try {
            $this->validateConfiguration();
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(10)
                ->get($this->getApiUrl() . '/transaction/detail', [
                    'reference' => $reference
                ]);

            if (!$response->successful()) {
                throw new \Exception($response->json('message', 'Failed to fetch payment status'));
            }

            return $response->json('data', []);

        } catch (\Exception $e) {
            Log::error('Failed to check payment status', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get package details
     */
    public function getPackageDetails(string $package): ?array
    {
        $packages = config('tripay.packages', []);
        return $packages[$package] ?? null;
    }

    /**
     * Get all VIP packages
     */
    public function getPackages(): array
    {
        return config('tripay.packages', []);
    }

    /**
     * Check if Tripay is configured
     */
    public function isConfigured(): bool
    {
        return !empty(config('tripay.api_key')) &&
               !empty(config('tripay.private_key')) &&
               !empty(config('tripay.merchant_code'));
    }

    /**
     * Check if Tripay is available for payment creation with caching
     */
    public function isAvailable(): array
    {
        return Cache::remember('tripay_health_status', self::HEALTH_CHECK_CACHE_TTL, function () {
            if (!$this->isConfigured()) {
                return [
                    'available' => false,
                    'reason' => 'not_configured',
                    'description' => 'Tripay belum dikonfigurasi. Silakan set TRIPAY_API_KEY, TRIPAY_PRIVATE_KEY, dan TRIPAY_MERCHANT_CODE.',
                ];
            }

            // Check circuit breaker status first
            if ($this->circuitBreaker->isOpen()) {
                Log::warning('Tripay health check blocked by circuit breaker', [
                    'circuit_state' => $this->circuitBreaker->getState(),
                ]);

                return [
                    'available' => false,
                    'reason' => 'circuit_open',
                    'description' => 'Tripay sedang mengalami gangguan. Sistem akan mencoba kembali secara otomatis.',
                ];
            }

            try {
                // Perform lightweight health check
                $this->getPaymentChannels();

                return [
                    'available' => true,
                    'reason' => null,
                    'description' => '',
                ];
            } catch (TripayException $e) {
                Log::warning('Tripay health check failed', [
                    'error' => $e->getMessage(),
                    'timestamp' => now(),
                ]);

                return [
                    'available' => false,
                    'reason' => 'api_error',
                    'description' => 'Tripay tidak dapat diakses saat ini. Silakan coba lagi nanti.',
                ];
            }
        });
    }

    /**
     * Force refresh health check status (bust cache)
     */
    public function refreshHealthStatus(): array
    {
        Cache::forget('tripay_health_status');
        return $this->isAvailable();
    }

    /**
     * Validate Tripay configuration
     */
    private function validateConfiguration(): void
    {
        if (!$this->isConfigured()) {
            throw TripayException::notConfigured();
        }
    }

    /**
     * Build payment payload
     */
    private function buildPaymentPayload(PaymentData $paymentData, string $signature, ?int $expiresInSeconds = null): array
    {
        $packageData = $this->getPackageDetails($paymentData->package);
        $expiresInSeconds = $expiresInSeconds ?? (24 * 60 * 60);

        return [
            'method' => $paymentData->paymentMethod,
            'merchant_ref' => $paymentData->merchantRef,
            'amount' => $paymentData->amount,
            'customer_name' => $paymentData->telegramUser->full_name ?: 'User ' . $paymentData->telegramUser->telegram_user_id,
            'customer_email' => 'user' . $paymentData->telegramUser->telegram_user_id . '@dracinbot.com',
            'customer_phone' => '08' . substr($paymentData->telegramUser->telegram_user_id, 0, 10),
            'order_items' => [
                [
                    'name' => $packageData['name'],
                    'price' => $paymentData->amount,
                    'quantity' => 1,
                ]
            ],
            'callback_url' => config('tripay.callback_url'),
            'return_url' => config('tripay.return_url'),
            'expired_time' => (time() + $expiresInSeconds),
            'signature' => $signature
        ];
    }

    /**
     * Generate signature for Tripay API
     */
    private function generateSignature(string $merchantRef, int $amount): string
    {
        $merchantCode = config('tripay.merchant_code');
        $privateKey = config('tripay.private_key');
        $data = $merchantCode . $merchantRef . $amount;
        return hash_hmac('sha256', $data, $privateKey);
    }

    /**
     * Get HTTP headers for Tripay API
     */
    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('tripay.api_key'),
            'Accept' => 'application/json',
        ];
    }

    /**
     * Get API URL based on mode
     */
    private function getApiUrl(): string
    {
        $mode = config('tripay.mode', 'sandbox');
        return config('tripay.api_url')[$mode];
    }

    /**
     * Dispatch appropriate payment event based on status
     */
    private function dispatchPaymentEvent($payment, string $status): void
    {
        match ($status) {
            'paid' => event(new PaymentPaid($payment)),
            'expired' => event(new PaymentExpired($payment)),
            'cancelled' => event(new PaymentFailed($payment)),
            default => null,
        };
    }

    /**
     * Execute API call with retry mechanism and circuit breaker
     *
     * @param callable $callback The API call to execute
     * @param string $operation Operation name for logging
     * @return mixed
     * @throws TripayException
     */
    private function executeWithRetry(callable $callback, string $operation): mixed
    {
        // Check circuit breaker first
        if ($this->circuitBreaker->isOpen()) {
            $this->circuitBreaker->incrementHalfOpenAttempts();

            Log::warning("Tripay operation blocked by circuit breaker: {$operation}", [
                'circuit_state' => $this->circuitBreaker->getState(),
            ]);

            throw TripayException::channelsFetchFailed(
                'Tripay service temporarily unavailable. Circuit breaker is open.'
            );
        }

        $attempt = 0;
        $lastException = null;

        while ($attempt <= self::MAX_RETRIES) {
            try {
                $result = $callback();

                // Record success for circuit breaker
                $this->circuitBreaker->recordSuccess();

                if ($attempt > 0) {
                    Log::info("Tripay operation succeeded after retry: {$operation}", [
                        'attempt' => $attempt + 1,
                    ]);
                }

                return $result;

            } catch (TripayException $e) {
                $lastException = $e;
                $attempt++;

                // Record failure for circuit breaker
                $this->circuitBreaker->recordFailure($e->getMessage());

                if ($attempt <= self::MAX_RETRIES) {
                    $delay = self::INITIAL_RETRY_DELAY_MS * (2 ** ($attempt - 1));

                    Log::warning("Tripay operation failed, retrying: {$operation}", [
                        'attempt' => $attempt,
                        'max_retries' => self::MAX_RETRIES,
                        'delay_ms' => $delay,
                        'error' => $e->getMessage(),
                    ]);

                    usleep($delay * 1000); // Convert to microseconds
                } else {
                    Log::error("Tripay operation failed after all retries: {$operation}", [
                        'attempts' => $attempt,
                        'error' => $e->getMessage(),
                    ]);
                }

            } catch (\Exception $e) {
                $lastException = $e;
                $attempt++;

                // Record generic failure
                $this->circuitBreaker->recordFailure($e->getMessage());

                Log::error("Unexpected error during Tripay operation: {$operation}", [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw TripayException::channelsFetchFailed($e->getMessage());
            }
        }

        throw $lastException ?? TripayException::channelsFetchFailed(
            "Operation failed after {$attempt} attempts"
        );
    }
}

