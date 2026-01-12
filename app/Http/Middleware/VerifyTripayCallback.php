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
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip IP check in local development
        if (app()->environment('local')) {
            Log::warning('Tripay callback IP verification skipped (local environment)');
            return $next($request);
        }

        // Verify IP address
        $clientIp = $request->ip();

        if (!$this->isValidTripayIp($clientIp)) {
            Log::error('Unauthorized Tripay callback attempt', [
                'ip' => $clientIp,
                'user_agent' => $request->userAgent(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Verify callback signature
        if (!$this->hasValidSignature($request)) {
            Log::error('Invalid Tripay callback signature', [
                'ip' => $clientIp,
                'reference' => $request->input('reference'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature'
            ], 400);
        }

        Log::info('Tripay callback verification passed', [
            'ip' => $clientIp,
            'reference' => $request->input('reference'),
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
     * Verify callback signature
     */
    private function hasValidSignature(Request $request): bool
    {
        $privateKey = config('tripay.private_key');
        $merchantRef = $request->input('merchant_ref', '');
        $status = $request->input('status', '');
        $callbackSignature = $request->input('signature', '');

        if (empty($privateKey) || empty($merchantRef) || empty($status) || empty($callbackSignature)) {
            return false;
        }

        $computedSignature = hash_hmac('sha256', $merchantRef . $status, $privateKey);

        return hash_equals($computedSignature, $callbackSignature);
    }
}
