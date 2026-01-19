# Dracin Laravel - Telegram Bot

Laravel-based Telegram bot with dual-mode support for movie distribution and VIP management.

## Quick Start

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure .env
DB_DATABASE=dracin_db
TELE_BOT_TOKEN=your_bot_token
TELE_BOT_USERNAME=@your_bot
TELE_ADMIN_ID=your_telegram_id
TELE_CHANNEL_ID=@your_channel

# Run migrations
php artisan migrate

# Build assets
npm run build

# Start development
# Terminal 1
npm run dev

# Terminal 2
php artisan serve

# Terminal 3 (Bot)
php artisan telegram:polling
```

## Telegram Setup (Ringkas)

**Mode (pilih satu):**
- Polling: localhost/dev, perlu proses background.
- Webhook: production, wajib HTTPS.

**Switch mode:**
```bash
php artisan telegram:mode webhook
php artisan telegram:mode polling
php artisan telegram:webhook:info
```

**Start polling:**
```bash
php artisan telegram:polling
```

**Webhook env (wajib untuk webhook):**
```env
TELEGRAM_WEBHOOK_URL="https://yourdomain.com/api/telegram/webhook"
TELEGRAM_WEBHOOK_SECRET=your_secret_token
```

## Bot Commands

- `/start` - Start bot / Request video
- `/help` - Help message
- `/vip` - VIP subscription
- `/addmovie` - Add movie (Admin only)

## Available Artisan Commands

```bash
telegram:polling                    # Start polling mode
telegram:mode {polling|webhook}     # Switch mode
telegram:webhook:set                # Set webhook
telegram:webhook:remove             # Remove webhook
telegram:webhook:info               # Check webhook status
telegram:clear-state [user_id]      # Clear conversation state
```

## Payment (Tripay)

**Env wajib:**
```env
TRIPAY_API_KEY=
TRIPAY_PRIVATE_KEY=
TRIPAY_MERCHANT_CODE=
TRIPAY_MODE=sandbox|production
```

**Callback URL:**
```
https://yourdomain.com/payment/callback
```

**Catatan penting:**
- Queue worker harus aktif untuk aktivasi VIP & notifikasi.
- Callback Tripay harus bisa diakses publik.

## Environment Variables

```env
# Telegram Bot
TELE_BOT_TOKEN=
TELE_BOT_USERNAME=
TELE_ADMIN_ID=
TELE_CHANNEL_ID=
TELE_BOT_MODE=polling

# Webhook (for webhook mode)
TELEGRAM_WEBHOOK_URL="${APP_URL}/api/telegram/webhook"
TELEGRAM_WEBHOOK_SECRET=

# Payment (Tripay)
TRIPAY_API_KEY=
TRIPAY_PRIVATE_KEY=
TRIPAY_MERCHANT_CODE=
TRIPAY_MODE=sandbox
```

## License

MIT


php artisan data:audit
php artisan data:audit --details


php artisan data:repair-categories
php artisan data:repair-categories --apply

php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=UserSeeder
