<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Payment;
use App\Services\PaymentStatusService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentStatusService $paymentStatusService,
    ) {}

    public function index()
    {
        $user = auth()->user();
        $categories = $user ? $user->getAccessibleCategories() : collect();

        return view('payments.index', compact('categories'));
    }

    public function data(Request $request)
    {
        $user = $request->user();
        $categories = $user ? $user->getAccessibleCategories() : collect();
        $accessibleCategoryIds = $categories->pluck('id');
        $selectedCategoryId = $request->input('category_id');

        if ($user && !$user->isSuperAdmin()) {
            if (!$selectedCategoryId) {
                return response()->json([
                    'message' => 'Kategori wajib dipilih.',
                ], 422);
            }

            if (!$accessibleCategoryIds->contains((int) $selectedCategoryId)) {
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

        $query = Payment::with(['user', 'category'])
            ->orderBy('created_at', 'desc');

        // Search
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('username', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($selectedCategoryId && $selectedCategoryId !== 'all') {
            $query->where('category_id', $selectedCategoryId);
        } elseif ($user && !$user->isSuperAdmin()) {
            $query->whereIn('category_id', $accessibleCategoryIds);
        }

        $stats = [
            'total' => (clone $query)->count(),
            'paid' => (clone $query)->where('status', 'paid')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'failed' => (clone $query)->whereIn('status', ['expired', 'cancelled'])->count(),
        ];

        // Filter by status (for table data only)
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $total = (clone $query)->count();

        // Pagination
        $perPage = $request->per_page ?? 10;
        $page = $request->page ?? 1;

        $payments = $query->skip(($page - 1) * $perPage)
                          ->take($perPage)
                          ->get();

        return response()->json([
            'data' => $payments,
            'total' => $total,
            'stats' => $stats,
            'current_page' => (int) $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => (int) $perPage,
            'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
            'to' => min($page * $perPage, $total),
        ]);
    }

    public function updateStatus(Request $request, Payment $payment)
    {
        $user = $request->user();
        if ($user && !$user->isSuperAdmin()) {
            $accessibleCategoryIds = $user->getAccessibleCategories()->pluck('id');
            if (!$accessibleCategoryIds->contains((int) $payment->category_id)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Anda tidak memiliki akses ke transaksi ini.',
                ], 403);
            }
        }

        $request->validate([
            'status' => 'required|in:pending,paid,expired,cancelled',
        ]);

        try {
            $updatedPayment = $this->paymentStatusService->transition($payment, $request->status);

            return response()->json([
                'ok' => true,
                'message' => 'Payment status updated successfully!',
                'payment' => $updatedPayment->load('user'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to update payment status',
            ], 500);
        }
    }
}
