@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">VIP Packages</h1>
            <p class="text-sm text-gray-500">Kelola paket VIP per kategori.</p>
        </div>
        <a href="{{ route('vip-packages.create', ['category_id' => $selectedCategoryId]) }}"
           class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
            + Tambah Paket
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form method="GET" class="flex flex-col md:flex-row md:items-end gap-3">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700">Kategori</label>
                <select name="category_id" class="mt-1 w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $selectedCategoryId == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition">
                    Tampilkan
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Kode</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Durasi</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Harga</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Urutan</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($packages as $package)
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $package->code }}</td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-900">{{ $package->name }}</div>
                            @if($package->badge)
                                <div class="text-xs text-blue-600">{{ $package->badge }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $package->duration_days }} hari</td>
                        <td class="px-4 py-3 text-gray-700">Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @if($package->is_active)
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $package->sort_order }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('vip-packages.edit', $package) }}"
                               class="inline-flex items-center px-3 py-1 text-xs font-semibold text-blue-600 hover:text-blue-800">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('vip-packages.destroy', $package) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1 text-xs font-semibold text-red-600 hover:text-red-800"
                                        onclick="return confirm('Hapus paket ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                            Belum ada paket untuk kategori ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
