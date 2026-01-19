<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Models\ViewLog;
use App\Models\Movie;
use App\Models\VideoPart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ViewLogController extends Controller
{
    /**
     * Display view analytics dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $categories = $user ? $user->getAccessibleCategories() : collect();
        $movies = Movie::query()
            ->when($user && !$user->isSuperAdmin(), function ($query) use ($categories) {
                $query->whereIn('category_id', $categories->pluck('id'));
            })
            ->orderBy('title')
            ->get();

        return view('admin.view-logs.index', compact('categories', 'movies'));
    }

    /**
     * Get analytics data (AJAX)
     */
    public function analytics(Request $request)
    {
        $request->validate([
            'period' => 'nullable|string|in:today,week,month,year,all',
            'movie_id' => 'nullable|integer|exists:movies,id',
            'is_vip' => 'nullable|boolean',
            'category_id' => 'nullable',
        ]);

        $user = $request->user();
        $categories = $user ? $user->getAccessibleCategories() : collect();
        $accessibleCategoryIds = $categories->pluck('id');
        $selectedCategoryId = $request->input('category_id');

        if ($user && !$user->isSuperAdmin()) {
            if (!$selectedCategoryId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori wajib dipilih.',
                ], 422);
            }

            if (!$accessibleCategoryIds->contains((int) $selectedCategoryId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke kategori ini.',
                ], 403);
            }
        } elseif ($selectedCategoryId && $selectedCategoryId !== 'all') {
            if (!Category::whereKey($selectedCategoryId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kategori tidak ditemukan.',
                ], 422);
            }
        }

        $period = $request->input('period', 'week');
        $movieId = $request->input('movie_id');
        $isVip = $request->input('is_vip');

        // Base query
        $query = ViewLog::query();

        // Apply period filter
        $query = $this->applyPeriodFilter($query, $period);

        if ($selectedCategoryId && $selectedCategoryId !== 'all') {
            $query->where('category_id', $selectedCategoryId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $query->whereIn('category_id', $accessibleCategoryIds);
        }

        // Apply movie filter
        if ($movieId) {
            if ($user && !$user->isSuperAdmin()) {
                $movieInAccess = Movie::whereKey($movieId)
                    ->whereIn('category_id', $accessibleCategoryIds)
                    ->exists();

                if (!$movieInAccess) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke film ini.',
                    ], 403);
                }
            }

            $query->whereHas('videoPart', function ($q) use ($movieId) {
                $q->where('movie_id', $movieId);
            });
        }

        // Apply VIP filter
        if ($isVip !== null) {
            $query->where('is_vip', $isVip);
        }

        // Get overall stats
        $stats = [
            'total_views' => (clone $query)->count(),
            'vip_views' => (clone $query)->where('is_vip', true)->count(),
            'free_views' => (clone $query)->where('is_vip', false)->count(),
            'unique_users' => (clone $query)->distinct('user_id')->count('user_id'),
        ];

        // Get top movies
        $topMovies = (clone $query)
            ->select('video_parts.movie_id', DB::raw('COUNT(*) as views'))
            ->join('video_parts', 'video_parts.id', '=', 'view_logs.video_part_id')
            ->groupBy('video_parts.movie_id')
            ->orderByDesc('views')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $movie = Movie::find($item->movie_id);
                return [
                    'movie_id' => $item->movie_id,
                    'title' => $movie ? $movie->title : 'Unknown',
                    'views' => $item->views,
                ];
            });

        // Get views timeline (daily for last period)
        $timeline = $this->getViewsTimeline($query, $period);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'top_movies' => $topMovies,
            'timeline' => $timeline,
        ]);
    }

    /**
     * Get view logs data for DataTable (AJAX)
     */
    public function data(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable',
        ]);

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

        $query = ViewLog::with(['user', 'videoPart.movie', 'category'])
            ->orderBy('created_at', 'desc');

        // Search
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('username', 'like', "%{$search}%")
                       ->orWhere('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('name', 'like', "%{$search}%");
                })
                ->orWhereHas('videoPart.movie', function ($q2) use ($search) {
                    $q2->where('title', 'like', "%{$search}%");
                });
            });
        }

        // Filter by date range
        if ($request->has('start_date') && $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->has('end_date') && $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // Filter by VIP status
        if ($request->has('is_vip') && $request->input('is_vip') !== '') {
            $query->where('is_vip', $request->input('is_vip'));
        }

        if ($selectedCategoryId && $selectedCategoryId !== 'all') {
            $query->where('category_id', $selectedCategoryId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $query->whereIn('category_id', $accessibleCategoryIds);
        }

        $totalData = $query->count();
        $totalFiltered = $totalData;

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $logs = $query->skip($start)->take($length)->get();

        $data = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'user' => $log->user ? [
                    'id' => $log->user->telegram_id ?? $log->user->id,
                    'username' => $log->user->username,
                    'name' => $log->user->display_name,
                ] : null,
                'category' => $log->category ? [
                    'id' => $log->category->id,
                    'name' => $log->category->name,
                ] : ($log->videoPart && $log->videoPart->movie && $log->videoPart->movie->category ? [
                    'id' => $log->videoPart->movie->category->id,
                    'name' => $log->videoPart->movie->category->name,
                ] : null),
                'movie' => $log->videoPart && $log->videoPart->movie ? [
                    'id' => $log->videoPart->movie->id,
                    'title' => $log->videoPart->movie->title,
                ] : null,
                'part_number' => $log->videoPart ? $log->videoPart->part_number : null,
                'is_vip' => $log->is_vip,
                'source' => $log->source,
                'device' => $log->device,
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $log->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Get user viewing history
     */
    public function userHistory(Request $request, int $telegramUserId)
    {
        $user = $request->user();
        $categories = $user ? $user->getAccessibleCategories() : collect();
        $accessibleCategoryIds = $categories->pluck('id');
        $selectedCategoryId = $request->input('category_id');

        if ($user && !$user->isSuperAdmin()) {
            if (!$selectedCategoryId || !$accessibleCategoryIds->contains((int) $selectedCategoryId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke kategori ini.',
                ], 403);
            }
        }

        $user = User::findByTelegramId($telegramUserId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        }

        $logs = ViewLog::with(['videoPart.movie'])
            ->where('user_id', $user->id)
            ->when($selectedCategoryId && $selectedCategoryId !== 'all', function ($query) use ($selectedCategoryId) {
                $query->where('category_id', $selectedCategoryId);
            })
            ->when($user && !$user->isSuperAdmin(), function ($query) use ($accessibleCategoryIds) {
                $query->whereIn('category_id', $accessibleCategoryIds);
            })
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $data = $logs->map(function ($log) {
            return [
                'movie_title' => $log->videoPart && $log->videoPart->movie
                    ? $log->videoPart->movie->title
                    : 'Unknown',
                'part_number' => $log->videoPart ? $log->videoPart->part_number : null,
                'is_vip' => $log->is_vip,
                'viewed_at' => $log->created_at->format('Y-m-d H:i:s'),
                'viewed_at_human' => $log->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Apply period filter to query
     */
    private function applyPeriodFilter($query, string $period)
    {
        $column = 'view_logs.created_at';

        return match ($period) {
            'today' => $query->whereDate($column, today()),
            'week' => $query->whereBetween($column, [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth($column, now()->month)
                             ->whereYear($column, now()->year),
            'year' => $query->whereYear($column, now()->year),
            'all' => $query,
            default => $query->whereBetween($column, [now()->startOfWeek(), now()->endOfWeek()]),
        };
    }

    /**
     * Get views timeline data
     */
    private function getViewsTimeline($query, string $period)
    {
        $column = 'view_logs.created_at';
        $format = match ($period) {
            'today' => '%H:00',
            'week', 'month' => '%Y-%m-%d',
            'year' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $timeline = (clone $query)
            ->select(
                DB::raw("DATE_FORMAT({$column}, '{$format}') as date"),
                DB::raw('COUNT(*) as views'),
                DB::raw('SUM(CASE WHEN is_vip = 1 THEN 1 ELSE 0 END) as vip_views'),
                DB::raw('SUM(CASE WHEN is_vip = 0 THEN 1 ELSE 0 END) as free_views')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $timeline->map(function ($item) {
            return [
                'date' => $item->date,
                'views' => $item->views,
                'vip_views' => $item->vip_views,
                'free_views' => $item->free_views,
            ];
        });
    }
}
