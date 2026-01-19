<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryAdmin;
use App\Models\User;
use App\Models\UserCategoryVip;
use App\Models\ViewLog;
use App\Services\TelegramAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;

class BotAdminController extends Controller
{
    public function __construct(
        private readonly TelegramAuthService $authService
    ) {}

    /**
     * Display bot admins management page
     */
    public function index()
    {
        $user = auth()->user();
        $categories = $user ? $user->getAccessibleCategories() : collect();
        $selectedCategoryId = (int) (request()->input('category_id') ?? $categories->first()?->id ?? 0);

        return view('admin.bot-admins.index', compact('categories', 'selectedCategoryId'));
    }

    /**
     * Get bot admins data for DataTables
     */
    public function data(Request $request)
    {
        $query = $request->get('q');
        $perPage = (int) ($request->get('per_page', 10));
        $category = $this->resolveCategory($request);
        $categoryId = $category->id;

        $telegramUsers = User::telegramUsers()
            ->leftJoin('category_admins as ca', function ($join) use ($categoryId) {
                $join->on('users.id', '=', 'ca.user_id')
                    ->where('ca.category_id', '=', $categoryId);
            })
            ->select([
                'users.*',
                'ca.role as category_role',
                'ca.id as category_admin_id',
            ])
            ->when($query, function ($qr) use ($query) {
                $qr->where(function ($qq) use ($query) {
                    $qq->where('telegram_id', 'like', "%{$query}%")
                       ->orWhere('username', 'like', "%{$query}%")
                       ->orWhere('first_name', 'like', "%{$query}%")
                       ->orWhere('last_name', 'like', "%{$query}%");
                });
            })
            ->orderByRaw("FIELD(category_role, 'admin', 'moderator')")
            ->orderByDesc('id')
            ->paginate($perPage)
            ->through(function ($user) use ($categoryId) {
                $categoryRole = $user->category_role ?? User::ROLE_USER;
                $vipSubscription = $user->vipSubscriptions()
                    ->active()
                    ->where('category_id', $categoryId)
                    ->orderByDesc('vip_until')
                    ->first();

                return [
                    'id' => $user->id,
                    'telegram_user_id' => $user->telegram_id,
                    'username' => $user->username,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->display_name,
                    'email' => $user->email,
                    'role' => $categoryRole,
                    'role_display' => ucfirst($categoryRole),
                    'is_vip' => (bool) $vipSubscription,
                    'vip_until' => $vipSubscription?->vip_until?->format('d M Y H:i'),
                    'created_at' => $user->created_at?->format('d M Y H:i'),
                ];
            });

        return response()->json($telegramUsers);
    }

    /**
     * Toggle admin status for user
     */
    public function toggleAdmin(User $user)
    {
        try {
            $category = $this->resolveCategory(request());
            $oldRole = $user->getRoleForCategory($category->id) ?? User::ROLE_USER;
            $newRole = $oldRole === CategoryAdmin::ROLE_ADMIN ? CategoryAdmin::ROLE_MODERATOR : CategoryAdmin::ROLE_ADMIN;

            $assignment = CategoryAdmin::firstOrNew([
                'category_id' => $category->id,
                'user_id' => $user->id,
            ]);

            $assignment->role = $newRole;
            $assignment->save();

            Log::info('Bot admin status toggled via dashboard', [
                'telegram_id' => $user->telegram_id,
                'old_role' => $oldRole,
                'new_role' => $newRole,
                'category_id' => $category->id,
                'changed_by' => $this->actorEmail(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "User berhasil diubah menjadi " . ucfirst($newRole),
                'role' => $newRole,
                'role_display' => ucfirst($newRole),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to toggle bot admin status', [
                'telegram_id' => $user->telegram_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status admin: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set role for user
     */
    public function setRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin,moderator',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        try {
            $category = $this->resolveCategory($request);
            $oldRole = $user->getRoleForCategory($category->id) ?? User::ROLE_USER;
            $newRole = $request->role;

            if ($newRole === User::ROLE_USER) {
                CategoryAdmin::where('category_id', $category->id)
                    ->where('user_id', $user->id)
                    ->delete();
            } else {
                CategoryAdmin::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'role' => $newRole,
                    ]
                );
            }

            $this->authService->clearUserCache($user->telegram_id ?? $user->id);

            Log::info('Bot user role changed via dashboard', [
                'telegram_id' => $user->telegram_id,
                'old_role' => $oldRole,
                'new_role' => $newRole,
                'category_id' => $category->id,
                'changed_by' => $this->actorEmail(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Role berhasil diubah menjadi " . ucfirst($newRole),
                'role' => $newRole,
                'role_display' => ucfirst($newRole),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to set bot user role', [
                'telegram_id' => $user->telegram_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah role: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get bot admin statistics
     */
    public function stats()
    {
        $category = $this->resolveCategory(request());
        $categoryId = $category->id;

        $stats = [
            'total_users' => ViewLog::where('category_id', $categoryId)
                ->distinct('user_id')
                ->count('user_id'),
            'total_admins' => CategoryAdmin::where('category_id', $categoryId)
                ->where('role', CategoryAdmin::ROLE_ADMIN)
                ->count(),
            'total_moderators' => CategoryAdmin::where('category_id', $categoryId)
                ->where('role', CategoryAdmin::ROLE_MODERATOR)
                ->count(),
            'total_vip' => UserCategoryVip::active()
                ->where('category_id', $categoryId)
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Resolve authenticated user's email for logging purposes.
     */
    private function actorEmail(): string
    {
        return optional(request()->user())->email ?? 'system';
    }

    private function resolveCategory(Request $request): Category
    {
        $user = $request->user();
        $categories = $user ? $user->getAccessibleCategories() : collect();
        $accessibleCategoryIds = $categories->pluck('id');
        $selectedCategoryId = $request->input('category_id');

        if ($user && !$user->isSuperAdmin()) {
            if (!$selectedCategoryId) {
                $this->abortJson('Kategori wajib dipilih.', 422);
            }

            if (!$accessibleCategoryIds->contains((int) $selectedCategoryId)) {
                $this->abortJson('Anda tidak memiliki akses ke kategori ini.', 403);
            }
        }

        $category = $selectedCategoryId
            ? Category::find($selectedCategoryId)
            : $categories->first();

        if (!$category) {
            $this->abortJson('Kategori tidak ditemukan.', 422);
        }

        return $category;
    }

    private function abortJson(string $message, int $status): void
    {
        throw new HttpResponseException(
            response()->json(['message' => $message], $status)
        );
    }
}
