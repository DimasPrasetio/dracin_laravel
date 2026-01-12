<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi - Dracin HD</title>
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
                    <a href="{{ route('about') }}" class="block py-2 text-secondary hover:text-accent transition-colors">Tentang Kami</a>
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

    <!-- Content -->
    <main class="py-16 px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Kebijakan Privasi</h1>
            <p class="text-secondary mb-8">Terakhir diperbarui: {{ date('d F Y') }}</p>

            <div class="space-y-8 text-secondary leading-relaxed">
                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">1. Informasi yang Kami Kumpulkan</h2>
                    <p class="mb-4">Dracin HD mengumpulkan informasi berikut untuk menyediakan layanan streaming film drama china:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li><strong class="text-white">Telegram User ID:</strong> Untuk mengidentifikasi dan mengaktifkan akses VIP Anda</li>
                        <li><strong class="text-white">Nama Pengguna Telegram:</strong> Untuk komunikasi dan verifikasi akun</li>
                        <li><strong class="text-white">Username Telegram (opsional):</strong> Untuk memudahkan identifikasi</li>
                        <li><strong class="text-white">Informasi Transaksi:</strong> Detail pembayaran untuk proses langganan VIP</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">2. Penggunaan Informasi</h2>
                    <p class="mb-4">Informasi yang kami kumpulkan digunakan untuk:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Mengaktifkan dan mengelola akses VIP Anda</li>
                        <li>Memproses pembayaran dan transaksi</li>
                        <li>Memberikan dukungan pelanggan</li>
                        <li>Mengirimkan notifikasi terkait layanan</li>
                        <li>Meningkatkan kualitas layanan kami</li>
                        <li>Mencegah penyalahgunaan dan aktivitas penipuan</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">3. Keamanan Data</h2>
                    <p class="mb-4">Kami berkomitmen untuk melindungi data pribadi Anda dengan:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Enkripsi data saat transmisi</li>
                        <li>Penyimpanan data yang aman</li>
                        <li>Akses terbatas hanya untuk personel yang berwenang</li>
                        <li>Pemantauan sistem keamanan secara berkala</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">4. Berbagi Informasi dengan Pihak Ketiga</h2>
                    <p class="mb-4">Kami tidak menjual, memperdagangkan, atau mentransfer informasi pribadi Anda kepada pihak ketiga tanpa persetujuan Anda, kecuali:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li><strong class="text-white">Penyedia Payment Gateway:</strong> Untuk memproses pembayaran Anda</li>
                        <li><strong class="text-white">Telegram:</strong> Platform yang kami gunakan untuk memberikan layanan</li>
                        <li><strong class="text-white">Kewajiban Hukum:</strong> Jika diwajibkan oleh hukum yang berlaku</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">5. Hak Pengguna</h2>
                    <p class="mb-4">Anda memiliki hak untuk:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Mengakses informasi pribadi yang kami simpan tentang Anda</li>
                        <li>Meminta koreksi data yang tidak akurat</li>
                        <li>Meminta penghapusan data pribadi Anda</li>
                        <li>Menarik persetujuan penggunaan data (dengan konsekuensi penghentian layanan)</li>
                    </ul>
                    <p class="mt-4">Untuk menggunakan hak-hak ini, silakan hubungi kami melalui kontak yang tersedia.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">6. Cookies dan Teknologi Pelacakan</h2>
                    <p>Website kami menggunakan cookies untuk meningkatkan pengalaman pengguna. Cookies adalah file kecil yang disimpan di perangkat Anda untuk mengingat preferensi dan aktivitas browsing Anda.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">7. Retensi Data</h2>
                    <p>Kami menyimpan data pribadi Anda selama akun VIP Anda aktif dan periode tertentu setelahnya untuk tujuan hukum dan administratif. Data transaksi dapat disimpan lebih lama sesuai dengan persyaratan hukum.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">8. Perubahan Kebijakan Privasi</h2>
                    <p>Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan signifikan akan diumumkan melalui channel Telegram kami atau melalui notifikasi di website.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">9. Kontak</h2>
                    <p class="mb-4">Jika Anda memiliki pertanyaan tentang Kebijakan Privasi ini, silakan hubungi kami:</p>
                    <ul class="space-y-2">
                        <li><strong class="text-white">Email:</strong> <a href="mailto:amandayora1@gmail.com" class="text-accent hover:underline">amandayora1@gmail.com</a></li>
                        <li><strong class="text-white">Telegram:</strong> <a href="https://t.me/maharu01" target="_blank" class="text-accent hover:underline">@maharu01</a></li>
                        <li><strong class="text-white">Alamat:</strong> GLand Ciwastra Park, Jl. Cendana V no 3, Bojongsoang, Kab. Bandung</li>
                    </ul>
                </section>
            </div>

            <div class="mt-12">
                <a href="{{ route('landing') }}" class="btn-primary inline-block py-3 px-8 rounded-lg text-white font-semibold">
                    Kembali ke Beranda
                </a>
            </div>
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
