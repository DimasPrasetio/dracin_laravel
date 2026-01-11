<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use App\Models\BotState;

class AddMovieCommand extends Command
{
    protected string $name = 'addmovie';
    protected string $description = 'Tambah film baru (Admin only)';

    public function handle()
    {
        $update = $this->getUpdate();
        $telegramUser = $update->getMessage()->from;
        $message = $update->getMessage();

        // Check if admin
        if ($telegramUser->id != env('TELE_ADMIN_ID')) {
            $this->replyWithMessage([
                'text' => "\u{274C} Command ini hanya untuk admin.",
            ]);
            return;
        }

        // Set bot state and track command message ID
        BotState::setState($telegramUser->id, 'AWAITING_THUMBNAIL', [
            'step' => 'thumbnail',
            'message_ids' => [$message->message_id], // Track command message
        ]);

        $response = $this->replyWithMessage([
            'text' => "\u{1F3AC} <b>Tambah Film Baru</b>\n\n\u{1F4F8} Silakan masukkan thumbnail film (foto)",
            'parse_mode' => 'HTML',
        ]);

        // Track bot response message ID
        if (isset($response->message_id)) {
            $state = BotState::getState($telegramUser->id);
            $data = $state->data ?? [];
            $data['message_ids'][] = $response->message_id;
            BotState::setState($telegramUser->id, 'AWAITING_THUMBNAIL', $data);
        }
    }
}
