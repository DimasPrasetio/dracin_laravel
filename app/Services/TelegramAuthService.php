<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramAuthService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const CACHE_KEY_PREFIX = 'telegram_auth:';

    /**
     * Check if telegram user is admin
     */
    public function isAdmin(int|string $telegramUserId): bool
    {
        return Cache::remember(
            $this->getCacheKey($telegramUserId, 'is_admin'),
            self::CACHE_TTL,
            fn() => $this->checkAdminFromDatabase($telegramUserId)
        );
    }

    /**
     * Check if telegram user can add movies (admin or moderator)
     */
    public function canAddMovies(int|string $telegramUserId): bool
    {
        return Cache::remember(
            $this->getCacheKey($telegramUserId, 'can_add_movies'),
            self::CACHE_TTL,
            fn() => $this->checkCanAddMoviesFromDatabase($telegramUserId)
        );
    }

    /**
     * Check if telegram user can add movies for a specific category
     */
    public function canAddMoviesForCategory(int|string $telegramUserId, int $categoryId): bool
    {
        return Cache::remember(
            $this->getCacheKey($telegramUserId, "can_add_movies_category:{$categoryId}"),
            self::CACHE_TTL,
            fn() => $this->checkCanAddMoviesForCategoryFromDatabase($telegramUserId, $categoryId)
        );
    }

    /**
     * Check if telegram user is admin for a specific category
     */
    public function isCategoryAdmin(int|string $telegramUserId, int $categoryId): bool
    {
        return Cache::remember(
            $this->getCacheKey($telegramUserId, "is_category_admin:{$categoryId}"),
            self::CACHE_TTL,
            fn() => $this->checkIsCategoryAdminFromDatabase($telegramUserId, $categoryId)
        );
    }

    /**
     * Check if telegram user can edit movies (admin only)
     */
    public function canEditMovies(int|string $telegramUserId): bool
    {
        return Cache::remember(
            $this->getCacheKey($telegramUserId, 'can_edit_movies'),
            self::CACHE_TTL,
            fn() => $this->checkCanEditMoviesFromDatabase($telegramUserId)
        );
    }

    /**
     * Check if telegram user can delete movies (admin only)
     */
    public function canDeleteMovies(int|string $telegramUserId): bool
    {
        return Cache::remember(
            $this->getCacheKey($telegramUserId, 'can_delete_movies'),
            self::CACHE_TTL,
            fn() => $this->checkCanDeleteMoviesFromDatabase($telegramUserId)
        );
    }

    /**
     * Check if telegram user can manage VIP (admin only)
     */
    public function canManageVip(int|string $telegramUserId): bool
    {
        return Cache::remember(
            $this->getCacheKey($telegramUserId, 'can_manage_vip'),
            self::CACHE_TTL,
            fn() => $this->checkCanManageVipFromDatabase($telegramUserId)
        );
    }

    /**
     * Check if telegram user can manage users (admin only)
     */
    public function canManageUsers(int|string $telegramUserId): bool
    {
        return Cache::remember(
            $this->getCacheKey($telegramUserId, 'can_manage_users'),
            self::CACHE_TTL,
            fn() => $this->checkCanManageUsersFromDatabase($telegramUserId)
        );
    }

    /**
     * Check if telegram user can manage payments (admin only)
     */
    public function canManagePayments(int|string $telegramUserId): bool
    {
        return Cache::remember(
            $this->getCacheKey($telegramUserId, 'can_manage_payments'),
            self::CACHE_TTL,
            fn() => $this->checkCanManagePaymentsFromDatabase($telegramUserId)
        );
    }

    /**
     * Legacy method for backward compatibility
     * @deprecated Use canAddMovies() instead
     */
    public function canManageMovies(int|string $telegramUserId): bool
    {
        return $this->canAddMovies($telegramUserId);
    }

    /**
     * Check if telegram user has specific permission
     */
    public function hasPermission(int|string $telegramUserId, string $permission): bool
    {
        return Cache::remember(
            $this->getCacheKey($telegramUserId, "permission:$permission"),
            self::CACHE_TTL,
            fn() => $this->checkPermissionFromDatabase($telegramUserId, $permission)
        );
    }

    /**
     * Get telegram user with eager loaded relations
     */
    public function getTelegramUser(int|string $telegramUserId): ?User
    {
        return User::where('telegram_id', $telegramUserId)->first();
    }

    /**
     * Get all admin telegram user IDs
     */
    public function getAdminIds(): array
    {
        return Cache::remember(
            'telegram_auth:admin_ids',
            self::CACHE_TTL,
            function () {
                return User::admins()
                    ->whereNotNull('telegram_id')
                    ->pluck('telegram_id')
                    ->toArray();
            }
        );
    }

    /**
     * Clear cache for specific user
     */
    public function clearUserCache(int|string $telegramUserId): void
    {
        // Clear role-based caches
        Cache::forget($this->getCacheKey($telegramUserId, 'is_admin'));

        // Clear granular permission caches
        Cache::forget($this->getCacheKey($telegramUserId, 'can_add_movies'));
        Cache::forget($this->getCacheKey($telegramUserId, 'can_edit_movies'));
        Cache::forget($this->getCacheKey($telegramUserId, 'can_delete_movies'));
        Cache::forget($this->getCacheKey($telegramUserId, 'can_manage_vip'));
        Cache::forget($this->getCacheKey($telegramUserId, 'can_manage_users'));
        Cache::forget($this->getCacheKey($telegramUserId, 'can_manage_payments'));

        // Clear legacy cache
        Cache::forget($this->getCacheKey($telegramUserId, 'can_manage_movies'));

        // Clear global admin list cache
        Cache::forget('telegram_auth:admin_ids');

        Log::info('Telegram user cache cleared', [
            'telegram_user_id' => $telegramUserId,
        ]);
    }

    /**
     * Clear all auth cache
     */
    public function clearAllCache(): void
    {
        $store = Cache::getStore();

        if (method_exists($store, 'supportsTags') && $store->supportsTags()) {
            Cache::tags(['telegram_auth'])->flush();
        } else {
            Cache::flush();
        }

        Log::info('All telegram auth cache cleared');
    }

    /**
     * Check admin status from database
     */
    private function checkAdminFromDatabase(int|string $telegramUserId): bool
    {
        $user = $this->getTelegramUser($telegramUserId);

        if (!$user) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Check can add movies from database
     */
    private function checkCanAddMoviesFromDatabase(int|string $telegramUserId): bool
    {
        $user = $this->getTelegramUser($telegramUserId);

        if (!$user) {
            return false;
        }

        return $user->isStaff();
    }

    /**
     * Check can edit movies from database
     */
    private function checkCanEditMoviesFromDatabase(int|string $telegramUserId): bool
    {
        $user = $this->getTelegramUser($telegramUserId);

        if (!$user) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Check can delete movies from database
     */
    private function checkCanDeleteMoviesFromDatabase(int|string $telegramUserId): bool
    {
        $user = $this->getTelegramUser($telegramUserId);

        if (!$user) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Check can manage VIP from database
     */
    private function checkCanManageVipFromDatabase(int|string $telegramUserId): bool
    {
        $user = $this->getTelegramUser($telegramUserId);

        if (!$user) {
            return false;
        }

        return $user->isAdmin();
    }

    /**
     * Check can manage users from database
     */
    private function checkCanManageUsersFromDatabase(int|string $telegramUserId): bool
    {
        $user = $this->getTelegramUser($telegramUserId);

        if (!$user) {
            return false;
        }

        return $user->canManageUsers();
    }

    /**
     * Check can manage payments from database
     */
    private function checkCanManagePaymentsFromDatabase(int|string $telegramUserId): bool
    {
        $user = $this->getTelegramUser($telegramUserId);

        if (!$user) {
            return false;
        }

        return $user->canManagePayments();
    }

    /**
     * Check permission from database
     */
    private function checkPermissionFromDatabase(int|string $telegramUserId, string $permission): bool
    {
        $user = $this->getTelegramUser($telegramUserId);

        if (!$user) {
            return false;
        }

        return match ($permission) {
            'add_movies' => $user->isStaff(),
            'edit_movies' => $user->isAdmin(),
            'delete_movies' => $user->isAdmin(),
            'manage_vip' => $user->isAdmin(),
            'manage_users' => $user->canManageUsers(),
            'manage_payments' => $user->canManagePayments(),
            'view_analytics' => $user->canViewAnalytics(),
            // Legacy support
            'manage_movies' => $user->isStaff(),
            default => false,
        };
    }

    /**
     * Check if telegram user can add movies for specific category from database
     */
    private function checkCanAddMoviesForCategoryFromDatabase(int|string $telegramUserId, int $categoryId): bool
    {
        $user = $this->getTelegramUser($telegramUserId);

        if (!$user) {
            return false;
        }

        return $user->canAddMoviesForCategory($categoryId);
    }

    /**
     * Check if telegram user is admin for specific category from database
     */
    private function checkIsCategoryAdminFromDatabase(int|string $telegramUserId, int $categoryId): bool
    {
        $user = $this->getTelegramUser($telegramUserId);

        if (!$user) {
            return false;
        }

        return $user->isAdminForCategory($categoryId);
    }

    /**
     * Generate cache key
     */
    private function getCacheKey(int|string $telegramUserId, string $suffix): string
    {
        return self::CACHE_KEY_PREFIX . $telegramUserId . ':' . $suffix;
    }

    /**
     * Promote telegram user to admin
     */
    public function promoteToAdmin(User $user): bool
    {
        $result = $user->promoteToAdmin();

        if ($result) {
            $this->clearUserCache($user->telegram_id ?? $user->id);

            Log::info('Telegram user promoted to admin', [
                'telegram_id' => $user->telegram_id,
                'username' => $user->username,
            ]);
        }

        return $result;
    }

    /**
     * Demote telegram user to regular user
     */
    public function demoteToUser(User $user): bool
    {
        $result = $user->demoteToUser();

        if ($result) {
            $this->clearUserCache($user->telegram_id ?? $user->id);

            Log::info('Telegram user demoted to user', [
                'telegram_id' => $user->telegram_id,
                'username' => $user->username,
            ]);
        }

        return $result;
    }

    /**
     * Toggle admin status
     */
    public function toggleAdmin(User $user): bool
    {
        if ($user->isAdmin()) {
            return $this->demoteToUser($user);
        }

        return $this->promoteToAdmin($user);
    }
}
