# Telegram Bot Setup (Ringkas)

## Mode (Pilih Salah Satu)
- Polling: untuk localhost/dev, butuh proses background.
- Webhook: untuk production, wajib HTTPS.

## Env Wajib
```env
TELE_BOT_MODE=polling|webhook
TELEGRAM_WEBHOOK_URL="https://yourdomain.com/api/telegram/webhook"
TELEGRAM_WEBHOOK_SECRET=your_secret_token
```

## Perintah Utama
```bash
# Switch mode
php artisan telegram:mode webhook
php artisan telegram:mode polling

# Status webhook
php artisan telegram:webhook:info

# Start polling
php artisan telegram:polling

# Set/remove webhook
php artisan telegram:webhook:set [url]
php artisan telegram:webhook:remove
```

## Checklist Mode Webhook
1. Set `TELE_BOT_MODE=webhook` dan `TELEGRAM_WEBHOOK_URL`.
2. (Disarankan) set `TELEGRAM_WEBHOOK_SECRET`.
3. Jalankan `php artisan telegram:mode webhook`.
4. Cek `php artisan telegram:webhook:info`.

## Checklist Mode Polling
1. Set `TELE_BOT_MODE=polling`.
2. Jalankan `php artisan telegram:mode polling`.
3. Start `php artisan telegram:polling` (atau via supervisor).

## Monitoring
```bash
php artisan telegram:webhook:info
tail -f storage/logs/laravel.log
```

## Catatan Keamanan
- Jangan commit `.env`.
- Wajib HTTPS untuk webhook.
- Gunakan `TELEGRAM_WEBHOOK_SECRET` agar request webhook tervalidasi.
