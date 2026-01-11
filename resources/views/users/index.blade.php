@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="animate-fade-in space-y-6">
    <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
        <div>
            <h2 class="text-3xl font-bold text-white">User Management</h2>
            <p class="mt-1 text-sm text-white/80">Manage and monitor all system users</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="exportData()" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-all duration-200 shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export
            </button>
            <button onclick="showAddModal()" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add User
            </button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">All Users</h3>
                    <p class="mt-1 text-sm text-gray-600">A list of all users in the system</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search users..." class="w-64 px-4 py-2 pl-10 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
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
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Created At</th>
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

<div id="userModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4 overflow-y-auto">
        <div class="relative w-full max-w-2xl md:max-w-3xl lg:max-w-4xl bg-white rounded-2xl shadow-2xl transform transition-all mx-auto">
            <div class="flex flex-col max-h-[90vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Add User</h3>
                        <button onclick="closeModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <form id="userForm" class="flex flex-col flex-1 overflow-hidden">
                    <input type="hidden" id="userId">

                    <div class="flex-1 px-6 py-4 overflow-y-auto">
                        <div class="space-y-5">
                            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between px-4 py-3 border-b border-gray-100">
                                    <div>
                                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest">Langkah 1</p>
                                        <h4 class="text-base font-semibold text-gray-900">Informasi Identitas</h4>
                                        <p class="text-xs text-gray-500">Data singkat untuk audit aktivitas admin.</p>
                                    </div>
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-xs font-semibold text-blue-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Wajib diisi
                                    </span>
                                </div>
                                <div class="p-4 sm:p-5">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-blue-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                            </span>
                                            <input type="text" id="username" required class="w-full pl-12 pr-4 py-3 text-sm bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-inner">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Digunakan untuk login internal panel admin.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-blue-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c2.21 0 4-1.79 4-4S14.21 3 12 3 8 4.79 8 7s1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path>
                                                </svg>
                                            </span>
                                            <input type="text" id="name" required class="w-full pl-12 pr-4 py-3 text-sm bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-inner">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Nama lengkap akan muncul pada log aktivitas.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-blue-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12H8m8 0L8 6m8 6l-8 6"></path>
                                                </svg>
                                            </span>
                                            <input type="email" id="email" required class="w-full pl-12 pr-4 py-3 text-sm bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-inner">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Digunakan untuk notifikasi penting & reset akun.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-blue-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h2l3 7-1.34 2.68a2 2 0 001.8 2.92H17"></path>
                                                </svg>
                                            </span>
                                            <input type="text" id="phone" class="w-full pl-12 pr-4 py-3 text-sm bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-inner" placeholder="+62">
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Opsional - memudahkan koordinasi cepat via WhatsApp.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between px-4 py-3 border-b border-gray-100">
                                    <div>
                                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest">Langkah 2</p>
                                        <h4 class="text-base font-semibold text-gray-900">Keamanan & Hak Akses</h4>
                                        <p class="text-xs text-gray-500">Atur kata sandi yang kuat dan tentukan role.</p>
                                    </div>
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-xs font-semibold text-blue-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.105 0 2-.672 2-1.5S13.105 8 12 8s-2 .672-2 1.5.895 1.5 2 1.5zM6 19v-2a4 4 0 014-4h4a4 4 0 014 4v2"></path>
                                        </svg>
                                        Rahasiakan kredensial
                                    </span>
                                </div>
                                <div class="p-4 sm:p-5">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            Password <span id="passwordRequired" class="text-red-500">*</span>
                                            <span id="passwordOptional" class="text-gray-400 text-xs hidden">(Optional)</span>
                                        </label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-blue-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.57 3-3.5S13.657 4 12 4s-3 1.57-3 3.5S10.343 11 12 11zm0 0v9m-7-4h14"></path>
                                                </svg>
                                            </span>
                                            <input type="password" id="password" class="w-full pl-12 pr-14 py-3 text-sm bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-inner">
                                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 px-4 text-xs font-semibold text-blue-600 hover:text-blue-700">
                                                Show
                                            </button>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Gunakan kombinasi huruf besar, angka, dan simbol.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-blue-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </span>
                                            <select id="role" required class="w-full appearance-none pl-12 pr-10 py-3 text-sm bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-inner">
                                                <option value="">Pilih Role</option>
                                                <option value="admin">Admin</option>
                                                <option value="user">User</option>
                                            </select>
                                            <svg class="w-4 h-4 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Role menentukan akses menu dan jenis notifikasi.</p>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-1 gap-3 text-xs text-gray-600 sm:grid-cols-2">
                                    <div class="flex items-start gap-2 p-3 rounded-2xl bg-blue-50 border border-blue-100">
                                        <svg class="w-4 h-4 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                                        </svg>
                                        <p><strong>Admin</strong> memiliki akses penuh ke pengaturan film, pembayaran, telegram users, dan semua fitur manajemen.</p>
                                    </div>
                                    <div class="flex items-start gap-2 p-3 rounded-2xl bg-gray-50 border border-gray-200">
                                        <svg class="w-4 h-4 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <p><strong>User</strong> memiliki akses terbatas, hanya bisa melihat dashboard dan data yang telah ditentukan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 bg-white">
                        <div class="flex items-center space-x-3">
                            <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-all duration-200">
                                Cancel
                            </button>
                            <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                Save
                            </button>
                        </div>
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
    let isEdit = false;

    $('#togglePassword').on('click', function() {
        const passwordField = $('#password');
        const isPassword = passwordField.attr('type') === 'password';
        passwordField.attr('type', isPassword ? 'text' : 'password');
        $(this).text(isPassword ? 'Hide' : 'Show');
    });

    function loadUsers() {
        $.ajax({
            url: '{{ route("users.data") }}',
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
                showError('Failed to load users. Please try again.');
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">No users found</h3>
                            <p class="text-sm text-gray-500">Try adjusting your search or filter criteria</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        users.forEach(function(user) {
            const initial = user.name ? user.name.substring(0, 2).toUpperCase() : 'NA';
            const colors = [
                'from-blue-500 to-indigo-600',
                'from-purple-500 to-pink-600',
                'from-green-500 to-teal-600',
                'from-orange-500 to-red-600',
                'from-cyan-500 to-blue-600',
                'from-pink-500 to-rose-600'
            ];
            const color = colors[user.id % colors.length];

            const roleColors = {
                'admin': 'bg-blue-100 text-blue-800 border-blue-200',
                'user': 'bg-gray-100 text-gray-800 border-gray-200'
            };
            const roleColor = roleColors[user.role?.toLowerCase()] || 'bg-gray-100 text-gray-800 border-gray-200';

            const createdDate = user.created_at ? new Date(user.created_at).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }) : '-';

            const row = `
                <tr class="hover:bg-blue-50/50 transition-all duration-200 group">
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br ${color} flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-200">
                                    <span class="text-base font-bold text-white">${initial}</span>
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white"></div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">${user.name || '-'}</div>
                                <div class="text-sm text-gray-500 flex items-center mt-0.5">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    ${user.email || '-'}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-sm font-medium text-gray-700">${user.username || '-'}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        ${user.phone ? `
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span class="text-sm text-gray-700">${user.phone}</span>
                            </div>
                        ` : '<span class="text-sm text-gray-400 italic">No phone</span>'}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border ${roleColor} shadow-sm">
                            ${user.role || '-'}
                        </span>
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
                        <div class="flex items-center justify-center space-x-2">
                            <button onclick="editUser(${user.id})" class="p-2.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 hover:scale-110" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="deleteUser(${user.id})" class="p-2.5 text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200 hover:scale-110" title="Delete">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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

        if (startPage > 1) {
            pagination.append(`<button onclick="changePage(1)" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition-all duration-200">1</button>`);
            if (startPage > 2) {
                pagination.append(`<span class="px-2 text-gray-500">...</span>`);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === currentPage;
            const buttonClass = isActive 
                ? 'px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-md'
                : 'px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition-all duration-200';
            
            pagination.append(`<button onclick="changePage(${i})" class="${buttonClass}">${i}</button>`);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                pagination.append(`<span class="px-2 text-gray-500">...</span>`);
            }
            pagination.append(`<button onclick="changePage(${totalPages})" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition-all duration-200">${totalPages}</button>`);
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

    window.showAddModal = function() {
        isEdit = false;
        $('#modalTitle').text('Add User');
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('#password').prop('required', true);
        $('#password').attr('type', 'password');
        $('#togglePassword').text('Show');
        $('#passwordRequired').removeClass('hidden');
        $('#passwordOptional').addClass('hidden');
        $('#userModal').removeClass('hidden');
    };

    window.editUser = function(id) {
        isEdit = true;
        $('#modalTitle').text('Edit User');
        $('#password').prop('required', false);
        $('#passwordRequired').addClass('hidden');
        $('#passwordOptional').removeClass('hidden');
        
        $.ajax({
            url: '{{ route("users.data") }}',
            method: 'GET',
            data: { id: id },
            success: function(data) {
                $('#userId').val(data.id);
                $('#username').val(data.username);
                $('#name').val(data.name);
                $('#email').val(data.email);
                $('#phone').val(data.phone || '');
                $('#role').val(data.role);
                $('#password').val('');
                $('#password').attr('type', 'password');
                $('#togglePassword').text('Show');
                $('#userModal').removeClass('hidden');
            },
            error: function(xhr) {
                console.error('Error fetching user:', xhr);
                showError('Failed to load user data. Please try again.');
            }
        });
    };

    window.deleteUser = function(id) {
        showConfirm('You won\'t be able to revert this!', 'Are you sure?').then((result) => {
            if (result.isConfirmed) {
                showLoading('Deleting user...');
                
                $.ajax({
                    url: `/users/${id}`,
                    method: 'DELETE',
                    success: function(response) {
                        closeLoading();
                        if (response.ok) {
                            loadUsers();
                            showSuccess('User has been deleted successfully!', 'Deleted!');
                        }
                    },
                    error: function(xhr) {
                        closeLoading();
                        console.error('Error deleting user:', xhr);
                        showError('Failed to delete user. Please try again.');
                    }
                });
            }
        });
    };

    window.closeModal = function() {
        $('#userModal').addClass('hidden');
        $('#userForm')[0].reset();
        $('#password').attr('type', 'password');
        $('#togglePassword').text('Show');
    };

    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            username: $('#username').val().trim(),
            name: $('#name').val().trim(),
            email: $('#email').val().trim(),
            phone: $('#phone').val().trim(),
            role: $('#role').val()
        };

        const password = $('#password').val();
        if (password) {
            formData.password = password;
        }
        
        const userId = $('#userId').val();
        const url = isEdit ? `/users/${userId}` : '{{ route("users.store") }}';
        const method = isEdit ? 'PUT' : 'POST';
        
        showLoading(isEdit ? 'Updating user...' : 'Creating user...');
        
        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                closeLoading();
                if (response.ok) {
                    closeModal();
                    loadUsers();
                    showSuccess(
                        isEdit ? 'User has been updated successfully!' : 'User has been created successfully!',
                        'Success!'
                    );
                }
            },
            error: function(xhr) {
                closeLoading();
                console.error('Error saving user:', xhr);
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let errorMsg = '';
                        for (let field in errors) {
                            errorMsg += errors[field].join('<br>') + '<br>';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: errorMsg,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        showError('Validation error occurred. Please check your inputs.');
                    }
                } else {
                    showError('Failed to save user. Please try again.');
                }
            }
        });
    });

    window.exportData = function() {
        window.open("{{ route('users.export') }}", '_blank');
        showToast('Generating PDF...', 'success');
    };

    loadUsers();
});
</script>
@endpush
