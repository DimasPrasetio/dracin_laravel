<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak & Support - Dracin HD</title>
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
                    <a href="{{ route('about') }}" class="nav-link text-secondary hover:text-accent">Tentang Kami</a>
                    <a href="{{ route('contact') }}" class="nav-link text-white hover:text-accent">Kontak</a>
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
                    <a href="{{ route('about') }}" class="block py-2 text-secondary hover:text-accent transition-colors">Tentang Kami</a>
                    <a href="{{ route('contact') }}" class="block py-2 text-white hover:text-accent transition-colors">Kontak</a>
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
                Kontak & <span class="text-accent">Support</span>
            </h1>
            <p class="text-xl text-secondary leading-relaxed">
                Kami siap membantu Anda! Hubungi kami melalui saluran yang tersedia di bawah ini
            </p>
        </div>
    </section>

    <!-- Content -->
    <main class="py-16 px-4">
        <div class="max-w-6xl mx-auto">

            <!-- Contact Methods -->
            <section class="mb-16">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Email -->
                    <div class="card-dark rounded-xl p-8 text-center transition-all duration-300">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-orange-600 to-orange-400 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Email</h3>
                        <p class="text-secondary mb-4">Untuk pertanyaan umum dan support</p>
                        <a href="mailto:amandayora1@gmail.com" class="text-accent hover:text-orange-400 transition-colors break-all">
                            amandayora1@gmail.com
                        </a>
                    </div>

                    <!-- Telegram -->
                    <div class="card-dark rounded-xl p-8 text-center transition-all duration-300">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-orange-600 to-orange-400 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm4.987 7.756l-1.875 8.82c-.14.644-.51.803-1.034.5l-2.864-2.11-1.38 1.328c-.153.153-.28.28-.576.28l.204-2.91 5.324-4.808c.232-.204-.05-.32-.358-.115l-6.58 4.142-2.835-.885c-.616-.192-.628-.616.128-.912L16.4 7.057c.513-.192.96.115.587.699z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Telegram Admin</h3>
                        <p class="text-secondary mb-4">Respon cepat untuk bantuan langsung</p>
                        <a href="https://t.me/maharu01" target="_blank" class="text-accent hover:text-orange-400 transition-colors">
                            @maharu01
                        </a>
                    </div>

                    <!-- Channel -->
                    <div class="card-dark rounded-xl p-8 text-center transition-all duration-300">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-orange-600 to-orange-400 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm4.987 7.756l-1.875 8.82c-.14.644-.51.803-1.034.5l-2.864-2.11-1.38 1.328c-.153.153-.28.28-.576.28l.204-2.91 5.324-4.808c.232-.204-.05-.32-.358-.115l-6.58 4.142-2.835-.885c-.616-.192-.628-.616.128-.912L16.4 7.057c.513-.192.96.115.587.699z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Channel Telegram</h3>
                        <p class="text-secondary mb-4">Join untuk akses katalog film</p>
                        <a href="https://t.me/dracin_hd" target="_blank" class="text-accent hover:text-orange-400 transition-colors">
                            @dracin_hd
                        </a>
                    </div>
                </div>
            </section>

            <!-- Office Address -->
            <section class="mb-16">
                <div class="card-dark rounded-2xl p-8 md:p-12">
                    <div class="flex flex-col md:flex-row gap-8 items-start">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-orange-600 to-orange-400 flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <h2 class="text-2xl font-bold mb-4">Alamat Kantor</h2>
                            <p class="text-secondary text-lg leading-relaxed mb-4">
                                GLand Ciwastra Park<br>
                                Jl. Cendana V no 3<br>
                                Bojongsoang, Kabupaten Bandung<br>
                                Jawa Barat, Indonesia
                            </p>
                            <p class="text-secondary">
                                <strong class="text-white">Catatan:</strong> Kami adalah layanan online. Untuk bantuan cepat, silakan hubungi kami melalui email atau Telegram.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section class="mb-16">
                <h2 class="text-3xl font-bold mb-8 text-center">Pertanyaan yang Sering Diajukan</h2>
                <div class="space-y-6 max-w-4xl mx-auto">
                    <div class="card-dark rounded-lg p-6">
                        <h3 class="font-semibold text-lg mb-2">Berapa lama waktu respons support?</h3>
                        <p class="text-secondary">Tim support kami biasanya merespons dalam 1-24 jam pada hari kerja. Untuk bantuan cepat, silakan hubungi melalui Telegram admin kami.</p>
                    </div>

                    <div class="card-dark rounded-lg p-6">
                        <h3 class="font-semibold text-lg mb-2">Saya mengalami masalah pembayaran, apa yang harus dilakukan?</h3>
                        <p class="text-secondary">Jika pembayaran Anda sudah berhasil namun VIP belum aktif dalam 1 jam, segera hubungi kami melalui Telegram dengan melampirkan bukti pembayaran dan Telegram User ID Anda.</p>
                    </div>

                    <div class="card-dark rounded-lg p-6">
                        <h3 class="font-semibold text-lg mb-2">Bagaimana cara memperpanjang VIP?</h3>
                        <p class="text-secondary">Anda dapat memperpanjang VIP kapan saja dengan melakukan pembelian paket baru melalui website kami. Durasi VIP baru akan ditambahkan ke masa aktif yang tersisa.</p>
                    </div>

                    <div class="card-dark rounded-lg p-6">
                        <h3 class="font-semibold text-lg mb-2">Film yang saya cari tidak tersedia, bagaimana?</h3>
                        <p class="text-secondary">Silakan hubungi kami dan berikan judul film yang Anda inginkan. Kami akan berusaha menambahkannya ke katalog kami jika memungkinkan.</p>
                    </div>

                    <div class="card-dark rounded-lg p-6">
                        <h3 class="font-semibold text-lg mb-2">Apakah ada diskon untuk pembelian paket tertentu?</h3>
                        <p class="text-secondary">Kami secara berkala mengadakan promo khusus. Follow channel Telegram kami untuk mendapatkan informasi promo terbaru dan penawaran spesial.</p>
                    </div>
                </div>
            </section>

            <!-- Business Hours -->
            <section class="mb-16">
                <div class="card-dark rounded-2xl p-8 md:p-12 text-center">
                    <h2 class="text-2xl font-bold mb-6">Jam Operasional Support</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto text-left">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-accent flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <strong class="text-white">Senin - Jumat</strong>
                                <p class="text-secondary">09:00 - 21:00 WIB</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-accent flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <strong class="text-white">Sabtu - Minggu</strong>
                                <p class="text-secondary">10:00 - 18:00 WIB</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-secondary mt-6">
                        <strong class="text-white">Catatan:</strong> Layanan streaming tersedia 24/7. Jam operasional di atas hanya untuk customer support.
                    </p>
                </div>
            </section>

            <!-- CTA Section -->
            <section>
                <div class="card-dark rounded-2xl p-8 md:p-12 text-center">
                    <h2 class="text-3xl font-bold mb-4">Butuh Bantuan Sekarang?</h2>
                    <p class="text-secondary text-lg mb-8">Pilih cara terbaik untuk menghubungi kami</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="https://t.me/maharu01" target="_blank" class="btn-primary inline-flex items-center justify-center gap-2 py-3 px-8 rounded-lg text-white font-semibold">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm4.987 7.756l-1.875 8.82c-.14.644-.51.803-1.034.5l-2.864-2.11-1.38 1.328c-.153.153-.28.28-.576.28l.204-2.91 5.324-4.808c.232-.204-.05-.32-.358-.115l-6.58 4.142-2.835-.885c-.616-.192-.628-.616.128-.912L16.4 7.057c.513-.192.96.115.587.699z"/>
                            </svg>
                            Chat via Telegram
                        </a>
                        <a href="mailto:amandayora1@gmail.com" class="inline-flex items-center justify-center gap-2 py-3 px-8 rounded-lg text-white font-semibold border-2 border-accent hover:bg-accent transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            Kirim Email
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
