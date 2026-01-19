<?php

namespace App\Listeners;

use App\Events\PaymentPaid;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendPaymentSuccessNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        private readonly TelegramService $telegramService
    ) {}

    public function handle(PaymentPaid $event): void
    {
        try {
            $payment = $event->payment;
            $user = $payment->user;

            if (!$user || !$user->telegram_id) {
                Log::warning('Skipping payment success notification: user missing', [
                    'payment_id' => $payment->id,
                    'category_id' => $payment->category_id,
                ]);
                return;
            }

            $message = $this->buildSuccessMessage($payment);

            // Set category context if payment has category
            if ($payment->category) {
                $this->telegramService->setCategory($payment->category);
            }

            $this->telegramService->sendMessage(
                (int) $user->telegram_id,
                $message
            );

            Log::info('Payment success notification sent', [
                'payment_id' => $payment->id,
                'telegram_id' => $user->telegram_id,
                'category_id' => $payment->category_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send payment success notification', [
                'payment_id' => $event->payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function buildSuccessMessage($payment): string
    {
        $user = $payment->user;
        $category = $payment->category;
        $packageName = $payment->package_name ?? $payment->package;
        $packagePrice = $payment->package_price ?? $payment->amount;

        // Get VIP expiry based on category or global
        if ($category) {
            $vipData = $user->vipSubscriptions()
                ->where('category_id', $category->id)
                ->first();
            $vipUntil = $vipData?->vip_until?->format('d M Y H:i') ?? 'N/A';
            $categoryInfo = "\n- Kategori: {$category->name}";
            $channelInfo = $category->channel_id
                ? "Sekarang Anda bisa menonton semua film VIP di channel {$category->channel_id}"
                : "Sekarang Anda bisa menonton semua film VIP kategori {$category->name}";
        } else {
            $vipUntil = 'N/A';
            $categoryInfo = "";
            $channelInfo = "Sekarang Anda bisa menonton semua film VIP";
        }

        return "<b>Pembayaran Berhasil!</b>\n\n"
            . "Status VIP Anda sudah aktif.\n\n"
            . "<b>Detail Pembelian:</b>\n"
            . "- Paket: {$packageName}\n"
            . "- Harga: Rp " . number_format($packagePrice, 0, ',', '.') . "\n"
            . "- Berlaku hingga: {$vipUntil}"
            . $categoryInfo . "\n\n"
            . $channelInfo . "\n\n"
            . "Terima kasih sudah berlangganan.";
    }
}



