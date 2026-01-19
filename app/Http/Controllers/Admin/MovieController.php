<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\Payment;
use App\Models\ViewLog;
use App\Services\TelegramService;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MovieController extends Controller
{
    protected $telegramService;
    protected $videoService;

    public function __construct(TelegramService $telegramService, VideoService $videoService)
    {
        $this->telegramService = $telegramService;
        $this->videoService = $videoService;
    }

    public function index()
    {
        $user = auth()->user();
        $categories = $user ? $user->getAccessibleCategories() : collect();

        return view('movies.index', compact('categories'));
    }

    public function data(Request $request)
    {
        $user = $request->user();
        $query = Movie::with('videoParts')->latest();

        if ($user && !$user->isSuperAdmin()) {
            $categoryIds = $user->getAccessibleCategories()->pluck('id');
            $query->whereIn('category_id', $categoryIds);
        }

        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where('title', 'like', "%{$search}%");
        }

        $total = $query->count();

        if ($request->has('start')) {
            $query->skip($request->start);
        }

        if ($request->has('length')) {
            $query->take($request->length);
        }

        $movies = $query->get();

        return response()->json([
            'draw' => $request->draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $movies,
        ]);
    }

    public function create()
    {
        $freeParts = Setting::getFreeParts();
        return view('movies.create', compact('freeParts'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer|exists:categories,id',
            'title' => 'required|string|max:255',
            'thumbnail' => 'required|image|max:10240',
            'total_parts' => 'required|integer|min:1|max:50',
            'videos.*' => 'required|file|mimes:mp4,mkv,avi|max:2097152', // 2GB max
        ]);


        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $category = Category::findOrFail($request->category_id);
            $user = $request->user();

            if ($user && !$user->isSuperAdmin() && !$user->canAddMoviesForCategory($category->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk kategori ini.'
                ], 403);
            }

            $this->videoService->setCategory($category);

            // Ensure temp directory exists
            $tempDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Upload thumbnail to Telegram - use 'temp' disk
            $thumbnailPath = $request->file('thumbnail')->store('temp', 'temp');
            // Get full path with proper directory separator
            $fullThumbnailPath = storage_path('app' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $thumbnailPath));

            Log::info('Thumbnail path debug', [
                'stored_path' => $thumbnailPath,
                'full_path' => $fullThumbnailPath,
                'file_exists' => file_exists($fullThumbnailPath)
            ]);

            $thumbnailFileId = $this->telegramService->uploadPhoto($fullThumbnailPath);

            if (!$thumbnailFileId) {
                // Clean up temp file before throwing exception
                Storage::disk('temp')->delete($thumbnailPath);
                throw new \Exception('Failed to upload thumbnail to Telegram. Please check your bot token and admin ID.');
            }

            // Upload videos to Telegram
            $videoParts = [];
            foreach ($request->file('videos') as $index => $video) {
                $videoPath = $video->store('temp', 'temp');
                // Get full path with proper directory separator
                $fullVideoPath = storage_path('app' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $videoPath));

                Log::info('Video path debug', [
                    'part' => $index + 1,
                    'stored_path' => $videoPath,
                    'full_path' => $fullVideoPath,
                    'file_exists' => file_exists($fullVideoPath)
                ]);

                $fileId = $this->telegramService->uploadVideo($fullVideoPath);

                if (!$fileId) {
                    // Clean up temp video file
                    Storage::disk('temp')->delete($videoPath);
                    throw new \Exception("Failed to upload video part " . ($index + 1) . ". Please check your bot token and file size.");
                }

                $videoParts[] = [
                    'file_id' => $fileId,
                    'duration' => null,
                    'file_size' => $video->getSize(),
                ];

                // Clean up temp file
                Storage::disk('temp')->delete($videoPath);
            }

            // Create movie
            $movie = $this->videoService->createMovie([
                'category_id' => $category->id,
                'title' => $request->title,
                'thumbnail_file_id' => $thumbnailFileId,
                'total_parts' => $request->total_parts,
                'video_parts' => $videoParts,
                'created_by' => optional($request->user())->id ?? 1,
            ]);

            // Clean up temp thumbnail
            Storage::disk('temp')->delete($thumbnailPath);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Film berhasil ditambahkan dan dipost ke channel!',
                    'data' => $movie
                ]);
            }

            return redirect()->route('movies.index')
                ->with('success', 'Film berhasil ditambahkan dan dipost ke channel!');
        } catch (\Exception $e) {
            Log::error('Movie store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up any uploaded temp files on error
            if (isset($thumbnailPath)) {
                Storage::disk('temp')->delete($thumbnailPath);
            }
            if (isset($videoParts)) {
                foreach ($videoParts as $part) {
                    if (isset($part['temp_path'])) {
                        Storage::disk('temp')->delete($part['temp_path']);
                    }
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan film: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal menambahkan film: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Movie $movie)
    {
        $this->authorizeMovieAdmin($movie);
        $movie->load('videoParts');
        return view('movies.show', compact('movie'));
    }

    public function edit(Movie $movie)
    {
        return redirect()->route('movies.index');
    }

    public function update(Request $request, Movie $movie)
    {
        $this->authorizeMovieAdmin($movie);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'thumbnail' => 'nullable|image|max:10240',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data = ['title' => $request->title];

            // Upload new thumbnail if provided
            if ($request->hasFile('thumbnail')) {
                // Ensure temp directory exists
                $tempDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp');
                if (!is_dir($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }

                $thumbnailPath = $request->file('thumbnail')->store('temp', 'temp');
                // Get full path with proper directory separator
                $fullThumbnailPath = storage_path('app' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $thumbnailPath));
                $thumbnailFileId = $this->telegramService->uploadPhoto($fullThumbnailPath);

                if ($thumbnailFileId) {
                    $data['thumbnail_file_id'] = $thumbnailFileId;
                    Storage::disk('temp')->delete($thumbnailPath);
                }
            }

            $this->videoService->updateMovie($movie, $data);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Film berhasil diupdate!',
                    'data' => $movie->fresh(['videoParts'])
                ]);
            }

            return redirect()->route('movies.index')
                ->with('success', 'Film berhasil diupdate!');
        } catch (\Exception $e) {
            Log::error('Movie update error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate film: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal mengupdate film: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Movie $movie)
    {
        $this->authorizeMovieAdmin($movie);
        try {
            $this->videoService->deleteMovie($movie);

            return response()->json([
                'success' => true,
                'message' => 'Film berhasil dihapus!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus film: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateVip(Request $request, Movie $movie)
    {
        $this->authorizeMovieAdmin($movie);
        $request->validate([
            'vip_parts' => 'nullable|array',
            'vip_parts.*' => 'integer',
        ]);

        try {
            $vipParts = $request->vip_parts ?? [];
            $this->videoService->updatePartVipStatus($movie, $vipParts);

            return response()->json([
                'success' => true,
                'message' => 'Status VIP berhasil diupdate!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status VIP: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function transactions()
    {
        $user = auth()->user();
        $categories = $user ? $user->getAccessibleCategories() : collect();
        $selectedCategoryId = (int) (request()->input('category_id') ?? $categories->first()?->id ?? 0);

        return view('movies.transactions', compact('categories', 'selectedCategoryId'));
    }

    public function transactionsData(Request $request)
    {
        $query = Movie::with('videoParts')->latest();
        $user = $request->user();
        $categories = $user ? $user->getAccessibleCategories() : collect();
        $categoryIds = $categories->pluck('id');
        $selectedCategoryId = $request->input('category_id');

        if ($user && !$user->isSuperAdmin()) {
            if (!$selectedCategoryId) {
                return response()->json([
                    'message' => 'Kategori wajib dipilih.',
                ], 422);
            }

            if (!$categoryIds->contains((int) $selectedCategoryId)) {
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

        if ($selectedCategoryId && $selectedCategoryId !== 'all') {
            $query->where('category_id', $selectedCategoryId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $query->whereIn('category_id', $categoryIds);
        }

        // Apply filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            if ($request->status === 'posted') {
                $query->whereNotNull('channel_message_id');
            } elseif ($request->status === 'draft') {
                $query->whereNull('channel_message_id');
            }
        }

        $total = $query->count();

        // Apply pagination
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $movies = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $movieIds = $movies->pluck('id');
        $paymentStats = collect();
        $freeViewStats = collect();

        if ($movieIds->isNotEmpty()) {
            $paymentStats = Payment::selectRaw('video_parts.movie_id as movie_id, COUNT(*) as vip_access, SUM(payments.amount) as revenue')
                ->join('video_parts', 'video_parts.id', '=', 'payments.video_part_id')
                ->whereIn('video_parts.movie_id', $movieIds)
                ->groupBy('video_parts.movie_id')
                ->get()
                ->keyBy('movie_id');

            $freeViewStats = ViewLog::selectRaw('video_parts.movie_id as movie_id, COUNT(*) as free_views')
                ->join('video_parts', 'video_parts.id', '=', 'view_logs.video_part_id')
                ->whereIn('video_parts.movie_id', $movieIds)
                ->where('video_parts.is_vip', false)
                ->groupBy('video_parts.movie_id')
                ->get()
                ->keyBy('movie_id');
        }

        // Calculate stats for each movie
        $movies->transform(function ($movie) use ($paymentStats, $freeViewStats) {
            $paymentStat = $paymentStats->get($movie->id);
            $freeStat = $freeViewStats->get($movie->id);

            $movie->revenue = (int) ($paymentStat->revenue ?? 0);
            $movie->vip_access = (int) ($paymentStat->vip_access ?? 0);
            $movie->free_views = (int) ($freeStat->free_views ?? 0);
            $movie->total_views = $movie->free_views + $movie->vip_access;

            return $movie;
        });

        // Calculate overall stats
        $overallPaymentsQuery = Payment::query();

        if ($selectedCategoryId && $selectedCategoryId !== 'all') {
            $overallPaymentsQuery->where('category_id', $selectedCategoryId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $overallPaymentsQuery->whereIn('category_id', $categoryIds);
        }

        $overallPayments = $overallPaymentsQuery
            ->selectRaw('COUNT(*) as vip_access, SUM(amount) as revenue')
            ->first();

        $overallViews = ViewLog::selectRaw('COUNT(*) as views')
            ->whereHas('videoPart', function ($query) use ($user, $categoryIds, $selectedCategoryId) {
                if ($selectedCategoryId && $selectedCategoryId !== 'all') {
                    $query->whereHas('movie', function ($movieQuery) use ($selectedCategoryId) {
                        $movieQuery->where('category_id', $selectedCategoryId);
                    });
                    return;
                }

                if ($user && !$user->isSuperAdmin()) {
                    $query->whereHas('movie', function ($movieQuery) use ($categoryIds) {
                        $movieQuery->whereIn('category_id', $categoryIds);
                    });
                    return;
                }

                $query->whereHas('movie');
            })
            ->first();

        $stats = [
            'total_revenue' => (int) ($overallPayments->revenue ?? 0),
            'total_vip_access' => (int) ($overallPayments->vip_access ?? 0),
            'total_views' => (int) ($overallViews->views ?? 0) + (int) ($overallPayments->vip_access ?? 0),
            'active_movies' => Movie::whereNotNull('channel_message_id')
                ->when($selectedCategoryId && $selectedCategoryId !== 'all', function ($query) use ($selectedCategoryId) {
                    $query->where('category_id', $selectedCategoryId);
                })
                ->when($user && !$user->isSuperAdmin(), function ($query) use ($categoryIds) {
                    $query->whereIn('category_id', $categoryIds);
                })
                ->count(),
        ];

        return response()->json([
            'data' => $movies,
            'total' => $total,
            'stats' => $stats,
        ]);
    }

    public function transactionDetails(Movie $movie)
    {
        $this->authorizeMovieAdmin($movie);
        $movie->load('videoParts');

        // Get payments for this movie
        $transactions = Payment::with(['user', 'videoPart'])
            ->whereHas('videoPart', function ($q) use ($movie) {
                $q->where('movie_id', $movie->id);
            })
            ->latest()
            ->take(50)
            ->get();

        // Calculate stats
        $stats = [
            'revenue' => $transactions->sum('amount'),
            'vip_access' => $transactions->count(),
            'conversion_rate' => 0,
        ];

        // Calculate parts performance
        $partsStats = [];
        $partIds = $movie->videoParts->pluck('id');
        $partViewCounts = collect();
        $partPaymentCounts = collect();

        if ($partIds->isNotEmpty()) {
            $partViewCounts = ViewLog::selectRaw('video_part_id, COUNT(*) as views')
                ->whereIn('video_part_id', $partIds)
                ->groupBy('video_part_id')
                ->pluck('views', 'video_part_id');

            $partPaymentCounts = Payment::selectRaw('video_part_id, COUNT(*) as payments')
                ->whereIn('video_part_id', $partIds)
                ->groupBy('video_part_id')
                ->pluck('payments', 'video_part_id');
        }

        foreach ($movie->videoParts as $part) {
            $partViews = (int) ($partViewCounts[$part->id] ?? 0);
            $partPayments = (int) ($partPaymentCounts[$part->id] ?? 0);

            $partsStats[] = [
                'part_number' => $part->part_number,
                'is_vip' => $part->is_vip,
                'views' => $part->is_vip ? $partPayments : $partViews,
            ];
        }

        $stats['parts'] = $partsStats;

        // Calculate conversion rate
        $totalFreeViews = $movie->videoParts
            ->filter(fn ($part) => !$part->is_vip)
            ->sum(fn ($part) => (int) ($partViewCounts[$part->id] ?? 0));

        if ($totalFreeViews > 0) {
            $stats['conversion_rate'] = round(($stats['vip_access'] / $totalFreeViews) * 100, 1);
        }

        return response()->json([
            'movie' => $movie,
            'transactions' => $transactions,
            'stats' => $stats,
        ]);
    }

    private function authorizeMovieAdmin(Movie $movie): void
    {
        $user = request()->user();

        if (!$user) {
            abort(403, 'Unauthorized access');
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        if (!$user->canEditMoviesForCategory((int) $movie->category_id)) {
            abort(403, 'Anda tidak memiliki akses untuk kategori ini.');
        }
    }
}
