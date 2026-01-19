# Prasyarat (sekali)
# 1. Aktifkan PHP extension ZIP di php.ini
#    extension=zip
# 2. Install paket backup
#    composer require spatie/laravel-backup
# 3. Publish config
#    php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

# 1. Backup database terlebih dahulu
php artisan backup:run --only-db

# 2. Audit data integrity
php artisan data:audit --details

# 3. Jalankan migration
php artisan migrate

# 4. Clear cache
php artisan cache:clear
php artisan view:clear
