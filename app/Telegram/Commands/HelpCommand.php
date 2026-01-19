<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use App\Telegram\Commands\Traits\CategoryAware;

class HelpCommand extends Command
{
    use CategoryAware;

    protected string $name = 'help';
    protected string $description = 'Bantuan penggunaan bot';

    public function handle()
    {
        $category = $this->getCategory();
        $botName = $category ? $category->name : 'Dracin Bot';
        $channelId = $category?->channel_id ?? '@dracin_hd';

        $message = "<b>Bantuan {$botName}</b>\n\n";
        $message .= "<b>Daftar Commands:</b>\n\n";
        $message .= "/start - Mulai bot\n";
        $message .= "/vip - Info paket VIP dan harga\n";
        $message .= "/help - Menampilkan bantuan ini\n\n";
        $message .= "<b>Cara Menonton:</b>\n";
        $message .= "1. Join channel {$channelId}\n";
        $message .= "2. Klik link part yang ingin ditonton\n";
        $message .= "3. Video akan dikirim ke chat ini\n\n";
        $message .= "<b>Akses VIP:</b>\n";
        $message .= "Dapatkan akses ke semua part video dengan upgrade VIP.\n";
        $message .= "Ketik /vip untuk info lebih lanjut.\n\n";
        $message .= "Butuh bantuan lebih? Hubungi admin.";

        $this->replyWithMessage([
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
    }
}
