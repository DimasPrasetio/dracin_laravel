# 1. Backup database terlebih dahulu
php artisan backup:run --only-db

# 2. Audit data integrity
php artisan data:audit --details

# 3. Jalankan migration
php artisan migrate

# 4. Clear cache
php artisan cache:clear
php artisan view:clear
