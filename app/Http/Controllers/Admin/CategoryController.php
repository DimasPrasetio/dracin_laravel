<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryAdmin;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct(
        protected TelegramService $telegramService
    ) {}

    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $user = auth()->user();

        // Super admin can see all categories
        if ($user->isSuperAdmin()) {
            $categories = Category::withCount(['movies', 'payments' => function ($query) {
                $query->where('status', 'paid');
            }])->latest()->get();
        } else {
            // Regular admin/moderator can only see their categories
            $categories = $user->assignedCategories()
                ->withCount(['movies', 'payments' => function ($query) {
                    $query->where('status', 'paid');
                }])->latest()->get();
        }

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        $this->authorize('manageCategories', User::class);

        return view('admin.categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $this->authorize('manageCategories', User::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:categories,slug',
            'description' => 'nullable|string|max:500',
            'bot_token' => 'required|string|max:100',
            'bot_username' => 'required|string|max:100',
            'channel_id' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        // Ensure bot_username starts with @
        if (!str_starts_with($data['bot_username'], '@')) {
            $data['bot_username'] = '@' . $data['bot_username'];
        }

        // Generate webhook secret
        $data['webhook_secret'] = Str::random(32);
        $data['is_active'] = true;

        $category = Category::create($data);

        // Assign current user as admin of this category
        CategoryAdmin::create([
            'category_id' => $category->id,
            'user_id' => auth()->id(),
            'role' => 'admin',
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dibuat!');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->hasAccessToCategory($category->id)) {
            abort(403, 'Anda tidak memiliki akses ke kategori ini.');
        }

        $category->loadCount(['movies', 'payments' => function ($query) {
            $query->where('status', 'paid');
        }]);

        // Get category admins
        $categoryAdmins = $category->categoryAdmins()
            ->with(['user'])
            ->get();

        // Get statistics
        $stats = [
            'total_movies' => $category->movies()->count(),
            'total_revenue' => $category->payments()->where('status', 'paid')->sum('amount'),
            'active_vip' => $category->vipSubscriptions()->active()->count(),
            'total_views' => $category->viewLogs()->count(),
        ];

        return view('admin.categories.show', compact('category', 'categoryAdmins', 'stats'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdminForCategory($category->id)) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit kategori ini.');
        }

        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdminForCategory($category->id)) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit kategori ini.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string|max:500',
            'bot_token' => 'required|string|max:100',
            'bot_username' => 'required|string|max:100',
            'channel_id' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Ensure bot_username starts with @
        if (!str_starts_with($data['bot_username'], '@')) {
            $data['bot_username'] = '@' . $data['bot_username'];
        }

        $category->update($data);

        return redirect()->route('admin.categories.show', $category)
            ->with('success', 'Kategori berhasil diperbarui!');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        $this->authorize('manageCategories', User::class);

        // Prevent deletion of default category
        if ($category->slug === 'dracin') {
            return redirect()->back()
                ->with('error', 'Kategori utama Dracin tidak dapat dihapus.');
        }

        // Check if category has movies
        if ($category->movies()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Kategori tidak dapat dihapus karena masih memiliki film.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus!');
    }

    /**
     * Regenerate webhook secret for category.
     */
    public function regenerateWebhookSecret(Category $category)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdminForCategory($category->id)) {
            abort(403);
        }

        $category->update([
            'webhook_secret' => Str::random(32),
        ]);

        return redirect()->back()
            ->with('success', 'Webhook secret berhasil di-regenerate! Jangan lupa set webhook ulang.');
    }

    /**
     * Set webhook for category's bot.
     */
    public function setWebhook(Category $category)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdminForCategory($category->id)) {
            abort(403);
        }

        try {
            $success = $this->telegramService->setWebhook($category);

            if ($success) {
                Log::info('Webhook set for category via dashboard', [
                    'category' => $category->name,
                    'webhook_url' => $category->webhook_url,
                    'set_by' => $user->email,
                ]);

                return redirect()->back()
                    ->with('success', 'Webhook berhasil dikonfigurasi untuk ' . $category->bot_username);
            }

            return redirect()->back()
                ->with('error', 'Gagal mengkonfigurasi webhook. Periksa bot token.');

        } catch (\Exception $e) {
            Log::error('Failed to set webhook for category', [
                'category' => $category->name,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal mengkonfigurasi webhook: ' . $e->getMessage());
        }
    }

    /**
     * Get webhook status for category's bot (AJAX).
     */
    public function webhookStatus(Category $category)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdminForCategory($category->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $webhookInfo = $this->telegramService->getWebhookInfo($category);

            return response()->json([
                'success' => true,
                'data' => [
                    'url' => $webhookInfo['url'] ?? null,
                    'has_custom_certificate' => $webhookInfo['has_custom_certificate'] ?? false,
                    'pending_update_count' => $webhookInfo['pending_update_count'] ?? 0,
                    'last_error_date' => $webhookInfo['last_error_date'] ?? null,
                    'last_error_message' => $webhookInfo['last_error_message'] ?? null,
                    'max_connections' => $webhookInfo['max_connections'] ?? null,
                    'ip_address' => $webhookInfo['ip_address'] ?? null,
                    'is_configured' => !empty($webhookInfo['url']),
                    'is_correct_url' => ($webhookInfo['url'] ?? '') === $category->webhook_url,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get webhook info for category', [
                'category' => $category->name,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ==========================================
    // CATEGORY ADMIN MANAGEMENT
    // ==========================================

    /**
     * Add admin to category.
     */
    public function addAdmin(Request $request, Category $category)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdminForCategory($category->id)) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,moderator',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Check if already exists
        $exists = CategoryAdmin::where('category_id', $category->id)
            ->where(function ($query) use ($data) {
                $query->where('user_id', $data['user_id']);
            })
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('error', 'User sudah menjadi admin/moderator di kategori ini.');
        }

        CategoryAdmin::create([
            'category_id' => $category->id,
            'user_id' => $data['user_id'],
            'role' => $data['role'],
        ]);

        return redirect()->back()
            ->with('success', 'Admin berhasil ditambahkan!');
    }

    /**
     * Update admin role in category.
     */
    public function updateAdminRole(Request $request, Category $category, CategoryAdmin $categoryAdmin)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdminForCategory($category->id)) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'role' => 'required|in:admin,moderator',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $categoryAdmin->update(['role' => $request->role]);

        return redirect()->back()
            ->with('success', 'Role admin berhasil diperbarui!');
    }

    /**
     * Remove admin from category.
     */
    public function removeAdmin(Category $category, CategoryAdmin $categoryAdmin)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdminForCategory($category->id)) {
            abort(403);
        }

        // Prevent removing self if you're the only admin
        $adminCount = $category->categoryAdmins()->where('role', 'admin')->count();
        if ($adminCount <= 1 && $categoryAdmin->role === 'admin') {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus admin terakhir dari kategori.');
        }

        $categoryAdmin->delete();

        return redirect()->back()
            ->with('success', 'Admin berhasil dihapus dari kategori!');
    }

    /**
     * Get available users for adding as admin (AJAX).
     */
    public function getAvailableUsers(Request $request, Category $category)
    {
        $search = $request->get('search', '');
        $type = $request->get('type');

        // Get users not already in this category
        $existingUserIds = $category->categoryAdmins()
            ->whereNotNull('user_id')
            ->pluck('user_id');

        $users = User::whereNotIn('id', $existingUserIds)
            ->when($type === 'telegram', function ($query) {
                $query->whereNotNull('telegram_id');
            })
            ->when($type === 'web', function ($query) {
                $query->whereNotNull('email');
            })
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('telegram_id', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'username', 'telegram_id']);

        return response()->json($users);
    }
}
