@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tambah VIP Package</h1>
        <p class="text-sm text-gray-500">Buat paket VIP baru untuk kategori.</p>
    </div>

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">
            Periksa kembali input Anda.
        </div>
    @endif

    <form method="POST" action="{{ route('vip-packages.store') }}" class="bg-white rounded-xl shadow-sm p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700">Kategori</label>
            <select name="category_id" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ (old('category_id', $selectedCategoryId) == $category->id) ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Kode Paket</label>
                <input name="code" value="{{ old('code') }}" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="contoh: 7days">
                @error('code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Paket</label>
                <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="VIP 7 Hari">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Durasi (hari)</label>
                <input type="number" min="1" name="duration_days" value="{{ old('duration_days') }}" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                @error('duration_days') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Harga</label>
                <input type="number" min="0" name="price" value="{{ old('price') }}" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                @error('price') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Urutan</label>
                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', 0) }}" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                @error('sort_order') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Badge (opsional)</label>
            <input name="badge" value="{{ old('badge') }}" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Hemat 10%">
            @error('badge') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
            <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
            @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="text-sm text-gray-700">Aktif</label>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                Simpan
            </button>
            <a href="{{ route('vip-packages.index', ['category_id' => $selectedCategoryId]) }}" class="text-sm text-gray-600 hover:text-gray-800">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
