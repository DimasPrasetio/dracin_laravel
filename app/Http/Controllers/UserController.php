<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            $u = User::findOrFail($id);
            return response()->json([
                'id' => $u->id,
                'username' => $u->username,
                'name' => $u->name,
                'phone' => $u->phone,
                'email' => $u->email,
                'role' => $u->role,
                'created_at' => $u->created_at?->toDateTimeString(),
            ]);
        }

        $q = $request->get('q');
        $perPage = (int)($request->get('per_page', 10));
        $users = User::query()
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
        ]);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
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
        ]);
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
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