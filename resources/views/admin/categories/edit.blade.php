@extends('layouts.app')

@section('title', 'Edit Kategori - ' . $category->name)

@section('content')
<div class="animate-fade-in space-y-6">
    <!-- Page Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.categories.show', $category) }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Edit Kategori</h2>
            <p class="mt-1 text-sm text-gray-600">{{ $category->name }}</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Info -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Informasi Dasar</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori *</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('name') border-red-500 @enderror"
                            placeholder="Contoh: Drama Korea">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug) }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('slug') border-red-500 @enderror {{ $category->slug === 'default' ? 'bg-gray-100' : '' }}"
                            placeholder="Contoh: drama-korea"
                            {{ $category->slug === 'default' ? 'readonly' : '' }}>
                        @if($category->slug === 'default')
                        <p class="mt-1 text-xs text-gray-500">Slug kategori default tidak dapat diubah</p>
                        @endif
                        @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Opsional)</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('description') border-red-500 @enderror"
                        placeholder="Deskripsi singkat tentang kategori ini">{{ old('description', $category->description) }}</textarea>
                    @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">Kategori Aktif</label>
                </div>
            </div>

            <!-- Bot Configuration -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Konfigurasi Bot Telegram</h3>

                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div class="text-sm text-yellow-700">
                            <p class="font-medium">Perhatian</p>
                            <p class="mt-1">Mengubah bot token akan menyebabkan bot sebelumnya tidak berfungsi. Pastikan Anda sudah mengkonfigurasi webhook untuk bot baru.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="bot_token" class="block text-sm font-medium text-gray-700 mb-2">Bot Token *</label>
                        <input type="text" name="bot_token" id="bot_token" value="{{ old('bot_token', $category->bot_token) }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 font-mono text-sm @error('bot_token') border-red-500 @enderror"
                            placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz">
                        @error('bot_token')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bot_username" class="block text-sm font-medium text-gray-700 mb-2">Bot Username *</label>
                        <input type="text" name="bot_username" id="bot_username" value="{{ old('bot_username', $category->bot_username) }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('bot_username') border-red-500 @enderror"
                            placeholder="@nama_bot">
                        @error('bot_username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="channel_id" class="block text-sm font-medium text-gray-700 mb-2">Channel ID (Opsional)</label>
                    <input type="text" name="channel_id" id="channel_id" value="{{ old('channel_id', $category->channel_id) }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('channel_id') border-red-500 @enderror"
                        placeholder="@nama_channel atau -100xxxxxxxxxx">
                    <p class="mt-1 text-xs text-gray-500">Channel untuk posting film. Bot harus menjadi admin di channel.</p>
                    @error('channel_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Webhook Info -->
            <div class="bg-gray-50 rounded-xl p-4">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Informasi Webhook</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Webhook URL:</span>
                        <code class="bg-white px-2 py-1 rounded text-xs max-w-xs truncate" title="{{ $category->webhook_url }}">{{ $category->webhook_url }}</code>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Webhook Secret:</span>
                        <code class="bg-white px-2 py-1 rounded text-xs">{{ Str::limit($category->webhook_secret, 20) }}...</code>
                    </div>
                </div>

                <!-- Webhook Status & Actions -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-gray-700">Status Webhook</span>
                        <button type="button" onclick="checkWebhookStatus()" class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                            <svg id="refreshIcon" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh
                        </button>
                    </div>

                    <div id="webhookStatusContainer" class="bg-white rounded-lg p-3 border border-gray-200">
                        <div id="webhookStatusLoading" class="flex items-center justify-center py-2">
                            <svg class="animate-spin h-4 w-4 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-xs text-gray-500">Memeriksa...</span>
                        </div>

                        <div id="webhookStatusContent" class="hidden">
                            <div class="flex items-center space-x-2">
                                <span id="webhookStatusBadge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"></span>
                                <span id="webhookStatusText" class="text-xs text-gray-600"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <a href="{{ route('admin.categories.show', $category) }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Set Webhook Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Konfigurasi Webhook</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Setelah menyimpan perubahan bot token, klik tombol di bawah untuk mengkonfigurasi webhook ke Telegram API.
                    </p>
                    <form action="{{ route('admin.categories.set-webhook', $category) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" id="setWebhookBtn"
                            class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-200 shadow-sm hover:shadow-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Set Webhook Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    @if(auth()->user()->isSuperAdmin() && $category->slug !== 'default')
    <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-red-800 mb-2">Zona Berbahaya</h3>
        <p class="text-sm text-red-600 mb-4">Tindakan berikut tidak dapat dibatalkan. Harap berhati-hati.</p>
        <form id="delete-category-form" action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="button" id="open-delete-modal"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                Hapus Kategori
            </button>
        </form>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-category-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="delete-category-title">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-lg">
            <h4 id="delete-category-title" class="text-lg font-semibold text-gray-900">Hapus Kategori</h4>
            <p class="mt-2 text-sm text-gray-600">Aksi ini tidak dapat dibatalkan.</p>
            <div class="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-700">
                Konfirmasi akan tersedia dalam <span id="delete-countdown" class="font-semibold">5</span> detik.
            </div>

            <div id="delete-action-buttons" class="mt-6 hidden items-center justify-end space-x-3">
                <button type="button" id="cancel-delete-button"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
                <button type="submit" form="delete-category-form" id="confirm-delete-button"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const webhookStatusUrl = "{{ route('admin.categories.webhook-status', $category) }}";

    // Webhook Status Functions
    async function checkWebhookStatus() {
        const loadingEl = document.getElementById('webhookStatusLoading');
        const contentEl = document.getElementById('webhookStatusContent');
        const refreshIcon = document.getElementById('refreshIcon');

        if (!loadingEl || !contentEl) return;

        loadingEl.classList.remove('hidden');
        contentEl.classList.add('hidden');
        if (refreshIcon) refreshIcon.classList.add('animate-spin');

        try {
            const response = await fetch(webhookStatusUrl);
            const result = await response.json();

            if (result.success) {
                displayWebhookStatus(result.data);
            } else {
                displayWebhookError(result.error || 'Gagal memeriksa status');
            }
        } catch (error) {
            displayWebhookError('Tidak dapat terhubung');
        } finally {
            loadingEl.classList.add('hidden');
            contentEl.classList.remove('hidden');
            if (refreshIcon) refreshIcon.classList.remove('animate-spin');
        }
    }

    function displayWebhookStatus(data) {
        const badgeEl = document.getElementById('webhookStatusBadge');
        const textEl = document.getElementById('webhookStatusText');

        if (data.is_configured && data.is_correct_url) {
            badgeEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
            badgeEl.textContent = 'Aktif';
            textEl.textContent = 'Webhook terkonfigurasi';
        } else if (data.is_configured && !data.is_correct_url) {
            badgeEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800';
            badgeEl.textContent = 'URL Berbeda';
            textEl.textContent = 'Perlu update';
        } else {
            badgeEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
            badgeEl.textContent = 'Tidak Aktif';
            textEl.textContent = 'Belum dikonfigurasi';
        }
    }

    function displayWebhookError(message) {
        const badgeEl = document.getElementById('webhookStatusBadge');
        const textEl = document.getElementById('webhookStatusText');

        badgeEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
        badgeEl.textContent = 'Error';
        textEl.textContent = message;
    }

    // Check on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('webhookStatusContainer')) {
            checkWebhookStatus();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const openButton = document.getElementById('open-delete-modal');
        const modal = document.getElementById('delete-category-modal');
        const countdown = document.getElementById('delete-countdown');
        const actionButtons = document.getElementById('delete-action-buttons');
        const cancelButton = document.getElementById('cancel-delete-button');

        if (!openButton || !modal || !countdown || !actionButtons || !cancelButton) {
            return;
        }

        const initialSeconds = 5;
        let remaining = initialSeconds;
        let timerId = null;

        const resetCountdown = () => {
            remaining = initialSeconds;
            countdown.textContent = remaining;
            actionButtons.classList.add('hidden');
        };

        const startCountdown = () => {
            timerId = window.setInterval(() => {
                remaining -= 1;
                countdown.textContent = remaining;
                if (remaining <= 0) {
                    window.clearInterval(timerId);
                    timerId = null;
                    actionButtons.classList.remove('hidden');
                }
            }, 1000);
        };

        const openModal = () => {
            resetCountdown();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            startCountdown();
        };

        const closeModal = () => {
            if (timerId) {
                window.clearInterval(timerId);
                timerId = null;
            }
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        openButton.addEventListener('click', openModal);
        cancelButton.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });
    });
</script>
@endpush
