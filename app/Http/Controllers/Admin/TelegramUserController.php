<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Models\UserCategoryVip;
use Illuminate\Http\Request;

class TelegramUserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $categories = $user ? $user->getAccessibleCategories() : collect();
        $selectedCategoryId = (int) (request()->input('category_id') ?? $categories->first()?->id ?? 0);

        return view('telegram_users.index', compact('categories', 'selectedCategoryId'));
    }

    public function data(Request $request)
    {
        $user = $request->user();
        $categories = $user ? $user->getAccessibleCategories() : collect();
        $accessibleCategoryIds = $categories->pluck('id');
        $selectedCategoryId = $request->input('category_id');

        if ($user && !$user->isSuperAdmin()) {
            if (!$selectedCategoryId) {
                return response()->json([
                    'message' => 'Kategori wajib dipilih.',
                ], 422);
            }

            if (!$accessibleCategoryIds->contains((int) $selectedCategoryId)) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke kategori ini.',
                ], 403);
            }
        } elseif ($selectedCategoryId && $selectedCategoryId !== 'all') {
            if (!Category::whereKey($selectedCategoryId)->exists()) {
                return response()->json([
                    'message' => 'Kategori tidak ditemukan.',
                ], 422);
            }
        }

        $categoryId = $selectedCategoryId
            ? (int) $selectedCategoryId
            : (Category::getDefault()?->id ?? 0);

        $query = User::telegramUsers()->with('vipSubscriptions');

        // Search
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('telegram_id', 'like', "%{$search}%");
            });
        }

        // Sort by VIP status for default category (VIP users first)
        $query->orderByDesc('created_at');

        $total = $query->count();

        // Pagination
        $perPage = $request->per_page ?? 10;
        $page = $request->page ?? 1;

        $users = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($user) use ($categoryId) {
                $vipUntil = null;
                if ($categoryId) {
                    $vipUntil = $user->vipSubscriptions()
                        ->where('category_id', $categoryId)
                        ->value('vip_until');
                }

                $user->vip_until = $vipUntil;
                return $user;
            });

        return response()->json([
            'data' => $users,
            'total' => $total,
            'current_page' => (int) $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => (int) $perPage,
            'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
            'to' => min($page * $perPage, $total),
        ]);
    }

    public function updateVip(Request $request, User $user)
    {
        $request->validate([
            'vip_days' => 'required|integer|min:1',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        try {
            $vipDays = (int) $request->vip_days;
            $categoryId = (int) $request->category_id;

            if (!$categoryId) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Default category not found.',
                ], 422);
            }

            $actor = $request->user();
            if ($actor && !$actor->isSuperAdmin() && !$actor->isAdminForCategory($categoryId)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Anda tidak memiliki akses ke kategori ini.',
                ], 403);
            }

            $subscription = UserCategoryVip::firstOrCreate(
                ['user_id' => $user->id, 'category_id' => $categoryId],
                ['vip_until' => now()->subMinute()]
            );

            $vipUntil = $subscription->vip_until && $subscription->vip_until->isFuture()
                ? $subscription->vip_until->copy()->addDays($vipDays)
                : now()->addDays($vipDays);

            $subscription->update(['vip_until' => $vipUntil]);

            return response()->json([
                'ok' => true,
                'message' => 'VIP status updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to update VIP status',
            ], 500);
        }
    }
}
