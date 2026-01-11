# Deploy ke Shared Hosting (Hostinger) - Rekomendasi Ideal

Di hosting **tidak** menjalankan `npm run dev` atau `php artisan serve`.  
Gunakan **build static assets**, **web server bawaan hosting**, dan **Telegram webhook**.

## 1) Build Assets di Local (wajib)
```bash
npm install
npm run build
```
Upload hasil build di folder `public/build`.

## 2) Upload Project
1. Buat folder project di luar `public_html`, contoh:
   - `/home/u123456789/dracin_laravel`
2. Upload semua file project ke folder tersebut (termasuk `vendor` jika tidak pakai Composer di server).
3. Hanya folder `public` yang boleh jadi Document Root.

## 3) Set Document Root ke /public
Di hPanel → **Website** → **Dashboard** → **Advanced** → **Change Document Root**:
```
/home/u123456789/dracin_laravel/public
```

## 4) Konfigurasi .env (production)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=nama_db
DB_USERNAME=user_db
DB_PASSWORD=password_db

TELE_BOT_MODE=webhook
TELEGRAM_WEBHOOK_URL="https://domain-anda.com/api/telegram/webhook"
TELEGRAM_WEBHOOK_SECRET=your_secret_token

TRIPAY_API_KEY=...
TRIPAY_PRIVATE_KEY=...
TRIPAY_MERCHANT_CODE=...
TRIPAY_MODE=production
```

## 5) Install & Cache (via Terminal)
```bash
cd /home/u123456789/dracin_laravel
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 6) Set Telegram Webhook (wajib)
```bash
php artisan telegram:mode webhook
php artisan telegram:webhook:info
```

## 7) Queue Worker (wajib)
Shared hosting tidak bisa daemon, gunakan cron:
```
* * * * * /usr/bin/php /home/u123456789/dracin_laravel/artisan queue:work --stop-when-empty >> /home/u123456789/dracin_laravel/storage/logs/queue.log 2>&1
```

## 8) Permission Folder
Pastikan writable:
```
storage/
bootstrap/cache/
```

## 9) Callback Tripay
Set di Tripay Dashboard:
```
https://domain-anda.com/payment/callback
```

## 10) Test Cepat
1. Akses landing page.
2. Coba checkout + payment.
3. Cek log `storage/logs/laravel.log`.
4. Tes webhook Telegram:
   - `https://domain-anda.com/api/telegram/webhook/health`

## Catatan Penting
- Di hosting: **tidak pakai `php artisan serve`**.
- Di hosting: **tidak pakai `npm run dev`**, hanya `npm run build`.
- Telegram bot di hosting: **wajib webhook**, bukan polling.
