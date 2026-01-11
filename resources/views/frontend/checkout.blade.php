<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Dracin HD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #0B0B0B;
            color: #FFFFFF;
        }

        .btn-primary {
            background-color: #E84C1E;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #FFB347;
            box-shadow: 0 0 20px rgba(255, 179, 71, 0.4);
        }

        .card-dark {
            background-color: #141414;
            border: 1px solid #2B2B2B;
        }

        .text-accent {
            color: #FFB347;
        }

        .text-secondary {
            color: #CFCFCF;
        }

        input[type="text"],
        input[type="radio"] {
            background-color: #0B0B0B;
            border: 1px solid #2B2B2B;
            color: #FFFFFF;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #E84C1E;
            box-shadow: 0 0 0 2px rgba(232, 76, 30, 0.2);
        }

        .radio-option:has(input:checked) {
            border-color: #E84C1E;
            background-color: rgba(232, 76, 30, 0.1);
        }

        .radio-option {
            transition: all 0.2s ease;
        }

        .radio-option:hover {
            border-color: #FFB347;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="py-6 px-4" style="background-color: #141414; border-bottom: 1px solid #2B2B2B;">
        <div class="max-w-4xl mx-auto">
            <a href="{{ route('landing') }}" class="inline-flex items-center text-accent hover:text-white transition-colors mb-4">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
            <h1 class="text-3xl font-bold">Checkout Paket VIP</h1>
        </div>
    </header>

    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-2">
                <div class="card-dark rounded-lg p-8">
                    <h2 class="text-2xl font-bold mb-6">Informasi Pengguna</h2>

                    @if($errors->any())
                        <div class="mb-6 bg-red-900/30 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 bg-red-900/30 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('checkout.process') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="package" value="{{ $package }}">

                        <!-- Telegram User ID -->
                        <div>
                            <label for="telegram_user_id" class="block text-sm font-medium mb-2">
                                Telegram User ID <span class="text-red-400">*</span>
                            </label>
                            <input type="text"
                                   id="telegram_user_id"
                                   name="telegram_user_id"
                                   value="{{ old('telegram_user_id') }}"
                                   required
                                   class="w-full px-4 py-3 rounded-lg"
                                   placeholder="Contoh: 1234567890">
                            <p class="mt-2 text-sm text-secondary">
                                Dapatkan User ID Anda dengan mengirim pesan ke bot
                                <a href="https://t.me/userinfobot" target="_blank" class="text-accent hover:underline">@userinfobot</a>
                            </p>
                        </div>

                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-medium mb-2">
                                Username Telegram
                            </label>
                            <input type="text"
                                   id="username"
                                   name="username"
                                   value="{{ old('username') }}"
                                   class="w-full px-4 py-3 rounded-lg"
                                   placeholder="Contoh: username (tanpa @, opsional)">
                            <p class="mt-2 text-sm text-secondary">Tanpa tanda @</p>
                        </div>

                        <!-- First Name -->
                        <div>
                            <label for="first_name" class="block text-sm font-medium mb-2">
                                Nama Depan <span class="text-red-400">*</span>
                            </label>
                            <input type="text"
                                   id="first_name"
                                   name="first_name"
                                   value="{{ old('first_name') }}"
                                   required
                                   class="w-full px-4 py-3 rounded-lg"
                                   placeholder="Contoh: John">
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label for="last_name" class="block text-sm font-medium mb-2">
                                Nama Belakang
                            </label>
                            <input type="text"
                                   id="last_name"
                                   name="last_name"
                                   value="{{ old('last_name') }}"
                                   class="w-full px-4 py-3 rounded-lg"
                                   placeholder="Contoh: Doe (opsional)">
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium mb-4">
                                Metode Pembayaran <span class="text-red-400">*</span>
                            </label>

                            @if(isset($paymentStatus) && !$paymentStatus['available'])
                                <div class="bg-yellow-900/30 border border-yellow-500/50 text-yellow-300 px-4 py-3 rounded-lg mb-4">
                                    <p class="font-medium">Pembayaran sementara tidak tersedia</p>
                                    <p class="text-sm mt-1">{{ $paymentStatus['description'] }}</p>
                                </div>
                            @elseif(empty($channels))
                                <div class="bg-yellow-900/30 border border-yellow-500/50 text-yellow-300 px-4 py-3 rounded-lg mb-4">
                                    <p class="font-medium">Tripay belum dikonfigurasi</p>
                                    <p class="text-sm mt-1">Silakan atur Tripay terlebih dahulu.</p>
                                </div>
                            @endif

                            <div class="space-y-3">
                                @forelse($channels as $channel)
                                    <label class="radio-option flex items-center p-4 border-2 rounded-lg cursor-pointer" style="border-color: #2B2B2B;">
                                        <input type="radio"
                                               name="payment_method"
                                               value="{{ $channel['code'] }}"
                                               class="w-4 h-4 text-accent"
                                               style="accent-color: #E84C1E;"
                                               {{ old('payment_method') == $channel['code'] ? 'checked' : '' }}
                                               required>
                                        <div class="ml-4 flex-1">
                                            <div class="font-medium">{{ $channel['name'] }}</div>
                                            @if(isset($channel['fee_customer']))
                                                <div class="text-sm text-secondary">
                                                    Biaya: {{ $channel['fee_customer']['type'] == 'flat' ? 'Rp ' . number_format($channel['fee_customer']['amount'], 0, ',', '.') : $channel['fee_customer']['amount'] . '%' }}
                                                </div>
                                            @endif
                                        </div>
                                    </label>
                                @empty
                                    <!-- Default payment methods if API fails -->
                                    <label class="radio-option flex items-center p-4 border-2 rounded-lg cursor-pointer" style="border-color: #2B2B2B;">
                                        <input type="radio" name="payment_method" value="QRIS" class="w-4 h-4" style="accent-color: #E84C1E;" required>
                                        <div class="ml-4 flex-1">
                                            <div class="font-medium">QRIS</div>
                                            <div class="text-sm text-secondary">Scan QR dengan aplikasi mobile banking</div>
                                        </div>
                                    </label>

                                    <label class="radio-option flex items-center p-4 border-2 rounded-lg cursor-pointer" style="border-color: #2B2B2B;">
                                        <input type="radio" name="payment_method" value="BCAVA" class="w-4 h-4" style="accent-color: #E84C1E;">
                                        <div class="ml-4 flex-1">
                                            <div class="font-medium">BCA Virtual Account</div>
                                            <div class="text-sm text-secondary">Transfer melalui ATM/Mobile Banking BCA</div>
                                        </div>
                                    </label>

                                    <label class="radio-option flex items-center p-4 border-2 rounded-lg cursor-pointer" style="border-color: #2B2B2B;">
                                        <input type="radio" name="payment_method" value="BRIVA" class="w-4 h-4" style="accent-color: #E84C1E;">
                                        <div class="ml-4 flex-1">
                                            <div class="font-medium">BRI Virtual Account</div>
                                            <div class="text-sm text-secondary">Transfer melalui ATM/Mobile Banking BRI</div>
                                        </div>
                                    </label>

                                    <label class="radio-option flex items-center p-4 border-2 rounded-lg cursor-pointer" style="border-color: #2B2B2B;">
                                        <input type="radio" name="payment_method" value="BNIVA" class="w-4 h-4" style="accent-color: #E84C1E;">
                                        <div class="ml-4 flex-1">
                                            <div class="font-medium">BNI Virtual Account</div>
                                            <div class="text-sm text-secondary">Transfer melalui ATM/Mobile Banking BNI</div>
                                        </div>
                                    </label>

                                    <label class="radio-option flex items-center p-4 border-2 rounded-lg cursor-pointer" style="border-color: #2B2B2B;">
                                        <input type="radio" name="payment_method" value="MANDIRIVA" class="w-4 h-4" style="accent-color: #E84C1E;">
                                        <div class="ml-4 flex-1">
                                            <div class="font-medium">Mandiri Virtual Account</div>
                                            <div class="text-sm text-secondary">Transfer melalui ATM/Mobile Banking Mandiri</div>
                                        </div>
                                    </label>
                                @endforelse
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit"
                                class="w-full btn-primary font-semibold py-4 px-6 rounded-lg text-white text-lg">
                            Lanjutkan ke Pembayaran
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="card-dark rounded-lg p-6 sticky top-4">
                    <h3 class="text-xl font-bold mb-4">Ringkasan Pesanan</h3>

                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium">{{ $packageData['name'] }}</div>
                                <div class="text-sm text-secondary">{{ $packageData['description'] }}</div>
                            </div>
                        </div>

                        @if(isset($packageData['badge']))
                        <div>
                            <span class="bg-green-900/30 text-green-400 text-xs font-semibold px-3 py-1 rounded-full border border-green-500/30">
                                {{ $packageData['badge'] }}
                            </span>
                        </div>
                        @endif

                        <div class="border-t pt-4" style="border-color: #2B2B2B;">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-secondary">Durasi:</span>
                                <span class="font-medium">{{ $packageData['duration'] }} Hari</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-secondary">Harga:</span>
                                <span class="font-medium">Rp {{ number_format($packageData['price'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4 mb-6" style="border-color: #2B2B2B;">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold">Total</span>
                            <span class="text-2xl font-bold text-accent">Rp {{ number_format($packageData['price'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="rounded-lg p-4" style="background-color: rgba(232, 76, 30, 0.1); border: 1px solid rgba(232, 76, 30, 0.2);">
                        <h4 class="font-semibold text-sm mb-2">Yang Anda Dapatkan:</h4>
                        <ul class="space-y-2 text-sm text-secondary">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Akses semua film VIP
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Kualitas HD 1080p
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                VIP aktif otomatis
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Nonton tanpa batas
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-8 px-4 mt-12" style="background-color: #141414; border-top: 1px solid #2B2B2B;">
        <div class="max-w-4xl mx-auto text-center">
            <p class="text-sm text-secondary">&copy; {{ date('Y') }} Dracin HD. All rights reserved.</p>
        </div>
    </footer>

    @if(isset($paymentStatus) && !$paymentStatus['available'])
        <div id="tripay-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/70 px-4">
            <div class="card-dark rounded-lg p-6 max-w-md w-full">
                <h3 class="text-xl font-bold mb-2">Pembayaran sementara tidak tersedia</h3>
                <p class="text-secondary">{{ $paymentStatus['description'] }}</p>
                <div class="mt-6 text-right">
                    <button type="button" id="tripay-modal-close" class="btn-primary px-4 py-2 rounded-lg text-white font-semibold">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
        <script>
            (function () {
                var form = document.querySelector('form[action="{{ route('checkout.process') }}"]');
                var modal = document.getElementById('tripay-modal');
                var closeBtn = document.getElementById('tripay-modal-close');

                if (!form || !modal || !closeBtn) {
                    return;
                }

                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                });

                closeBtn.addEventListener('click', function () {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            })();
        </script>
    @endif

</body>
</html>
