<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        return view('movies.index');
    }

    public function data(Request $request)
    {
        $query = Movie::with('videoParts')->latest();

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
                'title' => $request->title,
                'thumbnail_file_id' => $thumbnailFileId,
                'total_parts' => $request->total_parts,
                'video_parts' => $videoParts,
                'created_by' => auth()->id(),
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
        $movie->load('videoParts');
        return view('movies.show', compact('movie'));
    }

    public function edit(Movie $movie)
    {
        return redirect()->route('movies.index');
    }

    public function update(Request $request, Movie $movie)
    {
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
        return view('movies.transactions');
    }

    public function transactionsData(Request $request)
    {
        $query = Movie::with('videoParts')->latest();

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

        // Calculate stats for each movie
        $movies->transform(function ($movie) {
            $payments = Payment::whereHas('videoPart', function ($q) use ($movie) {
                $q->where('movie_id', $movie->id);
            })->sum('amount');

            $vipAccess = Payment::whereHas('videoPart', function ($q) use ($movie) {
                $q->where('movie_id', $movie->id);
            })->count();

            $freeViews = ViewLog::whereHas('videoPart', function ($q) use ($movie) {
                $q->where('movie_id', $movie->id)
                  ->where('is_vip', false);
            })->count();

            $totalViews = $freeViews + $vipAccess;

            $movie->revenue = $payments;
            $movie->vip_access = $vipAccess;
            $movie->free_views = $freeViews;
            $movie->total_views = $totalViews;

            return $movie;
        });

        // Calculate overall stats
        $stats = [
            'total_revenue' => Payment::whereHas('videoPart.movie')->sum('amount'),
            'total_vip_access' => Payment::whereHas('videoPart.movie')->count(),
            'total_views' => ViewLog::whereHas('videoPart.movie')->count() + Payment::whereHas('videoPart.movie')->count(),
            'active_movies' => Movie::whereNotNull('channel_message_id')->count(),
        ];

        return response()->json([
            'data' => $movies,
            'total' => $total,
            'stats' => $stats,
        ]);
    }

    public function transactionDetails(Movie $movie)
    {
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
        foreach ($movie->videoParts as $part) {
            $partViews = ViewLog::where('video_part_id', $part->id)->count();
            $partPayments = Payment::where('video_part_id', $part->id)->count();

            $partsStats[] = [
                'part_number' => $part->part_number,
                'is_vip' => $part->is_vip,
                'views' => $part->is_vip ? $partPayments : $partViews,
            ];
        }

        $stats['parts'] = $partsStats;

        // Calculate conversion rate
        $totalFreeViews = ViewLog::whereHas('videoPart', function ($q) use ($movie) {
            $q->where('movie_id', $movie->id)->where('is_vip', false);
        })->count();

        if ($totalFreeViews > 0) {
            $stats['conversion_rate'] = round(($stats['vip_access'] / $totalFreeViews) * 100, 1);
        }

        return response()->json([
            'movie' => $movie,
            'transactions' => $transactions,
            'stats' => $stats,
        ]);
    }
}
