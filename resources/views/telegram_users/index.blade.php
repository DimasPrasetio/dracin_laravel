@extends('layouts.app')

@section('title', 'Telegram Users')

@section('content')
<div class="animate-fade-in space-y-6">
    <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Telegram Users</h2>
            <p class="mt-1 text-sm text-gray-600">Daftar user yang menggunakan layanan bot (VIP users ditampilkan pertama)</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">All Telegram Users</h3>
                    <p class="mt-1 text-sm text-gray-600">User yang pernah menggunakan bot @dracin_indo_bot</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Cari user..." class="w-64 px-4 py-2 pl-10 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="usersTable" class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Telegram ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">VIP Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">VIP Until</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Joined At</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600" id="tableInfo">
                    Showing 0 to 0 of 0 users
                </div>
                <div class="flex items-center space-x-2" id="tablePagination">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update VIP Modal -->
<div id="vipModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4 overflow-y-auto">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl transform transition-all mx-auto">
            <div class="flex flex-col max-h-[90vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest">VIP Management</p>
                            <h3 class="text-lg font-semibold text-gray-900">Update VIP Status</h3>
                        </div>
                        <button onclick="closeVipModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <form id="vipForm" class="flex flex-col flex-1 overflow-hidden">
                    <input type="hidden" id="userId">

                    <div class="flex-1 px-6 py-4 overflow-y-auto space-y-4">
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest">Durasi VIP</p>
                                    <h4 class="text-base font-semibold text-gray-900">Perpanjang masa aktif</h4>
                                </div>
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-xs font-semibold text-blue-700">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 8v.01M21 12A9 9 0 113 12a9 9 0 0118 0z"></path>
                                    </svg>
                                    Required
                                </span>
                            </div>
                            <div class="p-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">VIP Duration (days)</label>
                                <div class="flex items-center gap-3">
                                    <input type="number" id="vipDays" required min="1" value="30" class="w-full px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-widest">days</span>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Masukkan jumlah hari untuk extend VIP; sistem otomatis menambahkan dari tanggal VIP saat ini.</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4 text-sm text-blue-800 space-y-2">
                            <div class="flex items-center gap-2 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Dampak Perubahan VIP
                            </div>
                            <ul class="text-xs list-disc ml-5 space-y-1 text-blue-900/80">
                                <li>User langsung berpindah ke urutan VIP di bot.</li>
                                <li>Bot mengirim notifikasi otomatis ke pengguna.</li>
                                <li>Sisakan minimal 1 hari agar tidak terjadi overlap masa aktif.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center gap-3">
                        <button type="button" onclick="closeVipModal()" class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-100 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            Update VIP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    'use strict';

    let currentPage = 1;
    let totalPages = 1;
    let perPage = 10;
    let searchQuery = '';

    function loadUsers() {
        $.ajax({
            url: '{{ route("telegram-users.data") }}',
            method: 'GET',
            data: {
                q: searchQuery,
                per_page: perPage,
                page: currentPage
            },
            success: function(response) {
                renderTable(response.data);
                updatePagination(response);
            },
            error: function(xhr) {
                console.error('Error loading users:', xhr);
                showError('Gagal memuat data user');
            }
        });
    }

    function renderTable(users) {
        const tbody = $('#usersTable tbody');
        tbody.empty();

        if (!users || users.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Belum ada user</h3>
                            <p class="text-sm text-gray-500">User akan muncul ketika mereka menggunakan bot</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        users.forEach(function(user) {
            const fullName = (user.first_name || '') + ' ' + (user.last_name || '');
            const initial = fullName.trim().substring(0, 2).toUpperCase() || 'U';
            const vipUntil = user.vip_until ? new Date(user.vip_until) : null;
            const isVip = vipUntil && vipUntil > new Date();

            const colors = [
                'from-blue-500 to-indigo-600',
                'from-purple-500 to-pink-600',
                'from-green-500 to-teal-600',
                'from-orange-500 to-red-600',
            ];
            const color = colors[user.id % colors.length];

            const vipBadge = isVip
                ? '<span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border bg-gradient-to-r from-yellow-400 to-orange-500 text-white border-yellow-300 shadow-md">💎 VIP</span>'
                : '<span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border bg-gray-100 text-gray-800 border-gray-200">Basic</span>';

            const vipUntilText = vipUntil
                ? new Date(user.vip_until).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })
                : '-';

            const createdDate = new Date(user.created_at).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });

            const row = `
                <tr class="hover:bg-blue-50/50 transition-all duration-200 group">
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br ${color} flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-200">
                                    <span class="text-base font-bold text-white">${initial}</span>
                                </div>
                                ${isVip ? '<div class="absolute -bottom-1 -right-1 w-4 h-4 bg-yellow-400 rounded-full border-2 border-white"></div>' : ''}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">${fullName.trim() || 'Unknown'}</div>
                                <div class="text-xs text-gray-500 mt-0.5">@${user.username || 'No username'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-mono text-gray-700">${user.telegram_user_id}</span>
                    </td>
                    <td class="px-6 py-4">
                        ${vipBadge}
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm ${isVip ? 'text-yellow-600 font-semibold' : 'text-gray-400'}">${vipUntilText}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm text-gray-600">${createdDate}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center">
                            <button onclick="updateVip(${user.id})" class="p-2.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-all duration-200 hover:scale-110" title="Update VIP">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    function updatePagination(response) {
        totalPages = response.last_page;
        currentPage = response.current_page;

        const start = response.from || 0;
        const end = response.to || 0;
        const total = response.total || 0;

        $('#tableInfo').html(`Showing <span class="font-semibold text-gray-900">${start}</span> to <span class="font-semibold text-gray-900">${end}</span> of <span class="font-semibold text-gray-900">${total}</span> users`);

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
        currentPage = page;
        loadUsers();
    };

    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            searchQuery = $('#searchInput').val();
            currentPage = 1;
            loadUsers();
        }, 500);
    });

    window.updateVip = function(id) {
        $('#userId').val(id);
        $('#vipModal').removeClass('hidden');
    };

    window.closeVipModal = function() {
        $('#vipModal').addClass('hidden');
        $('#vipForm')[0].reset();
    };

    $('#vipForm').on('submit', function(e) {
        e.preventDefault();

        const userId = $('#userId').val();
        const vipDays = $('#vipDays').val();

        showLoading('Updating VIP status...');

        $.ajax({
            url: `/telegram-users/${userId}/update-vip`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { vip_days: vipDays },
            success: function(response) {
                closeLoading();
                if (response.ok) {
                    closeVipModal();
                    loadUsers();
                    showSuccess('VIP status updated successfully!', 'Success!');
                }
            },
            error: function(xhr) {
                closeLoading();
                console.error('Error updating VIP:', xhr);
                showError('Gagal mengupdate VIP status');
            }
        });
    });

    loadUsers();
});
</script>
@endpush
