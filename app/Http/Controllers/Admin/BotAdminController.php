<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use App\Services\TelegramAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        return view('admin.bot-admins.index');
    }

    /**
     * Get bot admins data for DataTables
     */
    public function data(Request $request)
    {
        $query = $request->get('q');
        $perPage = (int) ($request->get('per_page', 10));

        $telegramUsers = TelegramUser::query()
            ->with('linkedUser')
            ->when($query, function ($qr) use ($query) {
                $qr->where(function ($qq) use ($query) {
                    $qq->where('telegram_user_id', 'like', "%{$query}%")
                       ->orWhere('username', 'like', "%{$query}%")
                       ->orWhere('first_name', 'like', "%{$query}%")
                       ->orWhere('last_name', 'like', "%{$query}%")
                       ->orWhere('role', 'like', "%{$query}%");
                });
            })
            ->orderByRaw("FIELD(role, 'admin', 'moderator', 'user')")
            ->orderByDesc('id')
            ->paginate($perPage)
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'telegram_user_id' => $user->telegram_user_id,
                    'username' => $user->username,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'role' => $user->role,
                    'role_display' => $user->role_display_name,
                    'is_vip' => $user->isVip(),
                    'vip_until' => $user->vip_until?->format('d M Y H:i'),
                    'linked_user_id' => $user->linked_user_id,
                    'linked_user_name' => $user->linkedUser?->name,
                    'linked_user_email' => $user->linkedUser?->email,
                    'created_at' => $user->created_at?->format('d M Y H:i'),
                ];
            });

        return response()->json($telegramUsers);
    }

    /**
     * Toggle admin status for telegram user
     */
    public function toggleAdmin(TelegramUser $telegramUser)
    {
        try {
            $oldRole = $telegramUser->role;
            $this->authService->toggleAdmin($telegramUser);
            $telegramUser->refresh();

            Log::info('Bot admin status toggled via dashboard', [
                'telegram_user_id' => $telegramUser->telegram_user_id,
                'old_role' => $oldRole,
                'new_role' => $telegramUser->role,
                'changed_by' => $this->actorEmail(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "User berhasil diubah menjadi {$telegramUser->role_display_name}",
                'role' => $telegramUser->role,
                'role_display' => $telegramUser->role_display_name,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to toggle bot admin status', [
                'telegram_user_id' => $telegramUser->telegram_user_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status admin: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set role for telegram user
     */
    public function setRole(Request $request, TelegramUser $telegramUser)
    {
        $request->validate([
            'role' => 'required|in:user,admin,moderator',
        ]);

        try {
            $oldRole = $telegramUser->role;
            $telegramUser->update(['role' => $request->role]);

            // Clear cache
            $this->authService->clearUserCache($telegramUser->telegram_user_id);

            Log::info('Bot user role changed via dashboard', [
                'telegram_user_id' => $telegramUser->telegram_user_id,
                'old_role' => $oldRole,
                'new_role' => $request->role,
                'changed_by' => $this->actorEmail(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Role berhasil diubah menjadi {$telegramUser->role_display_name}",
                'role' => $telegramUser->role,
                'role_display' => $telegramUser->role_display_name,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to set bot user role', [
                'telegram_user_id' => $telegramUser->telegram_user_id,
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
        $stats = [
            'total_users' => TelegramUser::count(),
            'total_admins' => TelegramUser::where('role', TelegramUser::ROLE_ADMIN)->count(),
            'total_moderators' => TelegramUser::where('role', TelegramUser::ROLE_MODERATOR)->count(),
            'total_vip' => TelegramUser::where('vip_until', '>', now())->count(),
            'linked_users' => TelegramUser::whereNotNull('linked_user_id')->count(),
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
}
