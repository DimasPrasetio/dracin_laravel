<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index');
    }

    public function data(Request $request)
    {
        if ($id = $request->get('id')) {
            $u = User::with('linkedTelegramUser')->findOrFail($id);
            return response()->json([
                'id' => $u->id,
                'username' => $u->username,
                'name' => $u->name,
                'phone' => $u->phone,
                'email' => $u->email,
                'role' => $u->role,
                'telegram_user_id' => $u->linkedTelegramUser?->telegram_user_id,
                'telegram_username' => $u->linkedTelegramUser?->username,
                'created_at' => $u->created_at?->toDateTimeString(),
            ]);
        }

        $q = $request->get('q');
        $perPage = (int)($request->get('per_page', 10));
        $users = User::query()
            ->with('linkedTelegramUser')
            ->when($q, function ($qr) use ($q) {
                $qr->where(function ($qq) use ($q) {
                    $qq->where('username','like',"%{$q}%")
                       ->orWhere('name','like',"%{$q}%")
                       ->orWhere('email','like',"%{$q}%")
                       ->orWhere('phone','like',"%{$q}%")
                       ->orWhere('role','like',"%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->through(function ($u) {
                return [
                    'id' => $u->id,
                    'username' => $u->username,
                    'name' => $u->name,
                    'phone' => $u->phone,
                    'email' => $u->email,
                    'role' => $u->role,
                    'telegram_user_id' => $u->linkedTelegramUser?->telegram_user_id,
                    'telegram_username' => $u->linkedTelegramUser?->username,
                    'telegram_role' => $u->linkedTelegramUser?->role,
                    'created_at' => $u->created_at?->toDateTimeString(),
                ];
            });

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required','string','max:191','unique:users,username'],
            'name' => ['required','string','max:191'],
            'phone' => ['nullable','string','max:20'],
            'email' => ['required','email','max:191','unique:users,email'],
            'password' => ['required','string','min:6'],
            'role' => ['required','string','max:50'],
            'telegram_user_id' => ['nullable','integer','exists:telegram_users,telegram_user_id'],
        ]);
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        // Link to telegram user if provided
        if (!empty($data['telegram_user_id'])) {
            $telegramUser = TelegramUser::where('telegram_user_id', $data['telegram_user_id'])->first();
            if ($telegramUser) {
                $telegramUser->linkToUser($user);
            }
        }

        return response()->json(['ok'=>true,'id'=>$user->id]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username' => ['required','string','max:191',Rule::unique('users','username')->ignore($user->id)],
            'name' => ['required','string','max:191'],
            'phone' => ['nullable','string','max:20'],
            'email' => ['required','email','max:191',Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:6'],
            'role' => ['required','string','max:50'],
            'telegram_user_id' => ['nullable','integer','exists:telegram_users,telegram_user_id'],
        ]);
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Handle telegram user linking
        $oldTelegramUser = $user->linkedTelegramUser;
        $newTelegramUserId = $data['telegram_user_id'] ?? null;
        unset($data['telegram_user_id']);

        $user->update($data);

        // Update telegram linking if changed
        if ($oldTelegramUser && $oldTelegramUser->telegram_user_id != $newTelegramUserId) {
            // Unlink old telegram user
            $oldTelegramUser->unlinkFromUser();
        }

        if ($newTelegramUserId) {
            $telegramUser = TelegramUser::where('telegram_user_id', $newTelegramUserId)->first();
            if ($telegramUser) {
                $telegramUser->linkToUser($user);
            }
        }

        return response()->json(['ok'=>true]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['ok'=>true]);
    }

    public function export()
    {
        $users = User::orderBy('username')->get();

        $data = [
            'users' => $users,
            'total' => $users->count(),
            'generated_at' => now()->format('d M Y H:i:s'),
        ];

        $pdf = Pdf::loadView('users.export', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('users-report-' . now()->format('Ymd-His') . '.pdf');
    }
}