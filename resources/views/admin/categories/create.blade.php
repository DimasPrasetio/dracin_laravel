@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
<div class="animate-fade-in space-y-6">
    <!-- Page Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('admin.categories.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Tambah Kategori Baru</h2>
            <p class="mt-1 text-sm text-gray-600">Buat kategori baru dengan bot Telegram terpisah</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.categories.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Basic Info -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Informasi Dasar</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('name') border-red-500 @enderror"
                            placeholder="Contoh: Drama Korea">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug (Opsional)</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('slug') border-red-500 @enderror"
                            placeholder="Contoh: drama-korea (auto-generate jika kosong)">
                        @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi (Opsional)</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('description') border-red-500 @enderror"
                        placeholder="Deskripsi singkat tentang kategori ini">{{ old('description') }}</textarea>
                    @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Bot Configuration -->
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Konfigurasi Bot Telegram</h3>

                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium">Cara mendapatkan Bot Token:</p>
                            <ol class="mt-1 list-decimal list-inside space-y-1">
                                <li>Buka @BotFather di Telegram</li>
                                <li>Kirim /newbot dan ikuti instruksi</li>
                                <li>Copy token yang diberikan</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="bot_token" class="block text-sm font-medium text-gray-700 mb-2">Bot Token *</label>
                        <input type="text" name="bot_token" id="bot_token" value="{{ old('bot_token') }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 font-mono text-sm @error('bot_token') border-red-500 @enderror"
                            placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz">
                        @error('bot_token')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bot_username" class="block text-sm font-medium text-gray-700 mb-2">Bot Username *</label>
                        <input type="text" name="bot_username" id="bot_username" value="{{ old('bot_username') }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('bot_username') border-red-500 @enderror"
                            placeholder="@nama_bot">
                        @error('bot_username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="channel_id" class="block text-sm font-medium text-gray-700 mb-2">Channel ID (Opsional)</label>
                    <input type="text" name="channel_id" id="channel_id" value="{{ old('channel_id') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('channel_id') border-red-500 @enderror"
                        placeholder="@nama_channel atau -100xxxxxxxxxx">
                    <p class="mt-1 text-xs text-gray-500">Channel untuk posting film. Bot harus menjadi admin di channel.</p>
                    @error('channel_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <a href="{{ route('admin.categories.index') }}" class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const slugField = document.getElementById('slug');
    if (!slugField.value) {
        slugField.value = this.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
});
</script>
@endsection
