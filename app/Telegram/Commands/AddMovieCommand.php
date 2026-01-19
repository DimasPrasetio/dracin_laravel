<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use App\Models\BotState;
use App\Services\TelegramAuthService;
use App\Telegram\Commands\Traits\CategoryAware;

class AddMovieCommand extends Command
{
    use CategoryAware;

    protected string $name = 'addmovie';
    protected string $description = 'Tambah film baru (Admin & Moderator)';

    public function handle()
    {
        $update = $this->getUpdate();
        $telegramUser = $update->getMessage()->from;
        $message = $update->getMessage();
        $category = $this->getCategory();

        // Check if user can add movies (admin or moderator)
        // For category-specific bots, also check category admin
        $authService = app(TelegramAuthService::class);
        $canAddMovies = $authService->canAddMovies($telegramUser->id);

        // If category is set, also check if user is admin for this category
        if ($category && !$canAddMovies) {
            $canAddMovies = $authService->canAddMoviesForCategory($telegramUser->id, $category->id);
        }

        if (!$canAddMovies) {
            $this->replyWithMessage([
                'text' => "\u{274C} Command ini hanya untuk admin dan moderator.",
            ]);
            return;
        }

        // Set bot state and track command message ID
        // Include category_id in state for later use when creating movie
        $categoryInfo = $category ? " untuk {$category->name}" : "";
        $response = $this->replyWithMessage([
            'text' => "\u{1F3AC} <b>Tambah Film Baru{$categoryInfo}</b>\n\n\u{1F4F8} Silakan masukkan thumbnail film (foto)",
            'parse_mode' => 'HTML',
        ]);

        $messageIds = [$message->message_id];
        if (isset($response->message_id)) {
            $messageIds[] = $response->message_id;
        }

        BotState::setState($telegramUser->id, 'AWAITING_THUMBNAIL', [
            'step' => 'thumbnail',
            'category_id' => $category?->id,
            'message_ids' => $messageIds,
        ]);
    }
}
