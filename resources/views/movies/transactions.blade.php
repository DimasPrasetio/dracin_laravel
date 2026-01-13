@extends('layouts.app')

@section('title', 'Movie Transactions')

@section('content')
<div class="animate-fade-in space-y-6">
    <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Movie Transactions</h2>
            <p class="mt-1 text-sm text-gray-600">Track revenue and engagement for each movie</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-500/10 border border-green-500/50 text-green-400 px-4 py-3 rounded-xl" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total Revenue</p>
                    <p id="total-revenue" class="text-2xl font-bold text-blue-600 mt-1">Rp 0</p>
                </div>
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">VIP Access</p>
                    <p id="vip-access" class="text-2xl font-bold text-green-600 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 border border-purple-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total Views</p>
                    <p id="total-views" class="text-2xl font-bold text-purple-600 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-xl p-4 border border-orange-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Active Movies</p>
                    <p id="active-movies" class="text-2xl font-bold text-orange-600 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Movies Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Movie Performance</h3>
                    <p class="mt-1 text-sm text-gray-600">Revenue and engagement metrics per movie</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search movies..." class="w-64 px-4 py-2 pl-10 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <div class="relative">
                        <select id="statusFilter" class="w-48 px-4 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                            <option value="">All Status</option>
                            <option value="posted">Posted</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="moviesTable" class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Movie</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">VIP Access</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Free Views</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Conversion Rate</th>
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
                    Showing 0 to 0 of 0 movies
                </div>
                <div class="flex items-center space-x-2" id="tablePagination">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Movie Details Modal -->
<div id="movieDetailsModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl transform transition-all">
            <div class="flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Movie Transaction Details</h3>
                        <button onclick="closeDetailsModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-4 overflow-y-auto">
                    <div id="movieDetailsContent">
                        <!-- Movie details will be loaded here -->
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    <div class="flex items-center justify-end">
                        <button onclick="closeDetailsModal()" class="px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-all duration-200">
                            Close
                        </button>
                    </div>
                </div>
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
    let statusFilter = '';

    function loadMovies() {
        $.ajax({
            url: '{{ route("movies.transactions.data") }}',
            method: 'GET',
            data: {
                page: currentPage,
                per_page: perPage,
                search: searchQuery,
                status: statusFilter
            },
            success: function(response) {
                renderTable(response.data);
                updatePagination(response);
                updateStats(response.stats);
            },
            error: function(xhr) {
                console.error('Error loading movies:', xhr);
                showError('Gagal memuat data film');
            }
        });
    }

    function updateStats(stats) {
        $('#total-revenue').text(formatCurrency(stats.total_revenue || 0));
        $('#vip-access').text(stats.total_vip_access || 0);
        $('#total-views').text(stats.total_views || 0);
        $('#active-movies').text(stats.active_movies || 0);
    }

    function renderTable(movies) {
        const tbody = $('#moviesTable tbody');
        tbody.empty();

        if (!movies || movies.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">No movies found</h3>
                            <p class="text-sm text-gray-500">Movies will appear here when they generate transactions</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        movies.forEach(function(movie) {
            const statusBadge = movie.channel_message_id
                ? '<span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border bg-green-100 text-green-800 border-green-200 shadow-sm">Posted</span>'
                : '<span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border bg-yellow-100 text-yellow-800 border-yellow-200 shadow-sm">Draft</span>';

            const conversionRate = movie.total_views > 0 ? ((movie.vip_access / movie.total_views) * 100).toFixed(1) : 0;

            const row = `
                <tr class="hover:bg-blue-50/50 transition-all duration-200 group">
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-200">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">${movie.title}</div>
                                <div class="text-xs text-gray-500 mt-0.5">${movie.total_parts} Parts</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        ${statusBadge}
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-bold text-gray-900">${formatCurrency(movie.revenue || 0)}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-green-600">${movie.vip_access || 0}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-purple-600">${movie.free_views || 0}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-blue-600">${conversionRate}%</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center space-x-2">
                            <button onclick="viewMovieDetails(${movie.id})" class="p-2.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 hover:scale-110" title="View Details">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
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
        totalPages = Math.ceil((response.total || 0) / perPage);

        const start = response.total > 0 ? ((currentPage - 1) * perPage) + 1 : 0;
        const end = Math.min(currentPage * perPage, response.total);
        const total = response.total;

        $('#tableInfo').html(`Showing <span class="font-semibold text-gray-900">${start}</span> to <span class="font-semibold text-gray-900">${end}</span> of <span class="font-semibold text-gray-900">${total}</span> movies`);

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
        loadMovies();
    };

    window.viewMovieDetails = function(movieId) {
        showLoading('Loading movie details...');

        $.ajax({
            url: `/movies/${movieId}/transactions`,
            method: 'GET',
            success: function(response) {
                closeLoading();
                showMovieDetailsModal(response);
            },
            error: function(xhr) {
                closeLoading();
                console.error('Error loading movie details:', xhr);
                showError('Failed to load movie details');
            }
        });
    };

    function showMovieDetailsModal(data) {
        const content = generateMovieDetailsContent(data);
        $('#movieDetailsContent').html(content);
        $('#movieDetailsModal').removeClass('hidden');
    }

    function generateMovieDetailsContent(data) {
        const movie = data.movie;
        const transactions = data.transactions || [];
        const stats = data.stats || {};

        let html = `
            <div class="space-y-6">
                <!-- Movie Header -->
                <div class="flex items-start space-x-4">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-md">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-xl font-bold text-gray-900">${movie.title}</h4>
                        <p class="text-sm text-gray-600">${movie.total_parts} parts • ${stats.total_views || 0} total views</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100">
                        <div class="text-sm text-gray-600 font-medium">Revenue</div>
                        <div class="text-2xl font-bold text-green-600 mt-1">${formatCurrency(stats.revenue || 0)}</div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
                        <div class="text-sm text-gray-600 font-medium">VIP Access</div>
                        <div class="text-2xl font-bold text-blue-600 mt-1">${stats.vip_access || 0}</div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 border border-purple-100">
                        <div class="text-sm text-gray-600 font-medium">Conversion Rate</div>
                        <div class="text-2xl font-bold text-purple-600 mt-1">${stats.conversion_rate || 0}%</div>
                    </div>
                </div>

                <!-- Parts Breakdown -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <h5 class="text-lg font-semibold text-gray-900 mb-4">Parts Performance</h5>
                    <div class="space-y-3">
        `;

        movie.video_parts.forEach(part => {
            const partStats = stats.parts ? stats.parts.find(p => p.part_number === part.part_number) : null;
            const views = partStats ? partStats.views : 0;
            const vipStatus = part.is_vip ? 'VIP' : 'Free';

            html += `
                <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <span class="text-sm font-medium text-gray-900">Part ${part.part_number}</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${part.is_vip ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'}">
                            ${vipStatus}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600">${views} views</div>
                </div>
            `;
        });

        html += `
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <h5 class="text-lg font-semibold text-gray-900 mb-4">Recent VIP Access</h5>
        `;

        if (transactions.length === 0) {
            html += `
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <p class="text-sm text-gray-500">No VIP access transactions yet</p>
                </div>
            `;
        } else {
            html += `<div class="space-y-2">`;
            transactions.slice(0, 10).forEach(transaction => {
                html += `
                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${transaction.user?.first_name || 'Unknown User'}</div>
                                <div class="text-xs text-gray-500">Part ${transaction.part_number} • ${formatDate(transaction.created_at)}</div>
                            </div>
                        </div>
                        <div class="text-sm font-semibold text-green-600">${formatCurrency(transaction.amount)}</div>
                    </div>
                `;
            });
            html += `</div>`;
        }

        html += `
                </div>
            </div>
        `;

        return html;
    }

    window.closeDetailsModal = function() {
        $('#movieDetailsModal').addClass('hidden');
    };

    // Close modal when clicking outside
    $('#movieDetailsModal').on('click', function(e) {
        if (e.target === this) {
            closeDetailsModal();
        }
    });

    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            searchQuery = $('#searchInput').val();
            currentPage = 1;
            loadMovies();
        }, 500);
    });

    $('#statusFilter').change(function() {
        statusFilter = $(this).val();
        currentPage = 1;
        loadMovies();
    });

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

    loadMovies();
});
</script>
@endpush
