<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Dracin HD</title>
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

        .text-accent {
            color: #FFB347;
        }

        .text-secondary {
            color: #CFCFCF;
        }

        .btn-primary {
            background-color: #E84C1E;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #FFB347;
        }

        .card-dark {
            background-color: #141414;
            border: 1px solid #2B2B2B;
        }

        .card-dark:hover {
            border-color: #E84C1E;
        }

        /* Navbar Styles */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(10px);
            background-color: rgba(20, 20, 20, 0.95);
            border-bottom: 1px solid #2B2B2B;
        }

        .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #E84C1E, #FFB347);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .mobile-menu.active {
            max-height: 500px;
        }

        @media (max-width: 768px) {
            .nav-link::after {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar py-4 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between">
                <a href="{{ route('landing') }}" class="text-2xl font-bold">
                    <span class="text-accent">Dracin</span> <span class="text-white">HD</span>
                </a>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('landing') }}" class="nav-link text-secondary hover:text-accent">Beranda</a>
                    <a href="{{ route('landing') }}#pricing" class="nav-link text-secondary hover:text-accent">Paket VIP</a>
                    <a href="{{ route('about') }}" class="nav-link text-white hover:text-accent">Tentang Kami</a>
                    <a href="{{ route('contact') }}" class="nav-link text-secondary hover:text-accent">Kontak</a>
                    <a href="https://t.me/dracin_hd" target="_blank" class="flex items-center gap-2 btn-primary py-2 px-6 rounded-lg text-white font-semibold">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm4.987 7.756l-1.875 8.82c-.14.644-.51.803-1.034.5l-2.864-2.11-1.38 1.328c-.153.153-.28.28-.576.28l.204-2.91 5.324-4.808c.232-.204-.05-.32-.358-.115l-6.58 4.142-2.835-.885c-.616-.192-.628-.616.128-.912L16.4 7.057c.513-.192.96.115.587.699z"/>
                        </svg>
                        Join Channel
                    </a>
                </div>

                <button onclick="toggleMobileMenu()" class="md:hidden text-white focus:outline-none">
                    <svg id="menu-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div id="mobile-menu" class="mobile-menu md:hidden">
                <div class="pt-4 pb-2 space-y-3">
                    <a href="{{ route('landing') }}" class="block py-2 text-secondary hover:text-accent transition-colors">Beranda</a>
                    <a href="{{ route('landing') }}#pricing" class="block py-2 text-secondary hover:text-accent transition-colors">Paket VIP</a>
                    <a href="{{ route('about') }}" class="block py-2 text-white hover:text-accent transition-colors">Tentang Kami</a>
                    <a href="{{ route('contact') }}" class="block py-2 text-secondary hover:text-accent transition-colors">Kontak</a>
                    <a href="https://t.me/dracin_hd" target="_blank" class="flex items-center justify-center gap-2 btn-primary py-3 px-6 rounded-lg text-white font-semibold mt-4">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm4.987 7.756l-1.875 8.82c-.14.644-.51.803-1.034.5l-2.864-2.11-1.38 1.328c-.153.153-.28.28-.576.28l.204-2.91 5.324-4.808c.232-.204-.05-.32-.358-.115l-6.58 4.142-2.835-.885c-.616-.192-.628-.616.128-.912L16.4 7.057c.513-.192.96.115.587.699z"/>
                        </svg>
                        Join Channel
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('menu-icon');
            const closeIcon = document.getElementById('close-icon');

            menu.classList.toggle('active');
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        }
    </script>

    <!-- Hero Section -->
    <section class="py-16 px-4" style="background-color: #141414;">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">
                Tentang <span class="text-accent">Dracin HD</span>
            </h1>
            <p class="text-xl text-secondary leading-relaxed">
                Platform streaming film drama china terpercaya yang memberikan pengalaman menonton berkualitas HD dengan harga terjangkau
            </p>
        </div>
    </section>

    <!-- Content -->
    <main class="py-16 px-4">
        <div class="max-w-6xl mx-auto">

            <!-- Our Story -->
            <section class="mb-16">
                <div class="card-dark rounded-2xl p-8 md:p-12">
                    <h2 class="text-3xl font-bold mb-6">Cerita Kami</h2>
                    <div class="space-y-4 text-secondary leading-relaxed text-lg">
                        <p>
                            Dracin HD lahir dari kecintaan kami terhadap film drama china berkualitas tinggi. Kami memahami bahwa banyak penggemar drama china di Indonesia yang kesulitan mengakses konten berkualitas dengan subtitle Indonesia yang baik.
                        </p>
                        <p>
                            Dengan memanfaatkan teknologi Telegram bot, kami menghadirkan solusi streaming yang mudah, cepat, dan terjangkau. Tidak perlu aplikasi tambahan, tidak perlu ribet - cukup dengan Telegram yang sudah ada di smartphone Anda.
                        </p>
                        <p>
                            Kami berkomitmen untuk terus menghadirkan konten-konten terbaik dengan kualitas HD dan subtitle Indonesia yang akurat, sehingga Anda dapat menikmati setiap momen dalam film dengan maksimal.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Our Values -->
            <section class="mb-16">
                <h2 class="text-3xl font-bold mb-8 text-center">Nilai-Nilai Kami</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="card-dark rounded-xl p-8 text-center transition-all duration-300">
                        <div class="text-5xl mb-4">🎯</div>
                        <h3 class="text-xl font-semibold mb-3">Kualitas Terbaik</h3>
                        <p class="text-secondary">
                            Kami hanya menyediakan film dengan kualitas HD dan subtitle Indonesia yang berkualitas untuk pengalaman menonton terbaik.
                        </p>
                    </div>
                    <div class="card-dark rounded-xl p-8 text-center transition-all duration-300">
                        <div class="text-5xl mb-4">💰</div>
                        <h3 class="text-xl font-semibold mb-3">Harga Terjangkau</h3>
                        <p class="text-secondary">
                            Kami percaya hiburan berkualitas harus dapat diakses semua orang dengan harga yang ramah di kantong.
                        </p>
                    </div>
                    <div class="card-dark rounded-xl p-8 text-center transition-all duration-300">
                        <div class="text-5xl mb-4">⚡</div>
                        <h3 class="text-xl font-semibold mb-3">Kemudahan Akses</h3>
                        <p class="text-secondary">
                            Teknologi Telegram bot memungkinkan Anda menonton kapan saja, di mana saja tanpa instalasi aplikasi tambahan.
                        </p>
                    </div>
                </div>
            </section>

            <!-- What We Offer -->
            <section class="mb-16">
                <div class="card-dark rounded-2xl p-8 md:p-12">
                    <h2 class="text-3xl font-bold mb-8">Apa yang Kami Tawarkan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-600 to-orange-400 flex items-center justify-center">
                                    <span class="text-2xl">🎬</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-2">Koleksi Film Lengkap</h3>
                                <p class="text-secondary">Ribuan film drama china dari berbagai genre: romantis, action, historical, fantasy, dan lainnya.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-600 to-orange-400 flex items-center justify-center">
                                    <span class="text-2xl">🎥</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-2">Kualitas HD 1080p</h3>
                                <p class="text-secondary">Semua film tersedia dalam kualitas HD untuk pengalaman visual yang memukau.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-600 to-orange-400 flex items-center justify-center">
                                    <span class="text-2xl">📝</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-2">Subtitle Indonesia</h3>
                                <p class="text-secondary">Semua film dilengkapi dengan subtitle Indonesia yang akurat dan mudah dibaca.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-600 to-orange-400 flex items-center justify-center">
                                    <span class="text-2xl">🔄</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-2">Update Berkala</h3>
                                <p class="text-secondary">Katalog film kami terus diperbarui dengan rilis-rilis terbaru dan populer.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-600 to-orange-400 flex items-center justify-center">
                                    <span class="text-2xl">💳</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-2">Pembayaran Mudah</h3>
                                <p class="text-secondary">Berbagai metode pembayaran: QRIS dan Virtual Account dari bank-bank terpercaya.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-600 to-orange-400 flex items-center justify-center">
                                    <span class="text-2xl">🛡️</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg mb-2">Keamanan Terjamin</h3>
                                <p class="text-secondary">Data dan transaksi Anda dilindungi dengan teknologi enkripsi terkini.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Statistics -->
            <section class="mb-16">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="card-dark rounded-xl p-6 text-center">
                        <div class="text-4xl font-bold text-accent mb-2">1000+</div>
                        <div class="text-secondary">Film Tersedia</div>
                    </div>
                    <div class="card-dark rounded-xl p-6 text-center">
                        <div class="text-4xl font-bold text-accent mb-2">24/7</div>
                        <div class="text-secondary">Akses Streaming</div>
                    </div>
                    <div class="card-dark rounded-xl p-6 text-center">
                        <div class="text-4xl font-bold text-accent mb-2">HD</div>
                        <div class="text-secondary">Kualitas 1080p</div>
                    </div>
                    <div class="card-dark rounded-xl p-6 text-center">
                        <div class="text-4xl font-bold text-accent mb-2">100%</div>
                        <div class="text-secondary">Sub Indonesia</div>
                    </div>
                </div>
            </section>

            <!-- Why Choose Us -->
            <section class="mb-16">
                <div class="card-dark rounded-2xl p-8 md:p-12">
                    <h2 class="text-3xl font-bold mb-8">Mengapa Memilih Dracin HD?</h2>
                    <div class="space-y-6 text-secondary leading-relaxed text-lg">
                        <div class="flex gap-4">
                            <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <strong class="text-white">Tanpa Aplikasi Tambahan</strong> - Cukup gunakan Telegram yang sudah ada di smartphone Anda
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <strong class="text-white">Proses Otomatis</strong> - VIP aktif langsung setelah pembayaran berhasil, tidak perlu menunggu lama
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <strong class="text-white">Paket Fleksibel</strong> - Pilih durasi sesuai kebutuhan, mulai dari 1 hari hingga 30 hari
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <strong class="text-white">Support Responsif</strong> - Tim kami siap membantu melalui email dan Telegram
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <strong class="text-white">Unlimited Streaming</strong> - Tonton sepuasnya tanpa batasan selama masa VIP aktif
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contact Section -->
            <section>
                <div class="card-dark rounded-2xl p-8 md:p-12 text-center">
                    <h2 class="text-3xl font-bold mb-4">Siap Bergabung?</h2>
                    <p class="text-secondary text-lg mb-8">Mulai nikmati ribuan film drama china berkualitas HD sekarang juga!</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('landing') }}#pricing" class="btn-primary inline-block py-3 px-8 rounded-lg text-white font-semibold">
                            Lihat Paket VIP
                        </a>
                        <a href="{{ route('contact') }}" class="inline-block py-3 px-8 rounded-lg text-white font-semibold border-2 border-accent hover:bg-accent transition-all">
                            Hubungi Kami
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- Footer -->
    <footer class="py-8 px-4" style="background-color: #141414; border-top: 1px solid #2B2B2B;">
        <div class="max-w-6xl mx-auto text-center text-secondary">
            <p class="text-sm">&copy; {{ date('Y') }} Dracin HD. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
