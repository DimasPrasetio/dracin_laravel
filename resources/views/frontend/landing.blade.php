<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dracin HD - Nonton Film Drama China Berkualitas Tinggi</title>
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

        .card-dark:hover {
            border-color: #E84C1E;
            box-shadow: 0 4px 20px rgba(232, 76, 30, 0.15);
        }

        .popular-badge {
            background: linear-gradient(135deg, #E84C1E 0%, #FFB347 100%);
            box-shadow: 0 4px 15px rgba(255, 179, 71, 0.3);
        }

        .text-accent {
            color: #FFB347;
        }

        .text-secondary {
            color: #CFCFCF;
        }

        .border-divider {
            border-color: #2B2B2B;
        }

        .glow-effect {
            box-shadow: 0 0 30px rgba(255, 179, 71, 0.2);
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <section class="relative py-20 px-4" style="background-color: #0B0B0B;">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 20% 30%, rgba(232, 76, 30, 0.1) 0%, transparent 50%), radial-gradient(circle at 80% 70%, rgba(255, 179, 71, 0.1) 0%, transparent 50%);"></div>
        </div>

        <div class="max-w-6xl mx-auto text-center relative z-10">
            <h1 class="text-5xl md:text-6xl font-bold mb-6">
                <span class="text-accent">Dracin</span> <span class="text-white">HD</span>
            </h1>
            <p class="text-2xl md:text-3xl mb-4 font-light">
                Platform Streaming Film Drama China Berkualitas HD
            </p>
            <p class="text-lg md:text-xl mb-8 text-secondary">
                Nikmati ribuan film drama china terbaik dengan subtitle Indonesia
            </p>

            <!-- Features -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12 max-w-4xl mx-auto">
                <div class="card-dark rounded-lg p-6 transition-all duration-300">
                    <div class="text-4xl mb-3">🎥</div>
                    <h3 class="font-semibold text-lg mb-2">Kualitas HD</h3>
                    <p class="text-sm text-secondary">Video berkualitas tinggi untuk pengalaman menonton terbaik</p>
                </div>
                <div class="card-dark rounded-lg p-6 transition-all duration-300">
                    <div class="text-4xl mb-3">📱</div>
                    <h3 class="font-semibold text-lg mb-2">Via Telegram</h3>
                    <p class="text-sm text-secondary">Akses langsung melalui bot Telegram, tanpa aplikasi tambahan</p>
                </div>
                <div class="card-dark rounded-lg p-6 transition-all duration-300">
                    <div class="text-4xl mb-3">⚡</div>
                    <h3 class="font-semibold text-lg mb-2">Unlimited</h3>
                    <p class="text-sm text-secondary">Tonton sepuasnya selama masa VIP aktif</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-20 px-4" style="background-color: #141414;">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-4">Pilih Paket VIP Anda</h2>
                <p class="text-secondary text-lg">Harga terjangkau untuk hiburan tanpa batas</p>
            </div>

            @if(session('error'))
                <div class="max-w-2xl mx-auto mb-8 bg-red-900/30 border border-red-500/50 text-red-300 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($packages as $key => $package)
                <div class="relative">
                    @if(isset($package['popular']) && $package['popular'])
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 z-10">
                        <span class="popular-badge text-white text-xs font-bold px-4 py-2 rounded-full">
                            TERPOPULER
                        </span>
                    </div>
                    @endif

                    <div class="card-dark rounded-2xl p-8 h-full flex flex-col transition-all duration-300 {{ isset($package['popular']) && $package['popular'] ? 'glow-effect' : '' }}">
                        @if(isset($package['badge']))
                        <div class="mb-4">
                            <span class="bg-green-900/30 text-green-400 text-xs font-semibold px-3 py-1 rounded-full border border-green-500/30">
                                {{ $package['badge'] }}
                            </span>
                        </div>
                        @endif

                        <h3 class="text-2xl font-bold mb-2">{{ $package['name'] }}</h3>
                        <div class="mb-6">
                            <span class="text-4xl font-bold text-accent">Rp {{ number_format($package['price'], 0, ',', '.') }}</span>
                            <span class="text-secondary">/{{ $package['duration'] }} hari</span>
                        </div>

                        <p class="text-secondary mb-6 flex-grow">{{ $package['description'] }}</p>

                        <ul class="space-y-3 mb-8">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-secondary">Akses semua film VIP</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-secondary">Kualitas HD 1080p</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-secondary">Subtitle Indonesia</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-secondary">Nonton tanpa batas</span>
                            </li>
                        </ul>

                        <a href="{{ route('checkout', ['package' => $key]) }}"
                           class="block w-full text-center btn-primary font-semibold py-3 px-6 rounded-lg text-white">
                            Pilih Paket
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20 px-4" style="background-color: #0B0B0B;">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">
                    Cara Nonton <span class="text-accent">Dracin HD</span>
                </h2>
                <p class="text-secondary text-lg md:text-xl">
                    Proses mudah dan cepat, dari berlangganan hingga menonton film favorit Anda
                </p>
            </div>

            <!-- Step by Step Process -->
            <div class="space-y-12">

                <!-- Step 1: Pilih Paket -->
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-1/3 order-2 md:order-1">
                        <div class="card-dark rounded-xl p-8 h-full">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 rounded-full flex items-center justify-center bg-gradient-to-br from-orange-600 to-orange-400">
                                    <span class="text-2xl font-bold text-white">1</span>
                                </div>
                                <h3 class="text-2xl font-bold">Pilih Paket VIP</h3>
                            </div>
                            <p class="text-secondary text-lg leading-relaxed">
                                Pilih paket berlangganan yang sesuai dengan kebutuhan Anda.
                                Tersedia paket 1 hari untuk trial, 3 hari, 7 hari, hingga 30 hari dengan harga yang sangat terjangkau.
                            </p>
                            <div class="mt-6 flex items-center gap-2 text-accent">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold">Mulai dari Rp 2.500</span>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 order-1 md:order-2">
                        <div class="card-dark rounded-xl p-6 border-2 border-accent/30">
                            <div class="text-6xl mb-4 text-center">💎</div>
                            <p class="text-center text-secondary italic">
                                "Pilih dari 4 paket VIP yang tersedia di landing page"
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Arrow Down -->
                <div class="flex justify-center">
                    <svg class="w-8 h-8 text-accent animate-bounce" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a1 1 0 01-.707-.293l-7-7a1 1 0 011.414-1.414L10 15.586l6.293-6.293a1 1 0 011.414 1.414l-7 7A1 1 0 0110 18z" clip-rule="evenodd"/>
                    </svg>
                </div>

                <!-- Step 2: Isi Data & Payment -->
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-2/3 order-1">
                        <div class="card-dark rounded-xl p-6 border-2 border-accent/30">
                            <div class="text-6xl mb-4 text-center">📝</div>
                            <p class="text-center text-secondary italic mb-4">
                                "Isi data akun Telegram Anda dan pilih metode pembayaran"
                            </p>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="bg-black/30 rounded-lg p-3">
                                    <div class="text-accent font-semibold mb-1">Data Required:</div>
                                    <ul class="text-secondary space-y-1">
                                        <li>• Telegram User ID</li>
                                        <li>• First Name</li>
                                        <li>• Username (opsional)</li>
                                    </ul>
                                </div>
                                <div class="bg-black/30 rounded-lg p-3">
                                    <div class="text-accent font-semibold mb-1">Payment:</div>
                                    <ul class="text-secondary space-y-1">
                                        <li>• QRIS (Semua E-Wallet)</li>
                                        <li>• Virtual Account Bank</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-1/3 order-2">
                        <div class="card-dark rounded-xl p-8 h-full">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 rounded-full flex items-center justify-center bg-gradient-to-br from-orange-600 to-orange-400">
                                    <span class="text-2xl font-bold text-white">2</span>
                                </div>
                                <h3 class="text-2xl font-bold">Checkout</h3>
                            </div>
                            <p class="text-secondary text-lg leading-relaxed">
                                Masukkan Telegram User ID dan data Anda. Pilih metode pembayaran favorit Anda,
                                lalu lakukan pembayaran dengan mudah dan aman.
                            </p>
                            <div class="mt-6 flex items-center gap-2 text-accent">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold">Proses otomatis & aman</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Arrow Down -->
                <div class="flex justify-center">
                    <svg class="w-8 h-8 text-accent animate-bounce" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a1 1 0 01-.707-.293l-7-7a1 1 0 011.414-1.414L10 15.586l6.293-6.293a1 1 0 011.414 1.414l-7 7A1 1 0 0110 18z" clip-rule="evenodd"/>
                    </svg>
                </div>

                <!-- Step 3: Join Channel -->
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-1/3 order-2 md:order-1">
                        <div class="card-dark rounded-xl p-8 h-full">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 rounded-full flex items-center justify-center bg-gradient-to-br from-orange-600 to-orange-400">
                                    <span class="text-2xl font-bold text-white">3</span>
                                </div>
                                <h3 class="text-2xl font-bold">Join Channel</h3>
                            </div>
                            <p class="text-secondary text-lg leading-relaxed">
                                Setelah pembayaran berhasil, VIP Anda otomatis aktif!
                                Masuk ke channel Telegram Dracin HD untuk mengakses katalog film lengkap.
                            </p>
                            <div class="mt-6">
                                <a href="https://t.me/dracin_hd" target="_blank"
                                   class="inline-flex items-center gap-2 text-accent hover:text-orange-400 transition-colors font-semibold">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 0C4.477 0 0 4.477 0 10s4.477 10 10 10 10-4.477 10-10S15.523 0 10 0zm4.987 6.463l-1.562 7.349c-.117.537-.425.669-.862.417l-2.387-1.759-1.15 1.107c-.127.127-.234.234-.48.234l.17-2.425 4.437-4.007c.192-.17-.042-.267-.298-.096l-5.484 3.452-2.362-.738c-.513-.16-.524-.513.107-.76l9.237-3.56c.427-.16.8.096.662.76z"/>
                                    </svg>
                                    @dracin_hd
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 order-1 md:order-2">
                        <div class="card-dark rounded-xl p-6 border-2 border-accent/30">
                            <div class="text-6xl mb-4 text-center">📢</div>
                            <p class="text-center text-secondary italic">
                                "Bergabung ke channel Telegram untuk melihat katalog film"
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Arrow Down -->
                <div class="flex justify-center">
                    <svg class="w-8 h-8 text-accent animate-bounce" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a1 1 0 01-.707-.293l-7-7a1 1 0 011.414-1.414L10 15.586l6.293-6.293a1 1 0 011.414 1.414l-7 7A1 1 0 0110 18z" clip-rule="evenodd"/>
                    </svg>
                </div>

                <!-- Step 4: Pilih Film -->
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-2/3 order-1">
                        <div class="card-dark rounded-xl p-6 border-2 border-accent/30">
                            <div class="text-6xl mb-4 text-center">🎬</div>
                            <p class="text-center text-secondary italic mb-4">
                                "Pilih film drama China favorit Anda dari katalog yang tersedia"
                            </p>
                            <div class="bg-black/30 rounded-lg p-4">
                                <div class="text-accent font-semibold mb-2 text-center">Ribuan Film Berkualitas HD:</div>
                                <ul class="text-secondary space-y-1 text-center">
                                    <li>✨ Drama Romantis</li>
                                    <li>⚔️ Action & Martial Arts</li>
                                    <li>🎭 Historical & Fantasy</li>
                                    <li>😊 Comedy & Family</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-1/3 order-2">
                        <div class="card-dark rounded-xl p-8 h-full">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 rounded-full flex items-center justify-center bg-gradient-to-br from-orange-600 to-orange-400">
                                    <span class="text-2xl font-bold text-white">4</span>
                                </div>
                                <h3 class="text-2xl font-bold">Pilih Film</h3>
                            </div>
                            <p class="text-secondary text-lg leading-relaxed">
                                Jelajahi katalog film di channel dan pilih film yang ingin Anda tonton.
                                Semua film tersedia dalam kualitas HD dengan subtitle Indonesia.
                            </p>
                            <div class="mt-6 flex items-center gap-2 text-accent">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold">Update film setiap hari</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Arrow Down -->
                <div class="flex justify-center">
                    <svg class="w-8 h-8 text-accent animate-bounce" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a1 1 0 01-.707-.293l-7-7a1 1 0 011.414-1.414L10 15.586l6.293-6.293a1 1 0 011.414 1.414l-7 7A1 1 0 0110 18z" clip-rule="evenodd"/>
                    </svg>
                </div>

                <!-- Step 5: Tonton via Bot -->
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-1/3 order-2 md:order-1">
                        <div class="card-dark rounded-xl p-8 h-full glow-effect border-2 border-accent/50">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 rounded-full flex items-center justify-center bg-gradient-to-br from-orange-600 to-orange-400">
                                    <span class="text-2xl font-bold text-white">5</span>
                                </div>
                                <h3 class="text-2xl font-bold">Nonton Sekarang!</h3>
                            </div>
                            <p class="text-secondary text-lg leading-relaxed">
                                Klik film yang Anda inginkan di channel, dan Anda akan diarahkan ke bot.
                                Film langsung dapat ditonton dengan kualitas HD terbaik!
                            </p>
                            <div class="mt-6 flex items-center gap-2 text-green-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold">Langsung streaming!</span>
                            </div>
                        </div>
                    </div>
                    <div class="w-full md:w-2/3 order-1 md:order-2">
                        <div class="card-dark rounded-xl p-8 border-2 border-green-500/30 bg-gradient-to-br from-green-900/10 to-transparent">
                            <div class="text-6xl mb-4 text-center">🎉</div>
                            <p class="text-center text-lg mb-4">
                                <span class="text-accent font-bold">Selamat Menonton!</span>
                            </p>
                            <p class="text-center text-secondary italic">
                                "Film akan langsung streaming melalui bot Telegram. Tanpa download, tanpa ribet!"
                            </p>
                            <div class="mt-6 grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <div class="text-2xl mb-1">⚡</div>
                                    <div class="text-xs text-secondary">Streaming Cepat</div>
                                </div>
                                <div>
                                    <div class="text-2xl mb-1">🎥</div>
                                    <div class="text-xs text-secondary">Kualitas HD</div>
                                </div>
                                <div>
                                    <div class="text-2xl mb-1">💯</div>
                                    <div class="text-xs text-secondary">Subtitle Indo</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- CTA Button -->
            <div class="text-center mt-16">
                <a href="#pricing"
                   onclick="event.preventDefault(); document.querySelector('[style*=background-color]').scrollIntoView({behavior: 'smooth', block: 'start'});"
                   class="inline-block btn-primary font-bold text-lg py-4 px-12 rounded-lg text-white shadow-lg hover:shadow-xl transition-all duration-300">
                    Mulai Berlangganan Sekarang
                </a>
                <p class="text-secondary mt-4">Mudah, cepat, dan langsung bisa nonton!</p>
            </div>
        </div>
    </section>

    <!-- Payment Methods Section -->
    <section class="py-20 px-4" style="background-color: #141414;">
        <div class="max-w-6xl mx-auto text-center">
            <h2 class="text-4xl font-bold mb-4">Metode Pembayaran</h2>
            <p class="text-secondary text-lg mb-12">Kami mendukung berbagai metode pembayaran untuk kemudahan Anda</p>

            <div class="flex flex-wrap justify-center items-center gap-8">
                <div class="card-dark px-8 py-4 rounded-lg">
                    <span class="font-semibold">QRIS</span>
                </div>
                <div class="card-dark px-8 py-4 rounded-lg">
                    <span class="font-semibold">BCA Virtual Account</span>
                </div>
                <div class="card-dark px-8 py-4 rounded-lg">
                    <span class="font-semibold">BNI Virtual Account</span>
                </div>
                <div class="card-dark px-8 py-4 rounded-lg">
                    <span class="font-semibold">BRI Virtual Account</span>
                </div>
                <div class="card-dark px-8 py-4 rounded-lg">
                    <span class="font-semibold">Mandiri Virtual Account</span>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 px-4" style="background-color: #0B0B0B;">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-4">Pertanyaan Umum</h2>
            </div>

            <div class="space-y-6">
                <div class="card-dark rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-2">Bagaimana cara mengakses film setelah berlangganan VIP?</h3>
                    <p class="text-secondary">Setelah pembayaran berhasil, VIP Anda akan otomatis aktif. Anda bisa langsung mengakses semua film melalui bot Telegram kami.</p>
                </div>

                <div class="card-dark rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-2">Berapa lama pembayaran diproses?</h3>
                    <p class="text-secondary">Pembayaran via QRIS dan Virtual Account biasanya diproses secara otomatis dalam hitungan menit setelah Anda melakukan pembayaran.</p>
                </div>

                <div class="card-dark rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-2">Apakah ada batasan jumlah film yang bisa ditonton?</h3>
                    <p class="text-secondary">Tidak ada batasan! Selama masa VIP aktif, Anda bisa menonton semua film sebanyak yang Anda mau.</p>
                </div>

                <div class="card-dark rounded-lg p-6">
                    <h3 class="font-semibold text-lg mb-2">Bagaimana cara mendapatkan Telegram User ID saya?</h3>
                    <p class="text-secondary">Anda bisa mendapatkan Telegram User ID dengan menghubungi bot kami @userinfobot atau bot serupa di Telegram.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 px-4" style="background-color: #141414; border-top: 1px solid #2B2B2B;">
        <div class="max-w-6xl mx-auto text-center">
            <h3 class="text-2xl font-bold mb-4"><span class="text-accent">Dracin</span> HD</h3>
            <p class="text-lg text-secondary mb-6">Platform streaming film drama china terpercaya</p>
            <p class="text-sm text-secondary">&copy; {{ date('Y') }} Dracin HD. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
