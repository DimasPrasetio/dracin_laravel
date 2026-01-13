<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('payment-create', function (Request $request) {
            $config = config('vip.rate_limits.payment_create', []);
            $decay = (int) ($config['decay_minutes'] ?? 1);
            $max = (int) ($config['max_attempts'] ?? 5);

            $key = (string) ($request->input('telegram_user_id') ?? $request->ip());

            return Limit::perMinutes($decay, $max)->by($key);
        });

        RateLimiter::for('payment-status', function (Request $request) {
            $config = config('vip.rate_limits.payment_status', []);
            $decay = (int) ($config['decay_minutes'] ?? 1);
            $max = (int) ($config['max_attempts'] ?? 30);
            $reference = (string) $request->route('reference');

            return Limit::perMinutes($decay, $max)->by($request->ip() . '|' . $reference);
        });

        RateLimiter::for('bot-webhook', function (Request $request) {
            $config = config('vip.rate_limits.bot_webhook', []);
            $decay = (int) ($config['decay_minutes'] ?? 1);
            $max = (int) ($config['max_attempts'] ?? 90);

            $userId = $request->input('message.from.id')
                ?? $request->input('callback_query.from.id')
                ?? $request->input('edited_message.from.id');

            $key = $userId ? 'bot-user:' . $userId : 'bot-ip:' . $request->ip();

            return Limit::perMinutes($decay, $max)->by($key);
        });
    }
}
