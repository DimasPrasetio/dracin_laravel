<?php

namespace App\Providers;

use App\Events\PaymentCreated;
use App\Events\PaymentExpired;
use App\Events\PaymentFailed;
use App\Events\PaymentPaid;
use App\Listeners\ActivateUserVip;
use App\Listeners\LogPaymentActivity;
use App\Listeners\SendPaymentSuccessNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        PaymentCreated::class => [
            LogPaymentActivity::class . '@handleCreated',
        ],
        PaymentPaid::class => [
            LogPaymentActivity::class . '@handlePaid',
            ActivateUserVip::class,
            SendPaymentSuccessNotification::class,
        ],
        PaymentExpired::class => [
            LogPaymentActivity::class . '@handleExpired',
        ],
        PaymentFailed::class => [
            LogPaymentActivity::class . '@handleFailed',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
