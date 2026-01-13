<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        return view('admin.view-logs.index');
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
        ]);

        $period = $request->input('period', 'week');
        $movieId = $request->input('movie_id');
        $isVip = $request->input('is_vip');

        // Base query
        $query = ViewLog::query();

        // Apply period filter
        $query = $this->applyPeriodFilter($query, $period);

        // Apply movie filter
        if ($movieId) {
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
            'unique_users' => (clone $query)->distinct('telegram_user_id')->count('telegram_user_id'),
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
        $query = ViewLog::with(['telegramUser', 'videoPart.movie'])
            ->orderBy('created_at', 'desc');

        // Search
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('telegramUser', function ($q2) use ($search) {
                    $q2->where('username', 'like', "%{$search}%")
                       ->orWhere('first_name', 'like', "%{$search}%");
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

        $totalData = $query->count();
        $totalFiltered = $totalData;

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $logs = $query->skip($start)->take($length)->get();

        $data = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'user' => $log->telegramUser ? [
                    'id' => $log->telegramUser->telegram_user_id,
                    'username' => $log->telegramUser->username,
                    'name' => $log->telegramUser->full_name,
                ] : null,
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
        $logs = ViewLog::with(['videoPart.movie'])
            ->where('telegram_user_id', $telegramUserId)
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
        return match ($period) {
            'today' => $query->whereDate('created_at', today()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month)
                             ->whereYear('created_at', now()->year),
            'year' => $query->whereYear('created_at', now()->year),
            'all' => $query,
            default => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
        };
    }

    /**
     * Get views timeline data
     */
    private function getViewsTimeline($query, string $period)
    {
        $format = match ($period) {
            'today' => '%H:00',
            'week', 'month' => '%Y-%m-%d',
            'year' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $timeline = (clone $query)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$format}') as date"),
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
