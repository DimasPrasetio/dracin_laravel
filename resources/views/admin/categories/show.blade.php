@extends('layouts.app')

@section('title', 'Detail Kategori - ' . $category->name)

@section('content')
<div class="animate-fade-in space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.categories.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <div class="flex items-center space-x-3">
                    <h2 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h2>
                    @if($category->is_active)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Aktif
                    </span>
                    @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Nonaktif
                    </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-600">{{ $category->description ?? 'Tidak ada deskripsi' }}</p>
            </div>
        </div>
        @if(auth()->user()->isAdminForCategory($category->id))
        <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-all duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Kategori
        </a>
        @endif
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total Film</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['total_movies'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total Revenue</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl p-4 border border-yellow-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">VIP Aktif</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['active_vip'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-xl p-4 border border-purple-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total Views</p>
                    <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($stats['total_views']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bot Information -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Informasi Bot</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Bot Username</span>
                    <a href="https://t.me/{{ ltrim($category->bot_username, '@') }}" target="_blank" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                        {{ $category->bot_username }}
                    </a>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Channel</span>
                    <span class="text-sm font-medium text-gray-900">{{ $category->channel_id ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Webhook URL</span>
                    <code class="text-xs bg-gray-100 px-2 py-1 rounded max-w-[200px] truncate" title="{{ $category->webhook_url }}">{{ $category->webhook_url }}</code>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <span class="text-sm text-gray-600">Webhook Secret</span>
                    <div class="flex items-center space-x-2">
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ Str::limit($category->webhook_secret, 15) }}...</code>
                        @if(auth()->user()->isAdminForCategory($category->id))
                        <form action="{{ route('admin.categories.regenerate-webhook', $category) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" onclick="return confirm('Regenerate webhook secret? Bot perlu dikonfigurasi ulang.')" class="text-xs text-blue-600 hover:text-blue-800">
                                Regenerate
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <!-- Webhook Status Section -->
                @if(auth()->user()->isAdminForCategory($category->id))
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-gray-700">Status Webhook</span>
                        <button type="button" onclick="checkWebhookStatus()" class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                            <svg id="refreshIcon" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>

                    <div id="webhookStatusContainer" class="bg-gray-50 rounded-xl p-4">
                        <div id="webhookStatusLoading" class="flex items-center justify-center py-2">
                            <svg class="animate-spin h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-gray-500">Memeriksa status webhook...</span>
                        </div>

                        <div id="webhookStatusContent" class="hidden space-y-3">
                            <div class="flex items-center space-x-2">
                                <span id="webhookStatusBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"></span>
                                <span id="webhookStatusText" class="text-sm text-gray-600"></span>
                            </div>

                            <div id="webhookErrorInfo" class="hidden bg-red-50 border border-red-200 rounded-lg p-3">
                                <p class="text-xs font-medium text-red-800">Error Terakhir:</p>
                                <p id="webhookErrorMessage" class="text-xs text-red-600 mt-1"></p>
                                <p id="webhookErrorDate" class="text-xs text-red-400 mt-1"></p>
                            </div>

                            <div id="webhookPendingInfo" class="hidden text-xs text-gray-500">
                                <span id="webhookPendingCount"></span> update tertunda
                            </div>
                        </div>
                    </div>

                    <!-- Set Webhook Button -->
                    <div class="mt-4">
                        <form action="{{ route('admin.categories.set-webhook', $category) }}" method="POST" id="setWebhookForm">
                            @csrf
                            <button type="submit" id="setWebhookBtn"
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <span id="setWebhookBtnText">Set Webhook</span>
                            </button>
                        </form>
                        <p class="mt-2 text-xs text-gray-500 text-center">
                            Klik untuk mengkonfigurasi webhook ke Telegram API
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Category Admins -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Admin Kategori</h3>
                @if(auth()->user()->isAdminForCategory($category->id))
                <button onclick="showAddAdminModal()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    + Tambah Admin
                </button>
                @endif
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($categoryAdmins as $admin)
                @php
                    $telegramId = $admin->user ? $admin->user->telegram_id : null;
                @endphp
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-blue-600">
                                {{ strtoupper(substr($admin->display_name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $admin->display_name }}</p>
                            <p class="text-xs text-gray-500">
                                <span class="inline-flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    User
                                </span>
                                @if($telegramId)
                                    <span class="ml-2 text-xs text-gray-400">Telegram ID: {{ $telegramId }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if(auth()->user()->isAdminForCategory($category->id))
                            <!-- Role Dropdown -->
                            <form action="{{ route('admin.categories.update-admin-role', [$category, $admin]) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <select name="role" onchange="this.form.submit()"
                                    class="text-xs border-0 rounded-full font-medium cursor-pointer focus:ring-2 focus:ring-offset-1 {{ $admin->role === 'admin' ? 'bg-red-100 text-red-800 focus:ring-red-500' : 'bg-yellow-100 text-yellow-800 focus:ring-yellow-500' }}">
                                    <option value="admin" {{ $admin->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="moderator" {{ $admin->role === 'moderator' ? 'selected' : '' }}>Moderator</option>
                                </select>
                            </form>

                            <!-- Delete Button - Only show if not the last admin -->
                            @php
                                $isLastAdmin = $admin->role === 'admin' && $categoryAdmins->where('role', 'admin')->count() <= 1;
                            @endphp
                            @if(!$isLastAdmin)
                            <form action="{{ route('admin.categories.remove-admin', [$category, $admin]) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus {{ $admin->display_name }} dari kategori ini?')"
                                    class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Hapus dari kategori">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                            @else
                            <span class="p-1.5 text-gray-300 cursor-not-allowed" title="Tidak dapat menghapus admin terakhir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $admin->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $admin->role_display_name }}
                            </span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p>Belum ada admin untuk kategori ini</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('movies.index') }}?category={{ $category->id }}" class="flex items-center p-4 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors">
                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Lihat Film</p>
                    <p class="text-xs text-gray-500">Kelola film di kategori ini</p>
                </div>
            </a>

            <a href="{{ route('payments.index') }}?category={{ $category->id }}" class="flex items-center p-4 bg-green-50 rounded-xl hover:bg-green-100 transition-colors">
                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Lihat Transaksi</p>
                    <p class="text-xs text-gray-500">Riwayat pembayaran kategori ini</p>
                </div>
            </a>

            <a href="{{ $category->bot_link }}" target="_blank" class="flex items-center p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors">
                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.121l-6.869 4.326-2.96-.924c-.643-.203-.657-.643.136-.953l11.566-4.458c.538-.194 1.006.128.832.939z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Buka Bot</p>
                    <p class="text-xs text-gray-500">{{ $category->bot_username }}</p>
                </div>
            </a>
        </div>
    </div>
</div>

@if(auth()->user()->isAdminForCategory($category->id))
<!-- Add Admin Modal -->
<div id="addAdminModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4 overflow-y-auto">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl transform transition-all mx-auto">
            <div class="flex flex-col max-h-[90vh] overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest">Kategori {{ $category->name }}</p>
                            <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Tambah Admin Kategori</h3>
                        </div>
                        <button type="button" onclick="hideAddAdminModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <form id="addAdminForm" action="{{ route('admin.categories.add-admin', $category) }}" method="POST" class="flex flex-col flex-1 overflow-hidden">
                    @csrf
                    <div class="flex-1 px-6 py-4 overflow-y-auto space-y-4">

                        <!-- User Search Card -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900">Cari & Pilih User</h4>
                                </div>
                            </div>
                            <div class="p-4">
                                <!-- User Search -->
                                <div id="webUserSection">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih User</label>
                                    <input type="hidden" name="user_id" id="selectedUserId">

                                    <!-- Custom Searchable Select -->
                                    <div class="relative" id="webUserSelectContainer">
                                        <!-- Select Trigger -->
                                        <button type="button" id="webUserSelectTrigger" onclick="toggleWebUserDropdown()"
                                            class="w-full flex items-center justify-between px-4 py-3 text-sm bg-gray-50 border border-gray-200 rounded-xl hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                            <div class="flex items-center gap-3" id="webUserSelectDisplay">
                                                <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </div>
                                                <span class="text-gray-500">Pilih user...</span>
                                            </div>
                                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" id="webUserSelectArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        <!-- Dropdown Panel -->
                                        <div id="webUserDropdown" class="absolute z-20 w-full mt-2 bg-white border border-gray-200 rounded-xl shadow-xl hidden overflow-hidden">
                                            <!-- Search Input -->
                                            <div class="p-3 border-b border-gray-100 bg-gray-50">
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                        </svg>
                                                    </div>
                                                    <input type="text" id="webUserSearch" placeholder="Cari nama, email, atau telegram ID..."
                                                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                                        autocomplete="off">
                                                </div>
                                            </div>
                                            <!-- Results List -->
                                            <div id="webUserResults" class="max-h-52 overflow-y-auto">
                                                <div class="p-4 text-center text-sm text-gray-500">
                                                    <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                    </svg>
                                                    Ketik untuk mencari user
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Klik untuk membuka dropdown dan cari user.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Role Selection Card -->
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900">Pilih Role</h4>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="relative flex flex-col p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-red-300 transition-all duration-200 has-[:checked]:border-red-500 has-[:checked]:bg-red-50 has-[:checked]:shadow-md group">
                                        <input type="radio" name="role" value="admin" class="sr-only peer" checked>
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center group-hover:bg-red-200 transition-colors">
                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                                </svg>
                                            </div>
                                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-red-500 peer-checked:bg-red-500 flex items-center justify-center transition-all duration-200">
                                                <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-900">Admin</p>
                                    </label>
                                    <label class="relative flex flex-col p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-yellow-300 transition-all duration-200 has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-50 has-[:checked]:shadow-md group">
                                        <input type="radio" name="role" value="moderator" class="sr-only peer">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center group-hover:bg-yellow-200 transition-colors">
                                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </div>
                                            <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-yellow-500 peer-checked:bg-yellow-500 flex items-center justify-center transition-all duration-200">
                                                <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-900">Moderator</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center gap-3">
                        <button type="button" onclick="hideAddAdminModal()" class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-100 transition-all duration-200">
                            Batal
                        </button>
                        <button type="submit" id="submitBtn" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:from-gray-400 disabled:to-gray-500" disabled>
                            Tambah Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    const categoryId = {{ $category->id }};
    const availableUsersUrl = "{{ route('admin.categories.available-users', $category) }}";
    const webhookStatusUrl = "{{ route('admin.categories.webhook-status', $category) }}";
    let searchTimeout = null;
    let webDropdownOpen = false;

    // =====================
    // Webhook Status Functions
    // =====================
    async function checkWebhookStatus() {
        const loadingEl = document.getElementById('webhookStatusLoading');
        const contentEl = document.getElementById('webhookStatusContent');
        const refreshIcon = document.getElementById('refreshIcon');

        // Show loading
        loadingEl.classList.remove('hidden');
        contentEl.classList.add('hidden');
        refreshIcon.classList.add('animate-spin');

        try {
            const response = await fetch(webhookStatusUrl);
            const result = await response.json();

            if (result.success) {
                displayWebhookStatus(result.data);
            } else {
                displayWebhookError(result.error || 'Gagal memeriksa status');
            }
        } catch (error) {
            console.error('Error checking webhook status:', error);
            displayWebhookError('Tidak dapat terhubung ke server');
        } finally {
            loadingEl.classList.add('hidden');
            contentEl.classList.remove('hidden');
            refreshIcon.classList.remove('animate-spin');
        }
    }

    function displayWebhookStatus(data) {
        const badgeEl = document.getElementById('webhookStatusBadge');
        const textEl = document.getElementById('webhookStatusText');
        const errorInfoEl = document.getElementById('webhookErrorInfo');
        const errorMsgEl = document.getElementById('webhookErrorMessage');
        const errorDateEl = document.getElementById('webhookErrorDate');
        const pendingInfoEl = document.getElementById('webhookPendingInfo');
        const pendingCountEl = document.getElementById('webhookPendingCount');
        const setWebhookBtnText = document.getElementById('setWebhookBtnText');

        // Reset
        errorInfoEl.classList.add('hidden');
        pendingInfoEl.classList.add('hidden');

        if (data.is_configured && data.is_correct_url) {
            badgeEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
            badgeEl.textContent = 'Aktif';
            textEl.textContent = 'Webhook terkonfigurasi dengan benar';
            setWebhookBtnText.textContent = 'Perbarui Webhook';
        } else if (data.is_configured && !data.is_correct_url) {
            badgeEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
            badgeEl.textContent = 'URL Berbeda';
            textEl.textContent = 'Webhook aktif tapi URL berbeda';
            setWebhookBtnText.textContent = 'Perbaiki Webhook';
        } else {
            badgeEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
            badgeEl.textContent = 'Tidak Aktif';
            textEl.textContent = 'Webhook belum dikonfigurasi';
            setWebhookBtnText.textContent = 'Set Webhook';
        }

        // Show error info if exists
        if (data.last_error_message) {
            errorInfoEl.classList.remove('hidden');
            errorMsgEl.textContent = data.last_error_message;
            if (data.last_error_date) {
                const errorDate = new Date(data.last_error_date * 1000);
                errorDateEl.textContent = errorDate.toLocaleString('id-ID');
            }
        }

        // Show pending updates
        if (data.pending_update_count > 0) {
            pendingInfoEl.classList.remove('hidden');
            pendingCountEl.textContent = data.pending_update_count;
        }
    }

    function displayWebhookError(message) {
        const badgeEl = document.getElementById('webhookStatusBadge');
        const textEl = document.getElementById('webhookStatusText');

        badgeEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
        badgeEl.textContent = 'Error';
        textEl.textContent = message;
    }

    // Check webhook status on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('webhookStatusContainer')) {
            checkWebhookStatus();
        }
    });

    // Modal functions
    function showAddAdminModal() {
        document.getElementById('addAdminModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function hideAddAdminModal() {
        document.getElementById('addAdminModal').classList.add('hidden');
        document.body.style.overflow = '';
        resetForm();
    }

    function resetForm() {
        document.getElementById('addAdminForm').reset();
        clearWebUserSelection();
        closeWebUserDropdown();
    }

    // =====================
    // User Dropdown
    // =====================
    function toggleWebUserDropdown() {
        if (webDropdownOpen) {
            closeWebUserDropdown();
        } else {
            openWebUserDropdown();
        }
    }

    function openWebUserDropdown() {
        const dropdown = document.getElementById('webUserDropdown');
        const arrow = document.getElementById('webUserSelectArrow');
        const trigger = document.getElementById('webUserSelectTrigger');

        dropdown.classList.remove('hidden');
        arrow.classList.add('rotate-180');
        trigger.classList.add('ring-2', 'ring-blue-500', 'border-transparent');
        webDropdownOpen = true;

        // Focus search input
        setTimeout(() => {
            document.getElementById('webUserSearch').focus();
        }, 100);
    }

    function closeWebUserDropdown() {
        const dropdown = document.getElementById('webUserDropdown');
        const arrow = document.getElementById('webUserSelectArrow');
        const trigger = document.getElementById('webUserSelectTrigger');

        dropdown.classList.add('hidden');
        arrow.classList.remove('rotate-180');
        trigger.classList.remove('ring-2', 'ring-blue-500', 'border-transparent');
        webDropdownOpen = false;

        // Reset search
        document.getElementById('webUserSearch').value = '';
        resetWebUserResults();
    }

    function resetWebUserResults() {
        document.getElementById('webUserResults').innerHTML = `
            <div class="p-4 text-center text-sm text-gray-500">
                <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Ketik untuk mencari user
            </div>
        `;
    }

    // User Search
    document.getElementById('webUserSearch').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();

        if (query.length < 2) {
            resetWebUserResults();
            return;
        }

        // Show loading
        document.getElementById('webUserResults').innerHTML = `
            <div class="p-4 text-center text-sm text-gray-500">
                <svg class="w-6 h-6 mx-auto text-blue-500 animate-spin mb-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Mencari...
            </div>
        `;

        searchTimeout = setTimeout(() => searchWebUsers(query), 300);
    });

    async function searchWebUsers(query) {
        try {
            const response = await fetch(`${availableUsersUrl}?search=${encodeURIComponent(query)}`);
            const users = await response.json();
            displayWebUserResults(users);
        } catch (error) {
            console.error('Error searching users:', error);
            document.getElementById('webUserResults').innerHTML = `
                <div class="p-4 text-center text-sm text-red-500">
                    Gagal mencari user. Coba lagi.
                </div>
            `;
        }
    }

    function displayWebUserResults(users) {
        const container = document.getElementById('webUserResults');

        if (users.length === 0) {
            container.innerHTML = `
                <div class="p-4 text-center text-sm text-gray-500">
                    <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Tidak ada user ditemukan
                </div>
            `;
            return;
        }

        container.innerHTML = users.map(user => `
            <div class="px-4 py-3 hover:bg-blue-50 cursor-pointer flex items-center space-x-3 transition-all duration-150 border-b border-gray-50 last:border-b-0" onclick="selectWebUser(${user.id}, '${escapeHtml(user.name)}', '${escapeHtml(user.email || '')}')">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-sm">
                    <span class="text-sm font-bold text-white">${user.name.substring(0, 2).toUpperCase()}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">${escapeHtml(user.name)}</p>
                    <p class="text-xs text-gray-500 truncate">${escapeHtml(user.email || user.username || '')}</p>
                </div>
                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        `).join('');
    }

    function selectWebUser(id, name, email) {
        document.getElementById('selectedUserId').value = id;

        // Update trigger display
        const display = document.getElementById('webUserSelectDisplay');
        display.innerHTML = `
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-sm">
                <span class="text-xs font-bold text-white">${name.substring(0, 2).toUpperCase()}</span>
            </div>
            <div class="flex-1 min-w-0 text-left">
                <p class="text-sm font-semibold text-gray-900 truncate">${escapeHtml(name)}</p>
                <p class="text-xs text-gray-500 truncate">${escapeHtml(email)}</p>
            </div>
            <button type="button" onclick="event.stopPropagation(); clearWebUserSelection();" class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

        // Update trigger style to show selected
        const trigger = document.getElementById('webUserSelectTrigger');
        trigger.classList.add('bg-gradient-to-r', 'from-blue-50', 'to-indigo-50', 'border-blue-200');
        trigger.classList.remove('bg-gray-50');

        closeWebUserDropdown();
        updateSubmitButton();
    }

    function clearWebUserSelection() {
        document.getElementById('selectedUserId').value = '';

        // Reset trigger display
        const display = document.getElementById('webUserSelectDisplay');
        display.innerHTML = `
            <div class="w-8 h-8 bg-gray-200 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <span class="text-gray-500">Pilih user...</span>
        `;

        // Reset trigger style
        const trigger = document.getElementById('webUserSelectTrigger');
        trigger.classList.remove('bg-gradient-to-r', 'from-blue-50', 'to-indigo-50', 'border-blue-200');
        trigger.classList.add('bg-gray-50');

        document.getElementById('webUserSearch').value = '';
        resetWebUserResults();
        updateSubmitButton();
    }

    // Update submit button state
    function updateSubmitButton() {
        const userId = document.getElementById('selectedUserId').value;
        const submitBtn = document.getElementById('submitBtn');

        submitBtn.disabled = !userId;
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        // Close web user dropdown
        if (!e.target.closest('#webUserSelectContainer')) {
            if (webDropdownOpen) {
                closeWebUserDropdown();
            }
        }
    });

    // Close modal and dropdowns on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (webDropdownOpen) {
                closeWebUserDropdown();
            } else {
                hideAddAdminModal();
            }
        }
    });

    // Prevent dropdown search input from triggering modal close
    document.getElementById('webUserSearch').addEventListener('click', function(e) {
        e.stopPropagation();
    });

</script>
@endpush

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const message = '{{ session('success') }}';
        if (window.showSuccess) {
            window.showSuccess(message);
            return;
        }
        if (window.Swal) {
            window.Swal.fire({ icon: 'success', title: 'Success', text: message });
            return;
        }
        alert(message);
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const message = '{{ session('error') }}';
        if (window.showError) {
            window.showError(message);
            return;
        }
        if (window.Swal) {
            window.Swal.fire({ icon: 'error', title: 'Error', text: message });
            return;
        }
        alert(message);
    });
</script>
@endif
