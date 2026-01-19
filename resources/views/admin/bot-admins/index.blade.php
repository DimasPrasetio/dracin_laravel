@extends('layouts.app')

@section('title', 'Bot Staff Management')

@section('content')
<div class="animate-fade-in space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Bot Staff Management</h2>
            <p class="mt-1 text-sm text-gray-600">Kelola admin dan moderator bot per kategori</p>
        </div>
        <button onclick="showPermissionMatrix()" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-all duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Permission Matrix
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total Users</p>
                    <p id="stat-total-users" class="text-2xl font-bold text-blue-600 mt-1">-</p>
                </div>
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-rose-50 rounded-xl p-4 border border-red-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Admins</p>
                    <p id="stat-total-admins" class="text-2xl font-bold text-red-600 mt-1">-</p>
                </div>
                <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl p-4 border border-yellow-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Moderators</p>
                    <p id="stat-total-moderators" class="text-2xl font-bold text-yellow-600 mt-1">-</p>
                </div>
                <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">VIP Users</p>
                    <p id="stat-total-vip" class="text-2xl font-bold text-green-600 mt-1">-</p>
                </div>
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Bot Users</h3>
                    <p class="mt-1 text-sm text-gray-600">Semua bot user berasal dari tabel users (telegram_id)</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <select id="categoryFilter" class="w-64 px-4 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $selectedCategoryId == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <input type="text" id="search-input" placeholder="Search users..." class="w-64 px-4 py-2 pl-10 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full" id="users-table">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Telegram ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">User Info</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">VIP Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-tbody" class="bg-white divide-y divide-gray-100">
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600" id="pagination-info">
                    Showing 0 to 0 of 0 entries
                </div>
                <div class="flex items-center space-x-2" id="pagination-nav">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Permission Matrix Modal -->
<div id="permissionModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Permission Matrix</h3>
                    <button onclick="closePermissionModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-200 rounded-lg">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border border-gray-200">Permission</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border border-gray-200">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">User</span>
                                </th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border border-gray-200">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Moderator</span>
                                </th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border border-gray-200">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Admin</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 border border-gray-200">View Movies</td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-green-600">YES</span></td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-green-600">YES</span></td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-green-600">YES</span></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-700 border border-gray-200">Add Movies</td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-red-600">NO</span></td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-green-600">YES</span></td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-green-600">YES</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 border border-gray-200">Edit Movies</td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-red-600">NO</span></td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-red-600">NO</span></td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-green-600">YES</span></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-700 border border-gray-200">Delete Movies</td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-red-600">NO</span></td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-red-600">NO</span></td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-green-600">YES</span></td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 border border-gray-200">Manage VIP</td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-red-600">NO</span></td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-red-600">NO</span></td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-green-600">YES</span></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-700 border border-gray-200">Manage Users</td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-red-600">NO</span></td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-red-600">NO</span></td>
                                <td class="px-4 py-3 text-center border border-gray-200"><span class="text-green-600">YES</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Role Change Modal -->
<div id="roleChangeModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Change User Role</h3>
            </div>

            <div class="p-6">
                <p class="mb-4 text-sm text-gray-600">Select role for <strong id="roleChangeUserName"></strong>:</p>
                <input type="hidden" id="roleChangeUserId">

                <div class="space-y-3">
                    <label class="flex items-start p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors">
                        <input type="radio" name="newRole" value="user" class="mt-0.5 w-4 h-4 text-blue-600">
                        <div class="ml-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">User</span>
                        </div>
                    </label>

                    <label class="flex items-start p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-yellow-300 transition-colors">
                        <input type="radio" name="newRole" value="moderator" class="mt-0.5 w-4 h-4 text-yellow-600">
                        <div class="ml-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Moderator</span>
                        </div>
                    </label>

                    <label class="flex items-start p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-300 transition-colors">
                        <input type="radio" name="newRole" value="admin" class="mt-0.5 w-4 h-4 text-red-600">
                        <div class="ml-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Admin</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex gap-3">
                <button onclick="closeRoleModal()" class="flex-1 px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmRoleChange()" class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentPage = 1;
let currentQuery = '';
let currentPerPage = 10;

// Load statistics
function loadStats() {
    const params = new URLSearchParams({
        category_id: document.getElementById('categoryFilter').value
    });

    fetch(`/bot-admins/stats?${params}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('stat-total-users').textContent = data.total_users;
            document.getElementById('stat-total-admins').textContent = data.total_admins;
            document.getElementById('stat-total-moderators').textContent = data.total_moderators;
            document.getElementById('stat-total-vip').textContent = data.total_vip;
        })
        .catch(error => console.error('Error loading stats:', error));
}

// Load users data
function loadUsers(page = 1) {
    currentPage = page;
    const tbody = document.getElementById('users-tbody');

    tbody.innerHTML = `
        <tr>
            <td colspan="7" class="px-6 py-12 text-center">
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
            </td>
        </tr>
    `;

    const params = new URLSearchParams({
        page: page,
        per_page: currentPerPage,
        q: currentQuery,
        category_id: document.getElementById('categoryFilter').value
    });

    fetch(`/bot-admins/data?${params}`)
        .then(response => response.json())
        .then(data => {
            renderUsersTable(data);
            renderPagination(data);
            loadStats();
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-red-600">
                        Error loading data. Please try again.
                    </td>
                </tr>
            `;
        });
}

// Render users table
function renderUsersTable(data) {
    const tbody = document.getElementById('users-tbody');

    if (data.data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    No users found
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = data.data.map(user => {
        const roleBadge = getRoleBadge(user.role);
        const vipBadge = user.is_vip
            ? `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">VIP</span><br><span class="text-xs text-gray-500">${user.vip_until}</span>`
            : '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Regular</span>';

        return `
            <tr class="hover:bg-blue-50/50 transition-colors">
                <td class="px-6 py-4">
                    <code class="text-xs bg-gray-100 px-2 py-1 rounded">${user.telegram_user_id}</code>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-semibold text-gray-900">${user.full_name || 'N/A'}</div>
                    ${user.username ? `<div class="text-xs text-gray-500">@${user.username}</div>` : ''}
                </td>
                <td class="px-6 py-4">${roleBadge}</td>
                <td class="px-6 py-4">${vipBadge}</td>
                <td class="px-6 py-4">
                    ${user.email
                        ? `<div class="text-sm text-gray-900">${user.email}</div>`
                        : '<span class="text-sm text-gray-400">No email</span>'
                    }
                </td>
                <td class="px-6 py-4">
                    <span class="text-sm text-gray-600">${user.created_at}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-center space-x-2">
                        <button onclick='setRole(${user.id}, "${user.role}", "${user.full_name}")' class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Change Role">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Get role badge HTML
function getRoleBadge(role) {
    const badges = {
        'admin': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Admin</span>',
        'moderator': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Moderator</span>',
        'user': '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">User</span>'
    };
    return badges[role] || badges['user'];
}

// Render pagination
function renderPagination(data) {
    const nav = document.getElementById('pagination-nav');
    const info = document.getElementById('pagination-info');

    info.innerHTML = `Showing <span class="font-semibold text-gray-900">${data.from || 0}</span> to <span class="font-semibold text-gray-900">${data.to || 0}</span> of <span class="font-semibold text-gray-900">${data.total}</span> entries`;

    if (data.last_page <= 1) {
        nav.innerHTML = '';
        return;
    }

    let pages = '';
    const prevDisabled = data.current_page === 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer';
    const nextDisabled = data.current_page === data.last_page ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 cursor-pointer';

    pages += `
        <button onclick="loadUsers(${data.current_page - 1})" ${data.current_page === 1 ? 'disabled' : ''}
            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg ${prevDisabled} transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
    `;

    let startPage = Math.max(1, data.current_page - 2);
    let endPage = Math.min(data.last_page, data.current_page + 2);

    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === data.current_page;
        const buttonClass = isActive
            ? 'px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-md'
            : 'px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition-all duration-200';

        pages += `<button onclick="loadUsers(${i})" class="${buttonClass}">${i}</button>`;
    }

    pages += `
        <button onclick="loadUsers(${data.current_page + 1})" ${data.current_page === data.last_page ? 'disabled' : ''}
            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg ${nextDisabled} transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    `;

    nav.innerHTML = pages;
}

// Set role - open modal
function setRole(userId, currentRole, userName) {
    document.getElementById('roleChangeUserId').value = userId;
    document.getElementById('roleChangeUserName').textContent = userName;

    document.querySelectorAll('input[name="newRole"]').forEach(radio => {
        radio.checked = radio.value === currentRole;
    });

    document.getElementById('roleChangeModal').classList.remove('hidden');
}

// Confirm role change
function confirmRoleChange() {
    const userId = document.getElementById('roleChangeUserId').value;
    const selectedRole = document.querySelector('input[name="newRole"]:checked');
    const categoryId = document.getElementById('categoryFilter').value;

    if (!selectedRole) {
        showError('Please select a role.');
        return;
    }

    showLoading('Updating role...');

    fetch(`/bot-admins/${userId}/set-role`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ role: selectedRole.value, category_id: categoryId })
    })
    .then(response => response.json())
    .then(data => {
        closeLoading();
        if (data.success) {
            showSuccess(data.message);
            loadUsers(currentPage);
            closeRoleModal();
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        closeLoading();
        console.error('Error:', error);
        showError('An error occurred. Please try again.');
    });
}

// Modal functions
function showPermissionMatrix() {
    document.getElementById('permissionModal').classList.remove('hidden');
}

function closePermissionModal() {
    document.getElementById('permissionModal').classList.add('hidden');
}

function closeRoleModal() {
    document.getElementById('roleChangeModal').classList.add('hidden');
}

// Search functionality
document.getElementById('search-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        currentQuery = e.target.value;
        loadUsers(1);
    }
});

document.getElementById('categoryFilter').addEventListener('change', () => {
    loadUsers(1);
});

// Initial load
document.addEventListener('DOMContentLoaded', () => {
    loadStats();
    loadUsers(1);
});
</script>
@endpush
