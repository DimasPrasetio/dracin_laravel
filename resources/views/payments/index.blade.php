@extends('layouts.app')

@section('title', 'Transactions Management')

@section('content')
<div class="animate-fade-in space-y-6">
    <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Transactions Management</h2>
            <p class="mt-1 text-sm text-gray-600">Manage VIP payment transactions and subscriptions</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total Transactions</p>
                    <p id="total-transactions" class="text-2xl font-bold text-blue-600 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Paid</p>
                    <p id="paid-count" class="text-2xl font-bold text-green-600 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl p-4 border border-yellow-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Pending</p>
                    <p id="pending-count" class="text-2xl font-bold text-yellow-600 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-rose-50 rounded-xl p-4 border border-red-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Expired/Cancelled</p>
                    <p id="failed-count" class="text-2xl font-bold text-red-600 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Transaction List</h3>
                    <p class="mt-1 text-sm text-gray-600">Monitor VIP payment activity and status updates</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Search by transaction ID, username..."
                            class="w-64 px-4 py-2.5 pl-10 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <div class="relative">
                        <select id="status-filter" class="w-48 px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="expired">Expired</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="payments-table" class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Transaction ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Package</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100"></tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600" id="tableInfo">
                    Showing 0 to 0 of 0 transactions
                </div>
                <div class="flex items-center space-x-2" id="tablePagination"></div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div id="status-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4 overflow-y-auto">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl transform transition-all mx-auto">
            <div class="flex flex-col max-h-[90vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest">Transactions</p>
                            <h3 class="text-lg font-semibold text-gray-900">Update Payment Status</h3>
                        </div>
                        <button id="cancel-status-btn" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" type="button">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 px-6 py-4 overflow-y-auto space-y-4">
                    <input type="hidden" id="payment-id">

                    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest">Transaction Status</p>
                                <h4 class="text-base font-semibold text-gray-900">Select latest status</h4>
                            </div>
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-xs font-semibold text-blue-700">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Required
                            </span>
                        </div>
                        <div class="p-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                            <select id="payment-status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="expired">Expired</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4 text-sm text-blue-800 space-y-2">
                        <div class="flex items-center gap-2 font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Status Impact
                        </div>
                        <ul class="text-xs list-disc ml-5 space-y-1 text-blue-900/80">
                            <li>Status <strong>Paid</strong> activates VIP automatically.</li>
                            <li>Status <strong>Expired</strong>/<strong>Cancelled</strong> disables VIP access.</li>
                            <li>Verify payment before updating the status.</li>
                        </ul>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center gap-3">
                    <button id="cancel-status-btn-footer" class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-100 transition-all duration-200" type="button">
                        Cancel
                    </button>
                    <button id="save-status-btn" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all duration-200">
                        Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let currentPage = 1;
    let totalPages = 1;
    let searchQuery = '';
    let statusFilter = '';

    function loadPayments(page = 1) {
        $.ajax({
            url: '{{ route("payments.data") }}',
            method: 'GET',
            data: {
                page: page,
                per_page: 10,
                q: searchQuery,
                status: statusFilter
            },
            success: function(response) {
                currentPage = response.current_page;
                totalPages = response.last_page;

                updateStats(response.data);
                renderTable(response.data);
                updatePagination(response);
            },
            error: function() {
                showError('Failed to load transactions');
            }
        });
    }

    function updateStats(payments) {
        const total = payments.length;
        const paid = payments.filter(p => p.status === 'paid').length;
        const pending = payments.filter(p => p.status === 'pending').length;
        const failed = payments.filter(p => p.status === 'expired' || p.status === 'cancelled').length;

        $('#total-transactions').text(total);
        $('#paid-count').text(paid);
        $('#pending-count').text(pending);
        $('#failed-count').text(failed);
    }

    function renderTable(payments) {
        const tbody = $('#payments-table tbody');
        tbody.empty();

        if (payments.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">No transactions found</h3>
                            <p class="text-sm text-gray-500">Transactions will appear here when users make payments</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        payments.forEach(payment => {
            const user = payment.telegram_user;
            const statusBadge = getStatusBadge(payment.status);
            const amount = formatCurrency(payment.amount);
            const date = formatDate(payment.created_at);

            tbody.append(`
                <tr class="hover:bg-blue-50/50 transition-all duration-200 group">
                    <td class="px-6 py-4">
                        <span class="text-sm font-mono font-semibold text-gray-900">#${payment.id}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors">${user?.first_name || 'N/A'}</div>
                        <div class="text-sm text-gray-500 mt-0.5">@${user?.username || 'N/A'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-gray-900">${payment.package}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-bold text-gray-900">${amount}</span>
                    </td>
                    <td class="px-6 py-4">
                        ${statusBadge}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm text-gray-600">${date}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center">
                            <button onclick="openStatusModal(${payment.id}, '${payment.status}')"
                                class="p-2.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 hover:scale-110" title="Update Status">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    function getStatusBadge(status) {
        const badges = {
            pending: '<span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold border bg-yellow-100 text-yellow-800 border-yellow-200"><span class="w-2 h-2 rounded-full bg-yellow-500"></span>Pending</span>',
            paid: '<span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold border bg-green-100 text-green-800 border-green-200"><span class="w-2 h-2 rounded-full bg-green-500"></span>Paid</span>',
            expired: '<span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold border bg-gray-100 text-gray-800 border-gray-200"><span class="w-2 h-2 rounded-full bg-gray-500"></span>Expired</span>',
            cancelled: '<span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold border bg-red-100 text-red-800 border-red-200"><span class="w-2 h-2 rounded-full bg-red-500"></span>Cancelled</span>'
        };
        return badges[status] || badges.pending;
    }

    function formatCurrency(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function updatePagination(response) {
        const start = response.from || 0;
        const end = response.to || 0;
        const total = response.total || 0;

        $('#tableInfo').html(`Showing <span class="font-semibold text-gray-900">${start}</span> to <span class="font-semibold text-gray-900">${end}</span> of <span class="font-semibold text-gray-900">${total}</span> transactions`);

        const pagination = $('#tablePagination');
        pagination.empty();

        if (totalPages <= 1) return;

        const prevDisabled = currentPage === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer';
        const nextDisabled = currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer';

        pagination.append(`
            <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}
                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg ${prevDisabled} transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
        `);

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === currentPage;
            const buttonClass = isActive
                ? 'px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-md'
                : 'px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition-all duration-200';

            pagination.append(`<button onclick="changePage(${i})" class="${buttonClass}">${i}</button>`);
        }

        pagination.append(`
            <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}
                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg ${nextDisabled} transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        `);
    }

    window.changePage = function(page) {
        if (page < 1 || page > totalPages || page === currentPage) return;
        loadPayments(page);
    };

    window.openStatusModal = function(paymentId, currentStatus) {
        $('#payment-id').val(paymentId);
        $('#payment-status').val(currentStatus);
        $('#status-modal').removeClass('hidden');
    };

    $('#cancel-status-btn, #cancel-status-btn-footer, #status-modal').click(function(e) {
        if (e.target === this) {
            $('#status-modal').addClass('hidden');
        }
    });

    $('#save-status-btn').click(function() {
        const paymentId = $('#payment-id').val();
        const status = $('#payment-status').val();

        $.ajax({
            url: `/payments/${paymentId}/update-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: status
            },
            success: function(response) {
                if (response.ok) {
                    showSuccess(response.message);
                    $('#status-modal').addClass('hidden');
                    loadPayments(currentPage);
                } else {
                    showError(response.message);
                }
            },
            error: function() {
                showError('Failed to update payment status');
            }
        });
    });

    $('#search-input').on('keyup', debounce(function() {
        searchQuery = $(this).val();
        loadPayments(1);
    }, 500));

    $('#status-filter').change(function() {
        statusFilter = $(this).val();
        loadPayments(1);
    });

    function debounce(func, wait) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), wait);
        };
    }

    function showSuccess(message) {
        alert(message);
    }

    function showError(message) {
        alert(message);
    }

    loadPayments(1);
});
</script>
@endpush
@endsection