<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $freeParts = Setting::get('free_parts', '1');
        $channelFooter = Setting::get('channel_post_footer', '');

        return view('settings.index', compact('freeParts', 'channelFooter'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'free_parts' => 'required|string',
            'channel_post_footer' => 'nullable|string',
        ]);

        try {
            Setting::set('free_parts', $request->free_parts, 'Part numbers that are free for basic users (comma separated)');
            Setting::set('channel_post_footer', $request->channel_post_footer, 'Footer message for channel posts');

            return back()->with('success', 'Pengaturan berhasil disimpan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }
}
