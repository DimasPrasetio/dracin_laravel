<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryVipPackage;
use App\Models\User;
use App\Models\Payment;
use App\Models\UserCategoryVip;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VipService
{
    private static ?bool $hasCategoryVipPackages = null;

    private function hasCategoryVipPackages(): bool
    {
        if (self::$hasCategoryVipPackages === null) {
            self::$hasCategoryVipPackages = Schema::hasTable('category_vip_packages');
        }

        return self::$hasCategoryVipPackages;
    }
    /**
     * Get VIP packages for a category. Falls back to config when DB is empty.
     */
    public function getPackages(?int $categoryId = null): array
    {
        if ($categoryId && $this->hasCategoryVipPackages()) {
            $records = CategoryVipPackage::active()
                ->where('category_id', $categoryId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($records->isNotEmpty()) {
                return $records->map(function (CategoryVipPackage $package) {
                    return [
                        'package' => $package->code,
                        'duration' => $package->duration_days,
                        'price' => $package->price,
                        'label' => $package->label,
                        'name' => $package->name,
                        'description' => $package->description,
                        'badge' => $package->badge,
                        'popular' => false,
                    ];
                })->values()->all();
            }
        }

        return $this->getFallbackPackages();
    }

    public function getPackageDetails(string $packageCode, ?int $categoryId = null): ?array
    {
        if ($categoryId && $this->hasCategoryVipPackages()) {
            $record = CategoryVipPackage::active()
                ->where('category_id', $categoryId)
                ->where('code', $packageCode)
                ->first();

            if ($record) {
                return [
                    'name' => $record->name,
                    'duration' => $record->duration_days,
                    'price' => $record->price,
                    'description' => $record->description,
                    'badge' => $record->badge,
                    'code' => $record->code,
                ];
            }
        }

        $fallback = config("vip.packages.{$packageCode}");
        if (!$fallback) {
            return null;
        }

        return [
            'name' => $fallback['name'] ?? $packageCode,
            'duration' => (int) ($fallback['duration'] ?? 0),
            'price' => (int) ($fallback['price'] ?? 0),
            'description' => $fallback['description'] ?? null,
            'badge' => $fallback['badge'] ?? null,
            'code' => $packageCode,
        ];
    }

    public function getPackageCodes(?int $categoryId = null): array
    {
        if ($categoryId && $this->hasCategoryVipPackages()) {
            $codes = CategoryVipPackage::active()
                ->where('category_id', $categoryId)
                ->orderBy('sort_order')
                ->pluck('code')
                ->all();

            if (!empty($codes)) {
                return $codes;
            }
        }

        return array_keys(config('vip.packages', []));
    }

    /**
     * Activate VIP for user (legacy method for backward compatibility)
     *
     * @throws \InvalidArgumentException if package is invalid
     * @throws \LogicException if user is already VIP
     */
    public function activateVip(User $user, string $package, ?int $categoryId = null): void
    {
        $packageData = $this->getPackageDetails($package, $categoryId);

        if (!$packageData) {
            throw new \InvalidArgumentException("Invalid package: {$package}");
        }

        $duration = (int) ($packageData['duration'] ?? 0);

        if ($categoryId === null) {
            $categoryId = Category::getDefault()?->id;
        }

        if (!$categoryId) {
            throw new \LogicException('Category is required for VIP activation.');
        }

        $this->activateVipForCategory($user, $categoryId, $duration);
    }

    /**
     * Activate VIP for user for a specific category
     *
     * @throws \LogicException if user is already VIP for this category
     */
    public function activateVipForCategory(User $user, int $categoryId, int $durationDays): void
    {
        DB::transaction(function () use ($user, $categoryId, $durationDays) {
            $subscription = UserCategoryVip::where('user_id', $user->id)
                ->where('category_id', $categoryId)
                ->lockForUpdate()
                ->first();

            if ($subscription && $subscription->isActive()) {
                throw new \LogicException(
                    'Cannot activate VIP for user who is already VIP for this category. ' .
                    'Current VIP expires at: ' . ($subscription->vip_until?->format('Y-m-d H:i:s') ?? 'N/A')
                );
            }

            $newVipUntil = Carbon::now()->addDays($durationDays);

            if ($subscription) {
                $subscription->update(['vip_until' => $newVipUntil]);
                return;
            }

            UserCategoryVip::create([
                'user_id' => $user->id,
                'category_id' => $categoryId,
                'vip_until' => $newVipUntil,
            ]);
        });
    }

    /**
     * Check if user has VIP access for a specific category
     */
    public function hasVipAccessForCategory(User $user, int $categoryId): bool
    {
        return $user->isVipForCategory($categoryId);
    }

    /**
     * Create payment record
     */
    public function createPayment(User $user, string $package): Payment
    {
        $packageData = $this->getPackageDetails($package);

        if (!$packageData) {
            throw new \InvalidArgumentException("Invalid package: {$package}");
        }

        $amount = (int) ($packageData['price'] ?? 0);

        return Payment::create([
            'user_id' => $user->id,
            'package' => $package,
            'package_name' => $packageData['name'] ?? $package,
            'package_duration_days' => (int) ($packageData['duration'] ?? 0),
            'package_price' => $amount,
            'amount' => $amount,
            'status' => 'pending',
        ]);
    }

    /**
     * Check if user has VIP access
     */
    public function hasVipAccess(User $user): bool
    {
        return $user->vipSubscriptions()->active()->exists();
    }

    /**
     * Get VIP info message
     */
    public function getVipInfoMessage(): string
    {
        $message = "<b>Paket VIP Dracin</b>\n\n";

        foreach ($this->getPackages() as $index => $package) {
            $no = $index + 1;
            $message .= "{$no}. {$package['label']}\n";
        }

        $message .= "\n<b>Pembayaran</b>\n";
        $message .= "Sistem pembayaran sedang dalam pengembangan.\n";
        $message .= "Untuk info lebih lanjut hubungi admin.\n";

        return $message;
    }

    private function getFallbackPackages(): array
    {
        $packages = config('vip.packages', []);
        $result = [];

        foreach ($packages as $key => $package) {
            $result[] = [
                'package' => $key,
                'duration' => (int) ($package['duration'] ?? 0),
                'price' => (int) ($package['price'] ?? 0),
                'label' => ($package['name'] ?? $key) . ' - Rp ' . number_format((int) ($package['price'] ?? 0), 0, ',', '.'),
                'name' => $package['name'] ?? $key,
                'description' => $package['description'] ?? null,
                'badge' => $package['badge'] ?? null,
                'popular' => (bool) ($package['popular'] ?? false),
            ];
        }

        return $result;
    }
}
