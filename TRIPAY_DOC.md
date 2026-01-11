# Tripay Payment (Ringkas + 2 Flow)

## Ringkasan
Project ini punya dua flow pembayaran Tripay:
1) Web-based (landing/checkout).
2) Telegram bot (perintah /vip).

Keduanya menggunakan data payment yang sama dan callback Tripay yang sama.

## Flow 1: Web-based (Landing/Checkout)
1. User pilih paket dari landing page.
2. Checkout form (input Telegram user ID + metode bayar).
3. Sistem create transaksi Tripay.
4. User bayar via QRIS/VA.
5. Callback Tripay masuk ke `/payment/callback`.
6. Status payment diupdate, event `PaymentPaid` dipanggil.
7. VIP aktif otomatis + notifikasi Telegram (queue).

File terkait:
- `app/Http/Controllers/CheckoutController.php`
- `app/Services/TripayService.php`
- `app/Repositories/PaymentRepository.php`
- `app/Listeners/ActivateUserVip.php`
- `routes/web.php`

## Flow 2: Telegram Bot (/vip)
1. User kirim `/vip`, bot tampilkan paket.
2. User pilih paket via inline button.
3. Bot create transaksi Tripay (QRIS).
4. Bot kirim QR image ke user (expired 10 menit).
5. Callback Tripay masuk ke `/payment/callback`.
6. Status payment diupdate, VIP aktif otomatis + notifikasi (queue).

File terkait:
- `app/Telegram/Commands/VipCommand.php`
- `app/Services/TelegramUpdateProcessor.php`
- `app/Services/TripayService.php`
- `app/Listeners/ActivateUserVip.php`
- `routes/web.php`

Catatan:
- Jika user sudah punya payment pending dan belum expired, bot akan mengirim QRIS yang sama (paket harus sama).
- File QRIS dibersihkan setelah dikirim.

## Env Wajib
```env
TRIPAY_API_KEY=
TRIPAY_PRIVATE_KEY=
TRIPAY_MERCHANT_CODE=
TRIPAY_MODE=sandbox|production
```

## Callback URL
```
https://yourdomain.com/payment/callback
```

## Catatan Penting
- Queue worker wajib aktif untuk aktivasi VIP + notifikasi.
- Callback Tripay harus bisa diakses publik.
- Jika Tripay belum dikonfigurasi, UI checkout tetap tampil tapi pembayaran diblok.

## Troubleshooting Singkat
- Callback tidak masuk: cek URL + IP whitelist + signature.
- VIP tidak aktif: cek queue worker dan log listener.
