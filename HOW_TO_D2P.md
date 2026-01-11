# Deploy ke VPS Hostinger (Ubuntu 24.04 LTS) - D2P

Target project: `https://github.com/DimasPrasetio/dracin_laravel`

Spesifikasi VPS:
- 2 vCPU Core
- 8 GB RAM
- 100 GB Disk Space NVMe
- 8 TB Bandwidth
- 1 Snapshot
- Backup Mingguan
- Alamat IP Dedicated
- Full Root Access
- AI Assistant
- Pendeteksi Malware

Catatan:
- Gunakan mode **webhook** untuk Telegram bot.
- Queue worker wajib aktif untuk VIP/Tripay.
- Document root harus ke folder `public`.

## 1) Arahkan DNS ke IP VPS
Set A record:
```
@   -> IP VPS
www -> IP VPS
```

## 2) Login ke VPS dan update system
```bash
ssh root@IP_VPS
apt update && apt upgrade -y
```

## 3) Install paket dasar
```bash
apt install -y nginx git unzip curl software-properties-common
```

## 4) Install PHP 8.2 + extensions
```bash
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd
```

## 5) Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

## 6) Install Node.js (untuk build assets)
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

## 7) Install & setup MySQL
```bash
apt install -y mysql-server
mysql
```
Di prompt MySQL:
```sql
CREATE DATABASE dracin_db;
CREATE USER 'dracin_user'@'localhost' IDENTIFIED BY 'password_kuat';
GRANT ALL PRIVILEGES ON dracin_db.* TO 'dracin_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 8) Clone project dari GitHub
```bash
mkdir -p /var/www
cd /var/www
git clone https://github.com/DimasPrasetio/dracin_laravel.git
cd dracin_laravel
```

## 9) Install dependencies & build assets
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

## 10) Konfigurasi .env
```bash
cp .env.example .env
php artisan key:generate
```
Edit `.env` (contoh minimal production):
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=dracin_db
DB_USERNAME=dracin_user
DB_PASSWORD=password_kuat

TELE_BOT_MODE=webhook
TELEGRAM_WEBHOOK_URL="${APP_URL}/api/telegram/webhook"
TELEGRAM_WEBHOOK_SECRET=isi_token_rahasia

TRIPAY_API_KEY=...
TRIPAY_PRIVATE_KEY=...
TRIPAY_MERCHANT_CODE=...
TRIPAY_MODE=production
```

## 11) Migrate & cache
```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 12) Permission storage
```bash
chown -R www-data:www-data /var/www/dracin_laravel
chmod -R 775 storage bootstrap/cache
```

## 13) Setup Nginx
Buat file config:
```bash
nano /etc/nginx/sites-available/dracin_laravel
```
Isi minimal:
```nginx
server {
    listen 80;
    server_name domain-anda.com www.domain-anda.com;
    root /var/www/dracin_laravel/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```
Aktifkan:
```bash
ln -s /etc/nginx/sites-available/dracin_laravel /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

## 14) SSL (HTTPS)
```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d domain-anda.com -d www.domain-anda.com
```

## 15) Queue Worker (wajib)
Install Supervisor:
```bash
apt install -y supervisor
```
Buat config:
```bash
nano /etc/supervisor/conf.d/dracin_queue.conf
```
Isi:
```ini
[program:dracin_queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/dracin_laravel/artisan queue:work --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/dracin_laravel/storage/logs/queue.log
```
Aktifkan:
```bash
supervisorctl reread
supervisorctl update
supervisorctl status
```

## 16) Set Telegram Webhook
```bash
cd /var/www/dracin_laravel
php artisan telegram:mode webhook
php artisan telegram:webhook:info
```

## 17) Test cepat
1. Akses landing page.
2. Tes webhook Telegram:
   - `https://domain-anda.com/api/telegram/webhook/health`
3. Cek log:
   - `storage/logs/laravel.log`
   - `storage/logs/queue.log`
