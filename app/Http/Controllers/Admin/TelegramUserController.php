<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use Illuminate\Http\Request;

class TelegramUserController extends Controller
{
    public function index()
    {
        return view('telegram_users.index');
    }

    public function data(Request $request)
    {
        $query = TelegramUser::query();

        // Search
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('telegram_user_id', 'like', "%{$search}%");
            });
        }

        // Sort by VIP status (VIP users first, then by vip_until desc)
        $query->orderByRaw('CASE WHEN vip_until > NOW() THEN 0 ELSE 1 END')
              ->orderBy('vip_until', 'desc')
              ->orderBy('created_at', 'desc');

        $total = $query->count();

        // Pagination
        $perPage = $request->per_page ?? 10;
        $page = $request->page ?? 1;

        $users = $query->skip(($page - 1) * $perPage)
                       ->take($perPage)
                       ->get();

        return response()->json([
            'data' => $users,
            'total' => $total,
            'current_page' => (int) $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => (int) $perPage,
            'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
            'to' => min($page * $perPage, $total),
        ]);
    }

    public function updateVip(Request $request, TelegramUser $telegramUser)
    {
        $request->validate([
            'vip_days' => 'required|integer|min:1',
        ]);

        try {
            $vipDays = (int) $request->vip_days;

            // Extend VIP if user already has VIP, otherwise start from now
            $vipUntil = $telegramUser->isVip()
                ? $telegramUser->vip_until->copy()->addDays($vipDays)
                : now()->addDays($vipDays);

            $telegramUser->update(['vip_until' => $vipUntil]);

            return response()->json([
                'ok' => true,
                'message' => 'VIP status updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to update VIP status',
            ], 500);
        }
    }
}
