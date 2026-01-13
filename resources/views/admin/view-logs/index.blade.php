@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                <i class="fas fa-chart-line text-blue-600"></i>
                View Analytics
            </h1>
            <p class="mt-2 text-sm text-gray-600">
                Track video views, user engagement, and viewing patterns
            </p>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                    <select id="periodFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="today">Today</option>
                        <option value="week" selected>This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                        <option value="all">All Time</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Movie</label>
                    <select id="movieFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Movies</option>
                        @foreach(\App\Models\Movie::orderBy('title')->get() as $movie)
                            <option value="{{ $movie->id }}">{{ $movie->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Content Type</label>
                    <select id="vipFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Content</option>
                        <option value="1">VIP Only</option>
                        <option value="0">Free Only</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button id="refreshBtn" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center gap-2">
                        <i class="fas fa-sync-alt"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div id="statsCards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Stats will be loaded here -->
            <div class="bg-white rounded-lg shadow-sm p-6 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                <div class="h-8 bg-gray-200 rounded w-1/2"></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                <div class="h-8 bg-gray-200 rounded w-1/2"></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                <div class="h-8 bg-gray-200 rounded w-1/2"></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                <div class="h-8 bg-gray-200 rounded w-1/2"></div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Timeline Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-area text-blue-600"></i>
                    Views Timeline
                </h3>
                <canvas id="timelineChart" height="200"></canvas>
            </div>

            <!-- Top Movies Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-green-600"></i>
                    Top 10 Movies
                </h3>
                <canvas id="topMoviesChart" height="200"></canvas>
            </div>
        </div>

        <!-- Recent Views Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-list text-purple-600"></i>
                    Recent Views
                </h3>
            </div>
            <div class="p-6">
                <div class="mb-4 flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="date" id="startDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Start Date">
                    </div>
                    <div class="flex-1">
                        <input type="date" id="endDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="End Date">
                    </div>
                    <div class="flex-1">
                        <select id="tableVipFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Types</option>
                            <option value="1">VIP</option>
                            <option value="0">Free</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table id="viewLogsTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Movie</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Viewed At</th>
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
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    let timelineChart, topMoviesChart;

    // Initialize DataTable
    const table = $('#viewLogsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("view-logs.data") }}',
            data: function(d) {
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
        order: [[5, 'desc']],
        pageLength: 25,
        language: {
            emptyTable: "No view logs found",
            zeroRecords: "No matching records found"
        }
    });

    // Filter change handlers for table
    $('#startDate, #endDate, #tableVipFilter').on('change', function() {
        table.ajax.reload();
    });

    // Load analytics data
    function loadAnalytics() {
        const period = $('#periodFilter').val();
        const movieId = $('#movieFilter').val();
        const isVip = $('#vipFilter').val();

        $.ajax({
            url: '{{ route("view-logs.analytics") }}',
            method: 'GET',
            data: { period, movie_id: movieId, is_vip: isVip },
            success: function(response) {
                if (response.success) {
                    renderStats(response.stats);
                    renderTimelineChart(response.timeline);
                    renderTopMoviesChart(response.top_movies);
                }
            },
            error: function() {
                alert('Failed to load analytics data');
            }
        });
    }

    // Render statistics cards
    function renderStats(stats) {
        const html = `
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Views</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">${stats.total_views.toLocaleString()}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-eye text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">VIP Views</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">${stats.vip_views.toLocaleString()}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-crown text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Free Views</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">${stats.free_views.toLocaleString()}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-unlock text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500 transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Unique Users</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">${stats.unique_users.toLocaleString()}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
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
    $('#periodFilter, #movieFilter, #vipFilter').on('change', loadAnalytics);
    $('#refreshBtn').on('click', loadAnalytics);

    // Initial load
    loadAnalytics();
});
</script>
@endpush
@endsection
