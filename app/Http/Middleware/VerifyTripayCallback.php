<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyTripayCallback
{
    /**
     * Tripay IP whitelist for callback
     *
     * @var array<string>
     */
    private const TRIPAY_IPS = [
        '95.111.200.230', // Tripay IPv4 (Official)
        '2a04:3543:1000:2310:ac92:4cff:fe87:63f9', // Tripay IPv6 (Official)
        '182.10.130.63', // Tripay IPv4 (Additional)
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $request->ip();
        $userAgent = $request->userAgent();

        // Log incoming callback for debugging
        Log::info('Tripay callback received', [
            'ip' => $clientIp,
            'user_agent' => $userAgent,
            'headers' => [
                'X-Callback-Event' => $request->header('X-Callback-Event'),
                'X-Callback-Signature' => $request->header('X-Callback-Signature'),
            ],
            'payload' => $request->all(),
        ]);

        // Skip all verification in local development
        if (app()->environment('local')) {
            Log::warning('Tripay callback verification skipped (local environment)');
            return $next($request);
        }

        // Check if this is a test callback from Tripay dashboard
        $isTestCallback = $this->isTestCallback($request);

        if ($isTestCallback) {
            Log::info('Tripay test callback detected', [
                'ip' => $clientIp,
                'note' => $request->input('note'),
            ]);

            // For test callback, verify using X-Callback-Signature header only
            if ($this->hasValidHeaderSignature($request)) {
                Log::info('Test callback signature verified via header');
                return $next($request);
            }

            // If header signature fails, try body signature for backward compatibility
            if ($this->hasValidBodySignature($request)) {
                Log::info('Test callback signature verified via body');
                return $next($request);
            }

            Log::warning('Test callback signature verification failed', [
                'ip' => $clientIp,
                'header_signature' => $request->header('X-Callback-Signature'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid test callback signature'
            ], 400);
        }

        // For real callback, verify IP address first
        if (!$this->isValidTripayIp($clientIp)) {
            Log::error('Unauthorized Tripay callback attempt - Invalid IP', [
                'ip' => $clientIp,
                'user_agent' => $userAgent,
                'expected_ips' => self::TRIPAY_IPS,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized IP address'
            ], 403);
        }

        // Verify callback signature (header first, then body)
        if (!$this->hasValidHeaderSignature($request) && !$this->hasValidBodySignature($request)) {
            Log::error('Invalid Tripay callback signature', [
                'ip' => $clientIp,
                'reference' => $request->input('reference'),
                'merchant_ref' => $request->input('merchant_ref'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature'
            ], 400);
        }

        Log::info('Tripay callback verification passed', [
            'ip' => $clientIp,
            'reference' => $request->input('reference'),
            'merchant_ref' => $request->input('merchant_ref'),
        ]);

        return $next($request);
    }

    /**
     * Check if IP is from Tripay
     */
    private function isValidTripayIp(string $ip): bool
    {
        return in_array($ip, self::TRIPAY_IPS, true);
    }

    /**
     * Check if this is a test callback from Tripay dashboard
     */
    private function isTestCallback(Request $request): bool
    {
        // Test callback indicators:
        // 1. reference and merchant_ref are null
        // 2. note contains "Test Callback"
        // 3. Has X-Callback-Event header
        $reference = $request->input('reference');
        $merchantRef = $request->input('merchant_ref');
        $note = $request->input('note', '');
        $hasCallbackEvent = $request->hasHeader('X-Callback-Event');

        return (
            is_null($reference) &&
            is_null($merchantRef) &&
            (str_contains($note, 'Test') || str_contains($note, 'test')) &&
            $hasCallbackEvent
        );
    }

    /**
     * Verify callback signature from X-Callback-Signature header
     * This is the recommended method by Tripay
     */
    private function hasValidHeaderSignature(Request $request): bool
    {
        $privateKey = config('tripay.private_key');
        $headerSignature = $request->header('X-Callback-Signature');

        if (empty($privateKey) || empty($headerSignature)) {
            return false;
        }

        // Get raw JSON payload
        $jsonPayload = $request->getContent();

        if (empty($jsonPayload)) {
            return false;
        }

        // Compute signature: HMAC-SHA256 of raw JSON body
        $computedSignature = hash_hmac('sha256', $jsonPayload, $privateKey);

        $isValid = hash_equals($computedSignature, $headerSignature);

        if (!$isValid) {
            Log::debug('Header signature verification failed', [
                'computed' => $computedSignature,
                'received' => $headerSignature,
            ]);
        }

        return $isValid;
    }

    /**
     * Verify callback signature from request body (legacy method)
     * Used as fallback for backward compatibility
     */
    private function hasValidBodySignature(Request $request): bool
    {
        $privateKey = config('tripay.private_key');
        $merchantRef = $request->input('merchant_ref', '');
        $status = $request->input('status', '');
        $bodySignature = $request->input('signature', '');

        // For test callback, merchant_ref and status might be null
        if (empty($privateKey) || empty($bodySignature)) {
            return false;
        }

        // If merchant_ref or status is empty, skip body signature verification
        if (empty($merchantRef) || empty($status)) {
            Log::debug('Skipping body signature verification (missing merchant_ref or status)');
            return false;
        }

        // Compute signature: HMAC-SHA256 of merchant_ref + status
        $computedSignature = hash_hmac('sha256', $merchantRef . $status, $privateKey);

        $isValid = hash_equals($computedSignature, $bodySignature);

        if (!$isValid) {
            Log::debug('Body signature verification failed', [
                'computed' => $computedSignature,
                'received' => $bodySignature,
                'merchant_ref' => $merchantRef,
                'status' => $status,
            ]);
        }

        return $isValid;
    }
}
