<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        return view('payments.index');
    }

    public function data(Request $request)
    {
        $query = Payment::with('telegramUser')
                       ->orderBy('created_at', 'desc');

        // Search
        if ($request->has('q') && $request->q) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('telegramUser', function($q) use ($search) {
                      $q->where('username', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $total = $query->count();

        // Pagination
        $perPage = $request->per_page ?? 10;
        $page = $request->page ?? 1;

        $payments = $query->skip(($page - 1) * $perPage)
                          ->take($perPage)
                          ->get();

        return response()->json([
            'data' => $payments,
            'total' => $total,
            'current_page' => (int) $page,
            'last_page' => ceil($total / $perPage),
            'per_page' => (int) $perPage,
            'from' => $total > 0 ? (($page - 1) * $perPage) + 1 : 0,
            'to' => min($page * $perPage, $total),
        ]);
    }

    public function updateStatus(Request $request, Payment $payment)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,expired,cancelled',
        ]);

        try {
            $updatedPayment = $this->paymentStatusService->transition($payment, $request->status);

            return response()->json([
                'ok' => true,
                'message' => 'Payment status updated successfully!',
                'payment' => $updatedPayment->load('telegramUser'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to update payment status',
            ], 500);
        }
    }
}
