<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Dracin HD</title>
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

        .qr-code-box {
            background-color: #FFFFFF;
            padding: 2rem;
            border-radius: 0.5rem;
        }

        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }

        .glow-success {
            box-shadow: 0 0 30px rgba(34, 197, 94, 0.3);
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="py-6 px-4" style="background-color: #141414; border-bottom: 1px solid #2B2B2B;">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold">Selesaikan Pembayaran</h1>
            <p class="mt-2 text-secondary">Order ID: <span class="text-accent">{{ $payment->tripay_reference }}</span></p>
        </div>
    </header>

    <div class="max-w-4xl mx-auto px-4 py-12">
        @if($payment->status === 'paid')
            <!-- Payment Success -->
            <div class="card-dark rounded-lg p-8 text-center glow-success">
                <div class="mb-6">
                    <svg class="w-24 h-24 text-green-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold mb-4">Pembayaran Berhasil!</h2>
                <p class="text-secondary mb-8">VIP Anda sudah aktif dan siap digunakan</p>

                <div class="rounded-lg p-6 mb-8" style="background-color: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2);">
                    <h3 class="font-semibold text-lg mb-4">Detail VIP:</h3>
                    <div class="space-y-2 text-left max-w-md mx-auto">
                        <div class="flex justify-between">
                            <span class="text-secondary">Paket:</span>
                            <span class="font-medium">{{ $payment->package }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary">Telegram User ID:</span>
                            <span class="font-medium">{{ $payment->telegramUser->telegram_user_id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary">Status VIP:</span>
                            <span class="font-medium text-green-400">Aktif</span>
                        </div>
                        @if($payment->telegramUser->vip_until)
                        <div class="flex justify-between">
                            <span class="text-secondary">Berlaku Hingga:</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($payment->telegramUser->vip_until)->format('d M Y H:i') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="text-center">
                    <p class="text-secondary mb-4">Silakan akses bot Telegram kami untuk mulai menonton!</p>
                    <a href="{{ route('landing') }}" class="inline-block btn-primary font-semibold py-3 px-8 rounded-lg text-white">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>

        @elseif($payment->status === 'expired' || $payment->status === 'cancelled')
            <!-- Payment Failed/Expired -->
            <div class="card-dark rounded-lg p-8 text-center">
                <div class="mb-6">
                    <svg class="w-24 h-24 text-red-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold mb-4">
                    Pembayaran {{ $payment->status === 'expired' ? 'Kadaluarsa' : 'Dibatalkan' }}
                </h2>
                <p class="text-secondary mb-8">Silakan lakukan pemesanan ulang untuk melanjutkan</p>

                <a href="{{ route('landing') }}" class="inline-block btn-primary font-semibold py-3 px-8 rounded-lg text-white">
                    Pesan Lagi
                </a>
            </div>

        @else
            <!-- Payment Pending -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Payment Instructions -->
                <div class="lg:col-span-2">
                    <div class="card-dark rounded-lg p-8">
                        <!-- Status Badge -->
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <span class="pulse-animation inline-block w-3 h-3 bg-yellow-400 rounded-full mr-2"></span>
                                <span class="text-yellow-400 font-semibold">Menunggu Pembayaran</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-secondary">Batas Waktu</div>
                                <div class="font-semibold text-accent" id="countdown">
                                    {{ \Carbon\Carbon::parse($payment->expired_at)->format('d M Y H:i') }}
                                </div>
                            </div>
                        </div>

                        <h2 class="text-2xl font-bold mb-6">
                            {{ $payment->tripay_payment_name ?: $payment->tripay_payment_method }}
                        </h2>

                        @if($payment->tripay_payment_method === 'QRIS' || str_contains($payment->tripay_payment_method, 'QRIS'))
                            <!-- QRIS Payment -->
                            <div class="text-center mb-8">
                                <div class="inline-block">
                                    @if($payment->tripay_qr_string)
                                        <div class="qr-code-box">
                                            <div id="qrcode" class="mx-auto"></div>
                                        </div>
                                        <p class="mt-4 text-sm text-secondary">Scan QR Code dengan aplikasi mobile banking Anda</p>
                                    @elseif($payment->tripay_checkout_url)
                                        <div class="card-dark p-6 rounded-lg">
                                            <p class="text-secondary mb-4">Klik tombol di bawah untuk melakukan pembayaran:</p>
                                            <a href="{{ $payment->tripay_checkout_url }}" target="_blank" class="inline-block btn-primary font-semibold py-3 px-8 rounded-lg text-white">
                                                Bayar Sekarang
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-lg p-6" style="background-color: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);">
                                <h3 class="font-semibold mb-3">Cara Pembayaran QRIS:</h3>
                                <ol class="list-decimal list-inside space-y-2 text-sm text-secondary">
                                    <li>Buka aplikasi mobile banking Anda (GoPay, OVO, Dana, LinkAja, atau bank yang mendukung QRIS)</li>
                                    <li>Pilih menu Scan QR atau QRIS</li>
                                    <li>Scan QR Code di atas</li>
                                    <li>Periksa detail pembayaran dan konfirmasi</li>
                                    <li>Pembayaran akan otomatis diverifikasi</li>
                                </ol>
                            </div>

                        @else
                            <!-- Virtual Account Payment -->
                            <div class="mb-8">
                                <div class="card-dark rounded-lg p-6 text-center border-2 border-accent">
                                    <p class="text-sm text-secondary mb-2">Nomor Virtual Account:</p>
                                    <div class="flex items-center justify-center">
                                        <p class="text-3xl font-bold text-accent tracking-wider" id="va-number">
                                            {{ $payment->tripay_pay_url ? 'Loading...' : '-' }}
                                        </p>
                                        <button onclick="copyVA()" class="ml-4 text-accent hover:text-white transition-colors">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-lg p-6" style="background-color: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);">
                                <h3 class="font-semibold mb-3">Cara Pembayaran Virtual Account:</h3>
                                <ol class="list-decimal list-inside space-y-2 text-sm text-secondary">
                                    <li>Buka aplikasi mobile banking atau ATM</li>
                                    <li>Pilih menu Transfer atau Bayar</li>
                                    <li>Pilih ke Virtual Account atau Bank {{ str_replace('VA', '', $payment->tripay_payment_method) }}</li>
                                    <li>Masukkan nomor Virtual Account di atas</li>
                                    <li>Masukkan nominal: <strong class="text-accent">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></li>
                                    <li>Konfirmasi pembayaran</li>
                                    <li>Pembayaran akan otomatis diverifikasi</li>
                                </ol>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="card-dark rounded-lg p-6 sticky top-4">
                        <h3 class="text-xl font-bold mb-4">Detail Pesanan</h3>

                        <div class="space-y-3 mb-6 text-sm">
                            <div class="flex justify-between">
                                <span class="text-secondary">Paket:</span>
                                <span class="font-medium">{{ $payment->package }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-secondary">Nama:</span>
                                <span class="font-medium">{{ $payment->telegramUser->full_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-secondary">Telegram ID:</span>
                                <span class="font-medium">{{ $payment->telegramUser->telegram_user_id }}</span>
                            </div>
                        </div>

                        <div class="border-t pt-4 mb-6" style="border-color: #2B2B2B;">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold">Total</span>
                                <span class="text-2xl font-bold text-accent">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <button onclick="checkPaymentStatus()" class="w-full btn-primary font-semibold py-3 px-6 rounded-lg text-white mb-3">
                            Cek Status Pembayaran
                        </button>

                        <div id="status-message" class="hidden text-center text-sm"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <footer class="py-8 px-4 mt-12" style="background-color: #141414; border-top: 1px solid #2B2B2B;">
        <div class="max-w-4xl mx-auto text-center">
            <p class="text-sm text-secondary">&copy; {{ date('Y') }} Dracin HD. All rights reserved.</p>
        </div>
    </footer>

    @if($payment->status === 'pending')
    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <script>
        // Generate QR Code if QRIS
        @if($payment->tripay_qr_string)
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ $payment->tripay_qr_string }}",
            width: 256,
            height: 256,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        @endif

        // Auto check payment status every 10 seconds
        let checkInterval = setInterval(function() {
            checkPaymentStatus(true);
        }, 10000);

        // Check payment status
        function checkPaymentStatus(silent = false) {
            if (!silent) {
                document.getElementById('status-message').innerHTML = '<span class="text-secondary">Memeriksa status...</span>';
                document.getElementById('status-message').classList.remove('hidden');
            }

            fetch("{{ route('payment.status', $payment->tripay_reference) }}")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.status === 'paid') {
                            clearInterval(checkInterval);
                            location.reload();
                        } else if (!silent) {
                            document.getElementById('status-message').innerHTML = '<span class="text-yellow-400">Pembayaran masih pending</span>';
                        }
                    } else if (!silent) {
                        document.getElementById('status-message').innerHTML = '<span class="text-red-400">Gagal memeriksa status</span>';
                    }
                })
                .catch(error => {
                    if (!silent) {
                        document.getElementById('status-message').innerHTML = '<span class="text-red-400">Terjadi kesalahan</span>';
                    }
                });
        }

        // Copy VA number
        function copyVA() {
            const vaNumber = document.getElementById('va-number').textContent;
            navigator.clipboard.writeText(vaNumber).then(function() {
                alert('Nomor Virtual Account berhasil disalin!');
            });
        }

        // Countdown timer
        function updateCountdown() {
            const expiredAt = new Date("{{ $payment->expired_at }}").getTime();
            const now = new Date().getTime();
            const distance = expiredAt - now;

            if (distance < 0) {
                clearInterval(checkInterval);
                document.getElementById("countdown").innerHTML = "EXPIRED";
                location.reload();
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("countdown").innerHTML = hours + " jam " + minutes + " menit " + seconds + " detik";
        }

        // Update countdown every second
        setInterval(updateCountdown, 1000);
        updateCountdown();
    </script>
    @endif

</body>
</html>
