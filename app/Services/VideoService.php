<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Movie;
use App\Models\VideoPart;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VideoService
{
    protected $telegramService;
    protected ?Category $category = null;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Set category context for multi-bot support
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        // Also set category on telegram service
        if ($category) {
            $this->telegramService->setCategory($category);
        }

        return $this;
    }

    /**
     * Get current category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Create movie with video parts
     */
    public function createMovie(array $data): Movie
    {
        return DB::transaction(function () use ($data) {
            // Determine category_id - use provided value, instance category, or null
            $categoryId = $data['category_id'] ?? $this->category?->id;

            // Create movie
            $movie = Movie::create([
                'title' => $data['title'],
                'thumbnail_file_id' => $data['thumbnail_file_id'],
                'total_parts' => $data['total_parts'],
                'created_by' => $data['created_by'] ?? null,
                'category_id' => $categoryId,
            ]);

            // Create video parts
            $freeParts = Setting::getFreeParts();

            foreach ($data['video_parts'] as $index => $videoPart) {
                $partNumber = $index + 1;

                VideoPart::create([
                    'movie_id' => $movie->id,
                    'video_id' => VideoPart::generateVideoId(),
                    'file_id' => $videoPart['file_id'],
                    'part_number' => $partNumber,
                    'is_vip' => !in_array($partNumber, $freeParts),
                    'duration' => $videoPart['duration'] ?? null,
                    'file_size' => $videoPart['file_size'] ?? null,
                ]);
            }

            // Post to channel
            $messageId = $this->telegramService->postMovieToChannel($movie);
            if ($messageId) {
                $movie->update(['channel_message_id' => $messageId]);
            }

            return $movie->fresh(['videoParts']);
        });
    }

    /**
     * Update movie
     */
    public function updateMovie(Movie $movie, array $data): Movie
    {
        return DB::transaction(function () use ($movie, $data) {
            $movie->update([
                'title' => $data['title'] ?? $movie->title,
                'thumbnail_file_id' => $data['thumbnail_file_id'] ?? $movie->thumbnail_file_id,
            ]);

            // Update channel message
            $this->telegramService->updateChannelMessage($movie);

            return $movie->fresh(['videoParts']);
        });
    }

    /**
     * Delete movie
     */
    public function deleteMovie(Movie $movie): bool
    {
        return DB::transaction(function () use ($movie) {
            // Delete from channel
            if ($movie->channel_message_id) {
                $movie->loadMissing('category');
                $this->telegramService->deleteChannelMessage($movie->channel_message_id, $movie->category);
            }

            // Delete movie (cascade will delete video parts)
            return $movie->delete();
        });
    }

    /**
     * Update VIP status for specific parts
     */
    public function updatePartVipStatus(Movie $movie, array $vipParts): void
    {
        DB::transaction(function () use ($movie, $vipParts) {
            // Set all parts to non-VIP first
            $movie->videoParts()->update(['is_vip' => false]);

            // Set specified parts to VIP
            if (!empty($vipParts)) {
                $movie->videoParts()
                    ->whereIn('part_number', $vipParts)
                    ->update(['is_vip' => true]);
            }

            // Update channel message
            $this->telegramService->updateChannelMessage($movie);
        });
    }

    /**
     * Get video by video_id (from deep link)
     */
    public function getVideoByVideoId(string $videoId): ?VideoPart
    {
        return VideoPart::with('movie')->where('video_id', $videoId)->first();
    }
}
