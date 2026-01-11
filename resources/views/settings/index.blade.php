@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Telegram Bot</h1>
        <p class="mt-1 text-sm text-gray-600">Konfigurasi bot dan channel settings</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST">
        @csrf

        <div class="bg-white rounded-xl shadow-sm p-6 space-y-6">
            <!-- Free Parts -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Part Gratis untuk User Basic</label>
                <input type="text" name="free_parts" value="{{ old('free_parts', $freeParts) }}"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">
                    Masukkan nomor part yang bisa diakses user basic, pisahkan dengan koma. Contoh: 1,3
                </p>
                @error('free_parts')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Channel Footer -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Footer Pesan Channel</label>
                <textarea name="channel_post_footer" rows="5"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ old('channel_post_footer', $channelFooter) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">
                    Pesan yang akan ditampilkan di bagian bawah setiap post film di channel
                </p>
                @error('channel_post_footer')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bot Info -->
            <div class="border-t pt-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">Informasi Bot</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Bot Username</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ env('TELE_BOT_USERNAME') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Channel ID</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ env('TELE_CHANNEL_ID') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Admin ID</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ env('TELE_ADMIN_ID') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Polling Command</dt>
                        <dd class="mt-1 text-sm text-gray-900"><code>php artisan telegram:polling</code></dd>
                    </div>
                </dl>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
