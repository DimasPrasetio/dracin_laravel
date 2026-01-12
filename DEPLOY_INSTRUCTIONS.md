# Instruksi Deploy Perbaikan Callback Tripay

## File yang Sudah Diubah:
1. ✅ `routes/web.php` - Menambahkan route callback Tripay (TANPA prefix /api)
2. ✅ `routes/api.php` - Menghapus route callback (agar tidak ada duplikasi)
3. ✅ `config/tripay.php` - Update callback URL ke `/payment/callback` (TANPA /api)
4. ✅ `bootstrap/app.php` - CSRF exception sudah benar untuk `/payment/callback`

## Langkah Deploy ke Server VPS:

### 1. Upload File ke Server
Upload 3 file yang sudah diubah ke server VPS:
```bash
# Di server VPS, masuk ke directory project:
cd /var/www/dracin_laravel

# Pull perubahan dari git (jika menggunakan git):
git pull origin main

# Atau upload manual file:
# - routes/web.php (route callback ditambahkan di baris 20-23)
# - routes/api.php (route callback dihapus)
# - config/tripay.php (callback_url sekarang: /payment/callback)
```

### 2. Clear Cache di Server
```bash
cd /var/www/dracin_laravel

# Clear semua cache
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Cache kembali untuk performa
php artisan config:cache
php artisan route:cache
```

### 3. Restart PHP-FPM
```bash
sudo systemctl restart php8.2-fpm
```

### 4. Test Callback URL
```bash
curl -X POST https://dracinhd.store/payment/callback \
  -H "Content-Type: application/json" \
  -d '{"reference":null,"merchant_ref":null,"note":"Test Callback"}' -i
```

**Response yang diharapkan:**
```
HTTP/1.1 200 OK
{"success":true,"message":"Test callback received successfully"}
```

**BUKAN:**
```
HTTP/1.1 404 Not Found  ❌
HTTP/1.1 302 Found      ❌
```

### 5. Verifikasi Log Laravel
```bash
tail -f /var/www/dracin_laravel/storage/logs/laravel.log
```

Harus muncul:
```
[timestamp] local.INFO: Tripay callback received
[timestamp] local.WARNING: Tripay callback verification skipped (local environment)
```

### 6. Update Callback URL di Tripay Dashboard

**PENTING!** Login ke Dashboard Tripay dan update:

1. **URL Callback Baru:**
   ```
   https://dracinhd.store/payment/callback
   ```

   **CATATAN:** URL ini TANPA `/api` karena route sekarang ada di `web.php`, bukan di `api.php`.

2. **Whitelist IP Server:**
   ```
   182.10.130.63
   ```

### 7. Update Environment ke Production (Opsional tapi Direkomendasikan)

Edit file `.env` di server:
```env
APP_ENV=production
APP_DEBUG=false
```

Kemudian:
```bash
php artisan config:cache
```

### 8. Test Callback dari Tripay Dashboard

Gunakan fitur "Test Callback" di dashboard Tripay.

**Indikator Berhasil:**
- ✅ Status "Success" di dashboard Tripay
- ✅ Log "Tripay callback received" muncul di Laravel log
- ✅ Tidak ada redirect 302
- ✅ Response 200 OK

---

## Troubleshooting

### Jika masih 404:
```bash
# Pastikan file sudah benar di server
ls -la /var/www/dracin_laravel/routes/api.php

# Periksa isi file
cat /var/www/dracin_laravel/routes/api.php | grep "payment/callback"

# Clear cache lagi
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Jika masih redirect:
```bash
# Cek apakah route callback masih di web.php
grep -n "payment/callback" /var/www/dracin_laravel/routes/web.php

# Harus terlihat di-comment (// di depannya)
```

### Jika masih "Unauthorized IP":
- Login Tripay Dashboard
- Setting → Whitelist IP
- Tambahkan: **182.10.130.63**

---

## Verifikasi Final

Setelah semua langkah selesai:

1. ✅ Route callback ada di `/payment/callback` (di web.php, TANPA /api prefix)
2. ✅ Config callback URL: `https://dracinhd.store/payment/callback`
3. ✅ CSRF exception sudah dikonfigurasi untuk `/payment/callback`
4. ✅ IP server di-whitelist di Tripay
5. ✅ Callback URL di-update di Tripay Dashboard
6. ✅ Test callback berhasil (200 OK)
7. ✅ Log "Tripay callback received" muncul

**Callback Tripay sekarang sudah berfungsi!** 🎉
