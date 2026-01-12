<?php

namespace Tests\Unit;

use App\Services\TripayCircuitBreaker;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\TestCase;

class TripayCircuitBreakerTest extends TestCase
{
    private TripayCircuitBreaker $circuitBreaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Cache facade
        if (!class_exists('\Illuminate\Support\Facades\Cache')) {
            $this->markTestSkipped('Laravel Cache not available');
        }

        $this->circuitBreaker = new TripayCircuitBreaker();

        try {
            Cache::flush();
        } catch (\Exception $e) {
            // Ignore if cache not available
        }
    }

    protected function tearDown(): void
    {
        try {
            Cache::flush();
        } catch (\Exception $e) {
            // Ignore if cache not available
        }

        parent::tearDown();
    }

    /** @test */
    public function it_starts_in_closed_state()
    {
        $this->assertEquals('closed', $this->circuitBreaker->getState());
        $this->assertFalse($this->circuitBreaker->isOpen());
    }

    /** @test */
    public function it_transitions_to_open_after_failure_threshold()
    {
        // Record 5 failures (threshold)
        for ($i = 0; $i < 5; $i++) {
            $this->circuitBreaker->recordFailure('Test failure ' . $i);
        }

        $this->assertEquals('open', $this->circuitBreaker->getState());
        $this->assertTrue($this->circuitBreaker->isOpen());
    }

    /** @test */
    public function it_stays_closed_below_failure_threshold()
    {
        // Record 4 failures (below threshold of 5)
        for ($i = 0; $i < 4; $i++) {
            $this->circuitBreaker->recordFailure('Test failure ' . $i);
        }

        $this->assertEquals('closed', $this->circuitBreaker->getState());
        $this->assertFalse($this->circuitBreaker->isOpen());
    }

    /** @test */
    public function it_resets_failure_count_on_success_in_closed_state()
    {
        // Record 3 failures
        for ($i = 0; $i < 3; $i++) {
            $this->circuitBreaker->recordFailure('Test failure');
        }

        $stats = $this->circuitBreaker->getStatistics();
        $this->assertEquals(3, $stats['failures']);

        // Record success
        $this->circuitBreaker->recordSuccess();

        $stats = $this->circuitBreaker->getStatistics();
        $this->assertEquals(0, $stats['failures']);
        $this->assertEquals('closed', $this->circuitBreaker->getState());
    }

    /** @test */
    public function it_transitions_to_half_open_after_timeout()
    {
        // Open the circuit
        for ($i = 0; $i < 5; $i++) {
            $this->circuitBreaker->recordFailure('Test failure');
        }

        $this->assertEquals('open', $this->circuitBreaker->getState());

        // Simulate timeout by manually setting opened_at to 61 seconds ago
        Cache::put('tripay_circuit_breaker.opened_at', now()->subSeconds(61), now()->addMinutes(5));

        // Check if circuit is open - should transition to half-open
        $isOpen = $this->circuitBreaker->isOpen();

        $this->assertFalse($isOpen);
        $this->assertEquals('half_open', $this->circuitBreaker->getState());
    }

    /** @test */
    public function it_transitions_to_closed_after_successful_half_open_tests()
    {
        // Open the circuit
        for ($i = 0; $i < 5; $i++) {
            $this->circuitBreaker->recordFailure('Test failure');
        }

        // Transition to half-open
        Cache::put('tripay_circuit_breaker.state', 'half_open', now()->addMinutes(2));
        Cache::put('tripay_circuit_breaker.half_open_attempts', 0, now()->addMinutes(2));
        Cache::put('tripay_circuit_breaker.half_open_successes', 0, now()->addMinutes(2));
        Cache::forget('tripay_circuit_breaker.opened_at');

        // Record 2 successes (success threshold)
        $this->circuitBreaker->recordSuccess();
        $this->circuitBreaker->recordSuccess();

        $this->assertEquals('closed', $this->circuitBreaker->getState());
    }

    /** @test */
    public function it_transitions_back_to_open_if_half_open_test_fails()
    {
        // Set to half-open state
        Cache::put('tripay_circuit_breaker.state', 'half_open', now()->addMinutes(2));
        Cache::put('tripay_circuit_breaker.half_open_attempts', 0, now()->addMinutes(2));
        Cache::put('tripay_circuit_breaker.half_open_successes', 0, now()->addMinutes(2));

        // Record failure in half-open state
        $this->circuitBreaker->recordFailure('Half-open test failed');

        $this->assertEquals('open', $this->circuitBreaker->getState());
    }

    /** @test */
    public function it_blocks_requests_when_open()
    {
        // Open the circuit
        for ($i = 0; $i < 5; $i++) {
            $this->circuitBreaker->recordFailure('Test failure');
        }

        $this->assertTrue($this->circuitBreaker->isOpen());
    }

    /** @test */
    public function it_limits_half_open_attempts()
    {
        // Set to half-open state
        Cache::put('tripay_circuit_breaker.state', 'half_open', now()->addMinutes(2));
        Cache::put('tripay_circuit_breaker.half_open_attempts', 3, now()->addMinutes(2)); // Max attempts reached

        $this->assertTrue($this->circuitBreaker->isOpen());
    }

    /** @test */
    public function it_can_be_manually_reset()
    {
        // Open the circuit
        for ($i = 0; $i < 5; $i++) {
            $this->circuitBreaker->recordFailure('Test failure');
        }

        $this->assertEquals('open', $this->circuitBreaker->getState());

        // Manual reset
        $this->circuitBreaker->reset();

        $this->assertEquals('closed', $this->circuitBreaker->getState());
        $this->assertFalse($this->circuitBreaker->isOpen());
    }

    /** @test */
    public function it_provides_accurate_statistics()
    {
        // Record some failures
        $this->circuitBreaker->recordFailure('Failure 1');
        $this->circuitBreaker->recordFailure('Failure 2');

        $stats = $this->circuitBreaker->getStatistics();

        $this->assertEquals('closed', $stats['state']);
        $this->assertEquals(2, $stats['failures']);
        $this->assertEquals(0, $stats['half_open_attempts']);
        $this->assertEquals(0, $stats['half_open_successes']);
        $this->assertNull($stats['opened_at']);
    }

    /** @test */
    public function it_increments_half_open_attempts_correctly()
    {
        // Set to half-open state
        Cache::put('tripay_circuit_breaker.state', 'half_open', now()->addMinutes(2));
        Cache::put('tripay_circuit_breaker.half_open_attempts', 0, now()->addMinutes(2));

        $this->circuitBreaker->incrementHalfOpenAttempts();

        $stats = $this->circuitBreaker->getStatistics();
        $this->assertEquals(1, $stats['half_open_attempts']);
    }

    /** @test */
    public function it_does_not_increment_half_open_attempts_when_not_in_half_open_state()
    {
        // Closed state
        $this->assertEquals('closed', $this->circuitBreaker->getState());

        $this->circuitBreaker->incrementHalfOpenAttempts();

        $stats = $this->circuitBreaker->getStatistics();
        $this->assertEquals(0, $stats['half_open_attempts']);
    }
}
