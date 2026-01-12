<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Circuit Breaker Pattern implementation for Tripay API
 *
 * Protects the application from cascading failures by preventing
 * repeated calls to a failing external service.
 *
 * States:
 * - CLOSED: Normal operation, all requests pass through
 * - OPEN: Service is failing, all requests are rejected
 * - HALF_OPEN: Testing if service has recovered
 */
class TripayCircuitBreaker
{
    private const CACHE_PREFIX = 'tripay_circuit_breaker';
    private const FAILURE_THRESHOLD = 5;
    private const SUCCESS_THRESHOLD = 2;
    private const TIMEOUT_SECONDS = 60;
    private const HALF_OPEN_MAX_REQUESTS = 3;

    /**
     * Check if circuit breaker is open (blocking requests)
     */
    public function isOpen(): bool
    {
        $state = $this->getState();

        if ($state === 'open') {
            $openedAt = Cache::get($this->getCacheKey('opened_at'));

            if ($openedAt && now()->diffInSeconds($openedAt) >= self::TIMEOUT_SECONDS) {
                // Transition to half-open state
                $this->transitionToHalfOpen();
                return false;
            }

            return true;
        }

        if ($state === 'half_open') {
            $attempts = Cache::get($this->getCacheKey('half_open_attempts'), 0);
            return $attempts >= self::HALF_OPEN_MAX_REQUESTS;
        }

        return false;
    }

    /**
     * Record a successful API call
     */
    public function recordSuccess(): void
    {
        $state = $this->getState();

        if ($state === 'half_open') {
            $successes = Cache::increment($this->getCacheKey('half_open_successes'));

            if ($successes >= self::SUCCESS_THRESHOLD) {
                $this->transitionToClosed();
                Log::info('Circuit breaker closed: Service recovered', [
                    'consecutive_successes' => $successes,
                ]);
            }
        } elseif ($state === 'closed') {
            // Reset failure count on success
            Cache::forget($this->getCacheKey('failures'));
        }
    }

    /**
     * Record a failed API call
     */
    public function recordFailure(string $reason = ''): void
    {
        $state = $this->getState();

        if ($state === 'half_open') {
            // If half-open test fails, go back to open
            $this->transitionToOpen();
            Log::warning('Circuit breaker re-opened: Half-open test failed', [
                'reason' => $reason,
            ]);
            return;
        }

        if ($state === 'closed') {
            $failures = Cache::increment($this->getCacheKey('failures'));

            if ($failures >= self::FAILURE_THRESHOLD) {
                $this->transitionToOpen();
                Log::error('Circuit breaker opened: Failure threshold reached', [
                    'failures' => $failures,
                    'threshold' => self::FAILURE_THRESHOLD,
                    'reason' => $reason,
                ]);
            }
        }
    }

    /**
     * Get current circuit breaker state
     */
    public function getState(): string
    {
        return Cache::get($this->getCacheKey('state'), 'closed');
    }

    /**
     * Get circuit breaker statistics
     */
    public function getStatistics(): array
    {
        return [
            'state' => $this->getState(),
            'failures' => Cache::get($this->getCacheKey('failures'), 0),
            'half_open_attempts' => Cache::get($this->getCacheKey('half_open_attempts'), 0),
            'half_open_successes' => Cache::get($this->getCacheKey('half_open_successes'), 0),
            'opened_at' => Cache::get($this->getCacheKey('opened_at')),
        ];
    }

    /**
     * Manually reset circuit breaker
     */
    public function reset(): void
    {
        $this->transitionToClosed();
        Log::info('Circuit breaker manually reset');
    }

    /**
     * Transition to CLOSED state
     */
    private function transitionToClosed(): void
    {
        Cache::put($this->getCacheKey('state'), 'closed', now()->addHours(24));
        Cache::forget($this->getCacheKey('failures'));
        Cache::forget($this->getCacheKey('opened_at'));
        Cache::forget($this->getCacheKey('half_open_attempts'));
        Cache::forget($this->getCacheKey('half_open_successes'));
    }

    /**
     * Transition to OPEN state
     */
    private function transitionToOpen(): void
    {
        Cache::put($this->getCacheKey('state'), 'open', now()->addMinutes(5));
        Cache::put($this->getCacheKey('opened_at'), now(), now()->addMinutes(5));
        Cache::forget($this->getCacheKey('half_open_attempts'));
        Cache::forget($this->getCacheKey('half_open_successes'));
    }

    /**
     * Transition to HALF_OPEN state
     */
    private function transitionToHalfOpen(): void
    {
        Cache::put($this->getCacheKey('state'), 'half_open', now()->addMinutes(2));
        Cache::put($this->getCacheKey('half_open_attempts'), 0, now()->addMinutes(2));
        Cache::put($this->getCacheKey('half_open_successes'), 0, now()->addMinutes(2));
        Cache::forget($this->getCacheKey('opened_at'));

        Log::info('Circuit breaker transitioned to half-open: Testing service recovery');
    }

    /**
     * Increment half-open attempts counter
     */
    public function incrementHalfOpenAttempts(): void
    {
        if ($this->getState() === 'half_open') {
            Cache::increment($this->getCacheKey('half_open_attempts'));
        }
    }

    /**
     * Get cache key with prefix
     */
    private function getCacheKey(string $key): string
    {
        return self::CACHE_PREFIX . '.' . $key;
    }
}
