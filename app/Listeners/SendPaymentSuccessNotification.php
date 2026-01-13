<?php

namespace App\Listeners;

use App\Events\PaymentPaid;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class SendPaymentSuccessNotification
{
    public function __construct(
        private readonly TelegramService $telegramService
    ) {}

    public function handle(PaymentPaid $event): void
    {
        try {
            $payment = $event->payment;
            $telegramUser = $payment->telegramUser;

            $message = $this->buildSuccessMessage($payment);

            $this->telegramService->sendMessage(
                $telegramUser->telegram_user_id,
                $message
            );

            Log::info('Payment success notification sent', [
                'payment_id' => $payment->id,
                'telegram_user_id' => $telegramUser->telegram_user_id,
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
        $vipUntil = $payment->telegramUser->vip_until
            ? $payment->telegramUser->vip_until->format('d M Y H:i')
            : 'N/A';

        return "🎉 *Pembayaran Berhasil!*\n\n"
            . "✅ Status VIP Anda sudah aktif!\n\n"
            . "📦 *Detail Pembelian:*\n"
            . "• Paket: {$payment->package}\n"
            . "• Harga: Rp " . number_format($payment->amount, 0, ',', '.') . "\n"
            . "• Berlaku hingga: {$vipUntil}\n\n"
            . "🎬 Sekarang Anda bisa menonton semua film VIP di channel @dracin_hd\n\n"
            . "Terima kasih sudah berlangganan Dracin HD! 🙏";
    }

}
