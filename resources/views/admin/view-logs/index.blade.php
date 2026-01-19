@extends('layouts.app')

@section('content')
<div class="animate-fade-in space-y-6">
    <!-- Header -->
    <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">View Analytics</h2>
            <p class="mt-1 text-sm text-gray-600">Track video views, user engagement, and viewing patterns</p>
        </div>
        <button id="refreshBtn" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-all duration-200 shadow-sm">
            <i class="fas fa-sync-alt mr-2"></i>
            Refresh
        </button>
    </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                    <select id="periodFilter" class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <option value="today">Today</option>
                        <option value="week" selected>This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                        <option value="all">All Time</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="categoryFilter" data-super-admin="{{ auth()->user()?->isSuperAdmin() ? '1' : '0' }}" class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" {{ $categories->isEmpty() ? 'disabled' : '' }}>
                        @if(auth()->user()?->isSuperAdmin())
                            <option value="all" selected>All Categories</option>
                        @else
                            <option value="" disabled selected>Select category</option>
                        @endif
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @if($categories->isEmpty())
                        <p class="mt-2 text-xs text-red-500">No accessible categories.</p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Movie</label>
                    <select id="movieFilter" class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <option value="">All Movies</option>
                        @foreach($movies as $movie)
                            <option value="{{ $movie->id }}" data-category-id="{{ $movie->category_id }}">{{ $movie->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Content Type</label>
                    <select id="vipFilter" class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <option value="">All Content</option>
                        <option value="1">VIP Only</option>
                        <option value="0">Free Only</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div id="statsCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Stats will be loaded here -->
            <div class="bg-white rounded-xl border border-gray-100 p-4 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                <div class="h-7 bg-gray-200 rounded w-1/2"></div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                <div class="h-7 bg-gray-200 rounded w-1/2"></div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                <div class="h-7 bg-gray-200 rounded w-1/2"></div>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-4 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                <div class="h-7 bg-gray-200 rounded w-1/2"></div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Timeline Chart -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-80">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-chart-area text-blue-600"></i>
                            Views Timeline
                        </h3>
                        <p class="mt-1 text-xs text-gray-500">Trend views per periode yang dipilih</p>
                    </div>
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-xs font-semibold text-blue-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18M3 6h18M3 18h18"></path>
                        </svg>
                        Timeline
                    </span>
                </div>
                <div class="mt-4 h-52 rounded-xl bg-gray-50/70 p-3">
                    <canvas id="timelineChart" class="h-full w-full"></canvas>
                </div>
            </div>

            <!-- Top Movies Chart -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 h-80">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-chart-bar text-green-600"></i>
                            Top 10 Movies
                        </h3>
                        <p class="mt-1 text-xs text-gray-500">Ranking film dengan views tertinggi</p>
                    </div>
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-50 text-xs font-semibold text-green-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        Ranking
                    </span>
                </div>
                <div class="mt-4 h-52 rounded-xl bg-gray-50/70 p-3">
                    <canvas id="topMoviesChart" class="h-full w-full"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Views Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-list text-purple-600"></i>
                    Recent Views
                </h3>
            </div>
            <div class="p-6">
                <div class="mb-4 flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <select id="tableCategoryFilter" class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" {{ $categories->isEmpty() ? 'disabled' : '' }}>
                            @if(auth()->user()?->isSuperAdmin())
                                <option value="all" selected>All Categories</option>
                            @else
                                <option value="" disabled selected>Select category</option>
                            @endif
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <input type="date" id="startDate" class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" placeholder="Start Date">
                    </div>
                    <div class="flex-1">
                        <input type="date" id="endDate" class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" placeholder="End Date">
                    </div>
                    <div class="flex-1">
                        <select id="tableVipFilter" class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                            <option value="">All Types</option>
                            <option value="1">VIP</option>
                            <option value="0">Free</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table id="viewLogsTable" class="w-full">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Movie</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Part</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Source</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Viewed At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Data will be loaded via DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<style>
    #viewLogsTable {
        border-collapse: separate;
        border-spacing: 0;
    }
    #viewLogsTable tbody tr {
        transition: background-color 0.2s ease;
    }
    #viewLogsTable tbody tr:hover {
        background-color: rgba(59, 130, 246, 0.08);
    }
    .dataTables_wrapper .dataTables_info {
        color: #4b5563;
        padding-top: 1rem;
        font-size: 0.875rem;
    }
    .dataTables_wrapper .dataTables_paginate {
        padding-top: 0.75rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid #d1d5db !important;
        background: #ffffff !important;
        color: #374151 !important;
        border-radius: 0.5rem !important;
        padding: 0.45rem 0.75rem !important;
        margin-left: 0.25rem !important;
        margin-right: 0.25rem !important;
        font-size: 0.875rem;
        transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: linear-gradient(to right, #2563eb, #4f46e5) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 6px 15px rgba(37, 99, 235, 0.25);
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f3f4f6 !important;
        color: #111827 !important;
        border-color: #d1d5db !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        opacity: 0.5;
        cursor: not-allowed !important;
        background: #ffffff !important;
    }
    .dataTables_wrapper .dataTables_processing {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        color: #111827;
        padding: 0.75rem 1rem;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    let timelineChart, topMoviesChart;
    const categoryFilter = $('#categoryFilter');
    const tableCategoryFilter = $('#tableCategoryFilter');
    const isSuperAdmin = categoryFilter.data('super-admin') === 1 || categoryFilter.data('super-admin') === '1';

    if (!isSuperAdmin) {
        const firstCategory = categoryFilter.find('option:not([disabled])').first().val();
        if (firstCategory) {
            categoryFilter.val(firstCategory);
            tableCategoryFilter.val(firstCategory);
        }
    }

    function syncMovieOptions() {
        const selectedCategory = categoryFilter.val();
        const movieOptions = $('#movieFilter option');
        let hasVisibleSelection = false;

        movieOptions.each(function() {
            const option = $(this);
            const optionCategory = option.data('category-id');

            if (!optionCategory || selectedCategory === 'all' || !selectedCategory) {
                option.show();
            } else if (String(optionCategory) === String(selectedCategory)) {
                option.show();
            } else {
                option.hide();
            }

            if (option.is(':selected') && option.is(':visible')) {
                hasVisibleSelection = true;
            }
        });

        if (!hasVisibleSelection) {
            $('#movieFilter').val('');
        }
    }

    // Initialize DataTable
    const table = $('#viewLogsTable').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        lengthChange: false,
        dom: 'rtip',
        pagingType: 'simple_numbers',
        ajax: {
            url: '{{ route("view-logs.data") }}',
            data: function(d) {
                d.category_id = $('#tableCategoryFilter').val();
                d.start_date = $('#startDate').val();
                d.end_date = $('#endDate').val();
                d.is_vip = $('#tableVipFilter').val();
            }
        },
        columns: [
            {
                data: 'user',
                render: function(data) {
                    if (!data) return '<span class="text-gray-400">Unknown</span>';
                    return `
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold">
                                ${data.name ? data.name.charAt(0).toUpperCase() : 'U'}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${data.name || 'Unknown'}</div>
                                <div class="text-xs text-gray-500">@${data.username || data.id}</div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'category',
                render: function(data) {
                    return data ? `<span class="text-sm text-gray-900">${data.name}</span>` : '<span class="text-gray-400">Unknown</span>';
                }
            },
            {
                data: 'movie',
                render: function(data) {
                    return data ? `<span class="text-sm text-gray-900">${data.title}</span>` : '<span class="text-gray-400">Unknown</span>';
                }
            },
            {
                data: 'part_number',
                render: function(data) {
                    return data ? `<span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Part ${data}</span>` : '-';
                }
            },
            {
                data: 'is_vip',
                render: function(data) {
                    return data
                        ? '<span class="px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 rounded-full flex items-center gap-1 w-fit"><i class="fas fa-crown"></i> VIP</span>'
                        : '<span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full flex items-center gap-1 w-fit"><i class="fas fa-unlock"></i> Free</span>';
                }
            },
            {
                data: 'source',
                render: function(data, type, row) {
                    const icon = row.device === 'telegram' ? 'fa-paper-plane' : 'fa-desktop';
                    return `<span class="text-xs text-gray-600"><i class="fab ${icon}"></i> ${data || 'bot'}</span>`;
                }
            },
            {
                data: 'created_at_human',
                render: function(data, type, row) {
                    return `<span class="text-sm text-gray-600" title="${row.created_at}">${data}</span>`;
                }
            }
        ],
        order: [[6, 'desc']],
        pageLength: 25,
                        language: {
                            emptyTable: "No view logs found",
                            zeroRecords: "No matching records found",
                            info: "Showing _START_ to _END_ of _TOTAL_ views",
                            infoEmpty: "Showing 0 to 0 of 0 views",
                            paginate: {
                                previous: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>',
                                next: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>'
                            }
                        }
    });

    // Filter change handlers for table
    $('#tableCategoryFilter, #startDate, #endDate, #tableVipFilter').on('change', function() {
        table.ajax.reload();
    });

    tableCategoryFilter.on('change', function() {
        if (categoryFilter.val() !== $(this).val()) {
            categoryFilter.val($(this).val());
            syncMovieOptions();
            loadAnalytics();
        }
    });

    categoryFilter.on('change', function() {
        tableCategoryFilter.val($(this).val());
        syncMovieOptions();
        loadAnalytics();
        table.ajax.reload();
    });

    // Load analytics data
    function loadAnalytics() {
        const period = $('#periodFilter').val();
        const categoryId = $('#categoryFilter').val();
        const movieId = $('#movieFilter').val();
        const isVip = $('#vipFilter').val();

        if (categoryFilter.is(':disabled')) {
            return;
        }

        $.ajax({
            url: '{{ route("view-logs.analytics") }}',
            method: 'GET',
            data: { period, category_id: categoryId, movie_id: movieId, is_vip: isVip },
            success: function(response) {
                if (response.success) {
                    renderStats(response.stats);
                    renderTimelineChart(response.timeline);
                    renderTopMoviesChart(response.top_movies);
                }
            },
            error: function() {
                if (window.showError) {
                    window.showError('Failed to load analytics data');
                    return;
                }
                if (window.Swal) {
                    window.Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load analytics data' });
                    return;
                }
                alert('Failed to load analytics data');
            }
        });
    }

    // Render statistics cards
    function renderStats(stats) {
        const html = `
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Total Views</p>
                        <p class="text-2xl font-bold text-blue-600 mt-1">${stats.total_views.toLocaleString()}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            <circle cx="12" cy="12" r="3" stroke-width="2"></circle>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl p-4 border border-yellow-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">VIP Views</p>
                        <p class="text-2xl font-bold text-yellow-600 mt-1">${stats.vip_views.toLocaleString()}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l2.2 4.5 5 .7-3.6 3.5.9 5-4.5-2.4L7.5 16.7l.9-5L4.8 8.2l5-.7L12 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Free Views</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">${stats.free_views.toLocaleString()}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15l3.5-3.5a3 3 0 00-4.2-4.2l-1.3 1.3-1.3-1.3a3 3 0 10-4.2 4.2L12 15z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 border border-purple-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 font-medium">Unique Users</p>
                        <p class="text-2xl font-bold text-purple-600 mt-1">${stats.unique_users.toLocaleString()}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20v-2a4 4 0 00-3-3.87M7 20v-2a4 4 0 013-3.87m8-2.13a4 4 0 11-8 0 4 4 0 018 0zM6 10a4 4 0 118 0 4 4 0 01-8 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        `;
        $('#statsCards').html(html);
    }

    // Render timeline chart
    function renderTimelineChart(timeline) {
        const ctx = document.getElementById('timelineChart').getContext('2d');

        if (timelineChart) {
            timelineChart.destroy();
        }

        timelineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: timeline.map(t => t.date),
                datasets: [
                    {
                        label: 'Total Views',
                        data: timeline.map(t => t.views),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'VIP Views',
                        data: timeline.map(t => t.vip_views),
                        borderColor: 'rgb(234, 179, 8)',
                        backgroundColor: 'rgba(234, 179, 8, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Free Views',
                        data: timeline.map(t => t.free_views),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Render top movies chart
    function renderTopMoviesChart(topMovies) {
        const ctx = document.getElementById('topMoviesChart').getContext('2d');

        if (topMoviesChart) {
            topMoviesChart.destroy();
        }

        topMoviesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: topMovies.map(m => m.title.length > 20 ? m.title.substring(0, 20) + '...' : m.title),
                datasets: [{
                    label: 'Views',
                    data: topMovies.map(m => m.views),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Event listeners
    $('#periodFilter, #categoryFilter, #movieFilter, #vipFilter').on('change', loadAnalytics);
    $('#refreshBtn').on('click', loadAnalytics);

    // Initial load
    syncMovieOptions();
    loadAnalytics();
});
</script>
@endpush
@endsection
