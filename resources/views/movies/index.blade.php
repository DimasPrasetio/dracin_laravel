@extends('layouts.app')

@section('title', 'Film Management')

@section('content')
<div class="animate-fade-in space-y-6">
    <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Film Management</h2>
            <p class="mt-1 text-sm text-gray-600">Kelola semua film yang akan dipost ke channel Telegram</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="showAddModal()" class="inline-flex items-center px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Tambah Film
            </button>
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

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Semua Film</h3>
                    <p class="mt-1 text-sm text-gray-600">Daftar film yang dipost di channel @dracin_hd</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Cari film..." class="w-64 px-4 py-2 pl-10 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="moviesTable" class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Film</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Total Part</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Free Parts</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Created At</th>
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
                    Showing 0 to 0 of 0 films
                </div>
                <div class="flex items-center space-x-2" id="tablePagination">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="movieModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4 overflow-y-auto">
        <div class="relative w-full max-w-2xl md:max-w-3xl lg:max-w-4xl bg-white rounded-2xl shadow-2xl transform transition-all mx-auto">
            <div class="flex flex-col max-h-[90vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Tambah Film</h3>
                        <button onclick="closeModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <form id="movieForm" enctype="multipart/form-data" class="flex flex-col flex-1 overflow-hidden">
                    <input type="hidden" id="movieId">
                    <input type="hidden" id="isEdit" value="0">

                    <div class="flex-1 px-6 py-4 overflow-y-auto">
                        <div class="space-y-5">
                            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between px-4 py-3 border-b border-gray-100">
                                    <div>
                                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest">Langkah 1</p>
                                        <h4 class="text-base font-semibold text-gray-900">Informasi Dasar Film</h4>
                                        <p class="text-xs text-gray-500">Isi seperlunya lalu lanjut unggah.</p>
                                    </div>
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-xs font-semibold text-blue-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18M3 6h18M3 18h18"></path>
                                        </svg>
                                        Detail
                                    </span>
                                </div>
                                <div class="p-4 sm:p-5">
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Film <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-blue-50 text-blue-500">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 01-8 0m8 0v10a4 4 0 01-4 4 4 4 0 01-4-4V7m8 0a4 4 0 00-8 0m8 0H6"></path>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <input type="text" id="title" name="title" required class="w-full pl-14 pr-4 py-3 text-sm bg-white border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                            </div>
                                            <p class="mt-2 text-xs text-gray-500">Judul ini akan tampil di channel dan riwayat transaksi.</p>
                                        </div>
                                        <div id="totalPartsField" class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                            <div class="flex items-center justify-between">
                                                <label class="text-sm font-semibold text-gray-700">Jumlah Part <span class="text-red-500">*</span></label>
                                                <span class="text-xs text-gray-400 font-medium">1 - 50 part</span>
                                            </div>
                                            <div class="flex items-center gap-3 mt-3">
                                                <button type="button" id="partsDecrease" class="w-10 h-10 flex items-center justify-center rounded-xl border border-gray-200 text-gray-600 hover:border-blue-300 hover:text-blue-600 transition-all duration-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                    </svg>
                                                </button>
                                                <input type="number" id="totalParts" name="total_parts" required min="1" max="50" value="1" class="flex-1 text-center text-xl font-semibold text-gray-900 bg-white border border-gray-200 rounded-xl py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <button type="button" id="partsIncrease" class="w-10 h-10 flex items-center justify-center rounded-xl border border-gray-200 text-gray-600 hover:border-blue-300 hover:text-blue-600 transition-all duration-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <p class="mt-2 text-xs text-gray-500">Sistem akan membuat kolom unggah sesuai angka ini.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="thumbnailField" class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between px-4 py-3 border-b border-gray-100">
                                    <div>
                                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest">Langkah 2</p>
                                        <h4 class="text-base font-semibold text-gray-900">Poster / Thumbnail Film</h4>
                                    </div>
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-xs font-semibold text-blue-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4-4-4-4m8 8l4-4-4-4m8 8l4-4-4-4"></path>
                                        </svg>
                                        Step 2
                                    </span>
                                </div>
                                <div class="p-4 sm:p-5">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Poster Film <span id="thumbnailRequired" class="text-red-500">*</span>
                                        <span id="thumbnailOptional" class="text-gray-400 text-xs hidden">(Optional - kosongkan jika tidak ingin mengubah)</span>
                                    </label>
                                    <input type="file" id="thumbnail" name="thumbnail" accept="image/*" class="sr-only">
                                    <div id="thumbnailDropZone" class="relative border-2 border-dashed border-gray-200 rounded-2xl p-4 text-center cursor-pointer transition-all duration-200 hover:border-blue-400 hover:bg-blue-50/40">
                                        <div id="thumbnailPlaceholder" class="flex flex-col items-center gap-2 pointer-events-none">
                                            <div class="p-3 rounded-2xl bg-blue-50 text-blue-600">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4zm0 6l9 4 9-4"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">Tarik file atau klik untuk unggah</p>
                                                <p class="text-xs text-gray-500">Format JPG/PNG, minimal 1080px, maksimal 10MB</p>
                                            </div>
                                        </div>
                                        <div id="thumbnailPreview" class="hidden flex flex-col items-center gap-3 pointer-events-none">
                                            <img id="thumbnailPreviewImage" src="" alt="Thumbnail preview" class="max-h-48 rounded-xl object-cover shadow-lg ring-2 ring-blue-100">
                                            <div class="text-center">
                                                <p id="thumbnailFileName" class="text-sm font-semibold text-gray-800"></p>
                                                <p id="thumbnailFileMeta" class="text-xs text-gray-500"></p>
                                            </div>
                                            <p class="text-xs text-gray-500">Klik ulang area ini untuk mengganti thumbnail.</p>
                                        </div>
                                    </div>
                                    <p class="mt-3 text-xs text-gray-500">Rasio ideal 16:9, gunakan poster tajam karena gambar ini juga dipakai ketika broadcast ke user VIP.</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between px-4 py-3 border-b border-gray-100">
                                    <div>
                                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-widest">Langkah 3</p>
                                        <h4 class="text-base font-semibold text-gray-900">Penjadwalan Part & File Video</h4>
                                        <p class="text-xs text-gray-500">Seret file ke part masing-masing atau klik area unggah.</p>
                                    </div>
                                    <span id="partsCounter" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-xs font-semibold text-blue-700">
                                        0 part disiapkan
                                    </span>
                                </div>
                                <div class="p-4 sm:p-5">
                                    <div class="space-y-3" id="videoPartsContainer">
                                        <!-- Video parts will be generated here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 bg-white">
                        <div class="flex items-center space-x-3">
                            <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-all duration-200">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                Simpan & Post ke Channel
                            </button>
                        </div>
                    </div>
                </form>

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

    const thumbnailInput = $('#thumbnail');
    const thumbnailDropZone = $('#thumbnailDropZone');
    const thumbnailPlaceholder = $('#thumbnailPlaceholder');
    const thumbnailPreview = $('#thumbnailPreview');
    const thumbnailImage = $('#thumbnailPreviewImage');
    const thumbnailFileName = $('#thumbnailFileName');
    const thumbnailFileMeta = $('#thumbnailFileMeta');

    function loadMovies() {
        $.ajax({
            url: '{{ route("movies.data") }}',
            method: 'GET',
            data: {
                search: { value: searchQuery },
                start: (currentPage - 1) * perPage,
                length: perPage
            },
            success: function(response) {
                renderTable(response.data);
                updatePagination(response);
            },
            error: function(xhr) {
                console.error('Error loading movies:', xhr);
                showError('Gagal memuat data film');
            }
        });
    }

    function renderTable(movies) {
        const tbody = $('#moviesTable tbody');
        tbody.empty();

        if (!movies || movies.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Belum ada film</h3>
                            <p class="text-sm text-gray-500">Klik tombol "Tambah Film" untuk menambahkan film baru</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        movies.forEach(function(movie) {
            const freeParts = movie.video_parts.filter(p => !p.is_vip).length;
            const createdDate = new Date(movie.created_at).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });

            const statusBadge = movie.channel_message_id
                ? '<span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border bg-green-100 text-green-800 border-green-200 shadow-sm">Posted</span>'
                : '<span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border bg-yellow-100 text-yellow-800 border-yellow-200 shadow-sm">Draft</span>';

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
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border bg-blue-100 text-blue-800 border-blue-200 shadow-sm">
                            ${movie.total_parts} Part${movie.total_parts > 1 ? 's' : ''}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border bg-green-100 text-green-800 border-green-200 shadow-sm">
                            ${freeParts} Free
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        ${statusBadge}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm text-gray-600">${createdDate}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center space-x-2">
                            <button onclick="editMovie(${movie.id})" class="p-2.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 hover:scale-110" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="deleteMovie(${movie.id})" class="p-2.5 text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200 hover:scale-110" title="Delete">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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
        totalPages = Math.ceil(response.recordsFiltered / perPage);

        const start = response.recordsFiltered > 0 ? ((currentPage - 1) * perPage) + 1 : 0;
        const end = Math.min(currentPage * perPage, response.recordsFiltered);
        const total = response.recordsFiltered;

        $('#tableInfo').html(`Showing <span class="font-semibold text-gray-900">${start}</span> to <span class="font-semibold text-gray-900">${end}</span> of <span class="font-semibold text-gray-900">${total}</span> films`);

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

    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            searchQuery = $('#searchInput').val();
            currentPage = 1;
            loadMovies();
        }, 500);
    });

    window.deleteMovie = function(id) {
        showConfirm('Film akan dihapus dari database dan channel!', 'Apakah Anda yakin?').then((result) => {
            if (result.isConfirmed) {
                showLoading('Menghapus film...');

                $.ajax({
                    url: `/movies/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        closeLoading();
                        if (response.success) {
                            loadMovies();
                            showSuccess('Film berhasil dihapus!', 'Deleted!');
                        }
                    },
                    error: function(xhr) {
                        closeLoading();
                        console.error('Error deleting movie:', xhr);
                        showError('Gagal menghapus film');
                    }
                });
            }
        });
    };

    window.showAddModal = function() {
        $('#modalTitle').text('Tambah Film');
        $('#movieId').val('');
        $('#isEdit').val('0');
        $('#title').val('');
        $('#totalParts').val('1');
        $('#thumbnail').val('');
        updateThumbnailPreview(null);
        $('#thumbnailRequired').show();
        $('#thumbnailOptional').hide();
        $('#videoPartsContainer').empty();

        // Generate video parts inputs for new movie
        generateVideoParts(1);

        $('#movieModal').removeClass('hidden');
        $('#title').focus();
    };

    window.editMovie = function(id) {
        showLoading('Memuat data film...');

        $.ajax({
            url: `/movies/${id}`,
            method: 'GET',
            success: function(response) {
                closeLoading();

                const movie = response;
                $('#modalTitle').text('Edit Film');
                $('#movieId').val(movie.id);
                $('#isEdit').val('1');
                $('#title').val(movie.title);
                $('#totalParts').val(movie.total_parts);
                $('#thumbnailRequired').hide();
                $('#thumbnailOptional').show();
                updateThumbnailPreview(null);
                $('#videoPartsContainer').empty();

                // Generate video parts inputs for existing movie
                generateVideoParts(movie.total_parts, movie.video_parts);

                $('#movieModal').removeClass('hidden');
                $('#title').focus();
            },
            error: function(xhr) {
                closeLoading();
                console.error('Error loading movie:', xhr);
                showError('Gagal memuat data film');
            }
        });
    };

    function generateVideoParts(totalParts, existingParts = []) {
        const container = $('#videoPartsContainer');
        container.empty();
        const uniqueSuffix = Date.now();
        const sourceParts = Array.isArray(existingParts) ? existingParts : [];

        for (let i = 1; i <= totalParts; i++) {
            const existingPart = sourceParts.find(p => p.part_number === i);
            const isVip = existingPart ? existingPart.is_vip : false;
            const inputId = `videoFile_${uniqueSuffix}_${i}`;

            const partHtml = `
                <div class="rounded-2xl border border-gray-200 bg-white p-3 sm:p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-gray-200 shadow-sm">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Part</span>
                            <span class="text-sm font-bold text-gray-900">#${i}</span>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-full px-3 py-1.5 shadow-sm">
                            <input type="checkbox" name="vip_parts[]" value="${i}" ${isVip ? 'checked' : ''} class="w-4 h-4 text-amber-500 bg-gray-100 border-gray-300 rounded focus:ring-amber-500">
                            VIP Only
                        </label>
                    </div>
                    <div class="space-y-3">
                        <div class="relative group">
                            <input type="file" name="videos[]" accept="video/*" id="${inputId}" class="sr-only part-file-input">
                            <div class="video-upload-tile border-2 border-dashed border-gray-200 rounded-2xl p-4 text-center cursor-pointer transition-all duration-200 hover:border-blue-400 hover:bg-blue-50/40" data-input="${inputId}">
                                <div class="flex flex-col items-center justify-center gap-2 pointer-events-none">
                                    <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828M5 19h14"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800" data-default-text>Tarik dan jatuhkan video</p>
                                        <p class="text-xs text-gray-500" data-helper-text>MP4, MKV, AVI maks 2GB</p>
                                    </div>
                                    <div class="hidden text-sm font-semibold text-gray-800" data-file-name></div>
                                    <div class="hidden text-xs text-gray-500" data-file-size></div>
                                </div>
                            </div>
                        </div>
                        ${existingPart ? `
                            <div class="flex items-start gap-2 text-xs text-emerald-600 bg-emerald-50 border border-emerald-100 rounded-xl px-3 py-2">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>File tersimpan (${existingPart.file_size ? formatFileSize(existingPart.file_size) : 'ukuran tidak diketahui'}). Biarkan kosong jika tidak ingin mengganti.</span>
                            </div>
                        ` : `
                            <p class="text-xs text-gray-500 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 8v.01M21 12A9 9 0 113 12a9 9 0 0118 0z"></path>
                                </svg>
                                File video akan dikirim sesuai urutan part.
                            </p>
                        `}
                    </div>
                </div>
            `;
            container.append(partHtml);
        }

        $('#partsCounter').text(`${totalParts} ${totalParts > 1 ? 'parts' : 'part'} disiapkan`);
    }

    function updatePartUploadTile(tile, file) {
        if (!tile || tile.length === 0) return;
        const defaultText = tile.find('[data-default-text]');
        const helperText = tile.find('[data-helper-text]');
        const fileName = tile.find('[data-file-name]');
        const fileSize = tile.find('[data-file-size]');

        if (file) {
            tile.addClass('border-blue-500 bg-blue-50/40');
            defaultText.text('File siap diunggah');
            helperText.text('Pastikan koneksi stabil saat upload');
            fileName.removeClass('hidden').text(file.name);
            fileSize.removeClass('hidden').text(formatFileSize(file.size));
        } else {
            tile.removeClass('border-blue-500 bg-blue-50/40');
            defaultText.text('Tarik dan jatuhkan video');
            helperText.text('MP4, MKV, AVI maks 2GB');
            fileName.addClass('hidden').text('');
            fileSize.addClass('hidden').text('');
        }
    }

    function updateThumbnailPreview(file) {
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                thumbnailImage.attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
            thumbnailDropZone.addClass('border-blue-500 bg-blue-50/40');
            thumbnailPlaceholder.addClass('hidden');
            thumbnailPreview.removeClass('hidden');
            thumbnailFileName.text(file.name);
            thumbnailFileMeta.text(formatFileSize(file.size));
        } else {
            thumbnailDropZone.removeClass('border-blue-500 bg-blue-50/40');
            thumbnailPlaceholder.removeClass('hidden');
            thumbnailPreview.addClass('hidden');
            thumbnailImage.attr('src', '');
            thumbnailFileName.text('');
            thumbnailFileMeta.text('');
        }
    }

    function assignFileToInput(input, file) {
        if (!input || !file || typeof DataTransfer === 'undefined') {
            return;
        }
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;
        $(input).trigger('change');
    }

    thumbnailDropZone.on('click', function() {
        thumbnailInput.trigger('click');
    });

    thumbnailInput.on('change', function() {
        const file = this.files && this.files[0] ? this.files[0] : null;
        updateThumbnailPreview(file);
    });

    thumbnailDropZone.on('dragover', function(e) {
        e.preventDefault();
        thumbnailDropZone.addClass('border-blue-500 bg-blue-50/40');
    });

    thumbnailDropZone.on('dragleave dragend', function(e) {
        e.preventDefault();
        thumbnailDropZone.removeClass('border-blue-500 bg-blue-50/40');
    });

    thumbnailDropZone.on('drop', function(e) {
        e.preventDefault();
        thumbnailDropZone.removeClass('border-blue-500 bg-blue-50/40');
        const files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
        if (files && files.length) {
            assignFileToInput(thumbnailInput[0], files[0]);
        }
    });

    $(document).on('click', '.video-upload-tile', function() {
        const inputId = $(this).data('input');
        const input = document.getElementById(inputId);
        if (input) {
            input.click();
        }
    });

    $(document).on('dragover', '.video-upload-tile', function(e) {
        e.preventDefault();
        $(this).addClass('border-blue-500 bg-blue-50/40');
    });

    $(document).on('dragleave dragend', '.video-upload-tile', function(e) {
        e.preventDefault();
        $(this).removeClass('border-blue-500 bg-blue-50/40');
    });

    $(document).on('drop', '.video-upload-tile', function(e) {
        e.preventDefault();
        $(this).removeClass('border-blue-500 bg-blue-50/40');
        const files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
        const inputId = $(this).data('input');
        if (files && files.length && inputId) {
            const input = document.getElementById(inputId);
            assignFileToInput(input, files[0]);
        }
    });

    $(document).on('change', '.part-file-input', function() {
        const tile = $(`.video-upload-tile[data-input="${this.id}"]`);
        const file = this.files && this.files[0] ? this.files[0] : null;
        updatePartUploadTile(tile, file);
    });

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    $('#totalParts').on('change', function() {
        let totalParts = parseInt($(this).val()) || 1;
        totalParts = Math.max(1, Math.min(50, totalParts));
        $(this).val(totalParts);
        generateVideoParts(totalParts);
    });

    $('#partsIncrease').on('click', function() {
        let current = parseInt($('#totalParts').val()) || 1;
        if (current < 50) {
            $('#totalParts').val(current + 1).trigger('change');
        }
    });

    $('#partsDecrease').on('click', function() {
        let current = parseInt($('#totalParts').val()) || 1;
        if (current > 1) {
            $('#totalParts').val(current - 1).trigger('change');
        }
    });

    $('#movieForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const isEdit = $('#isEdit').val() === '1';
        const url = isEdit ? `/movies/${$('#movieId').val()}` : '{{ route("movies.store") }}';
        const method = isEdit ? 'POST' : 'POST';

        if (isEdit) {
            formData.append('_method', 'PUT');
        }

        // Debug: Log FormData contents
        console.log('=== FormData Debug ===');
        for (let [key, value] of formData.entries()) {
            if (value instanceof File) {
                console.log(`${key}:`, value.name, `(${value.size} bytes)`);
            } else {
                console.log(`${key}:`, value);
            }
        }
        console.log('===================');

        // Validate required fields
        const title = $('#title').val().trim();
        if (!title) {
            showError('Judul film harus diisi');
            return;
        }

        const totalParts = parseInt($('#totalParts').val());
        if (!totalParts || totalParts < 1) {
            showError('Jumlah part minimal 1');
            return;
        }

        // Check if thumbnail is provided for new movies
        if (!isEdit) {
            const thumbnailFile = $('#thumbnail')[0].files;
            if (!thumbnailFile || thumbnailFile.length === 0) {
                showError('Thumbnail harus diupload');
                return;
            }

            // Check if all video files are provided
            const videoInputs = $('input[name="videos[]"]');
            let hasAllVideos = true;
            videoInputs.each(function() {
                if (!this.files || this.files.length === 0) {
                    hasAllVideos = false;
                    return false;
                }
            });

            if (!hasAllVideos) {
                showError(`Semua ${totalParts} video part harus diupload`);
                return;
            }
        }

        showLoading(isEdit ? 'Menyimpan perubahan...' : 'Menambahkan film...');

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                closeLoading();
                closeModal();
                loadMovies();
                showSuccess(isEdit ? 'Film berhasil diupdate!' : 'Film berhasil ditambahkan dan dipost ke channel!');
            },
            error: function(xhr) {
                closeLoading();
                console.error('Error saving movie:', xhr);

                let errorMessage = 'Gagal menyimpan film';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                showError(errorMessage);
            }
        });
    });

    window.closeModal = function() {
        $('#movieModal').addClass('hidden');
        $('#movieForm')[0].reset();
        updateThumbnailPreview(null);
    };

    // Close modal when clicking outside
    $('#movieModal').on('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    loadMovies();
});
</script>
@endpush
