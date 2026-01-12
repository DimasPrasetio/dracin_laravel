<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ketentuan Layanan - Dracin HD</title>
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
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Ketentuan Layanan</h1>
            <p class="text-secondary mb-8">Terakhir diperbarui: {{ date('d F Y') }}</p>

            <div class="space-y-8 text-secondary leading-relaxed">
                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">1. Penerimaan Ketentuan</h2>
                    <p>Dengan mengakses dan menggunakan layanan Dracin HD, Anda menyetujui untuk terikat oleh Ketentuan Layanan ini. Jika Anda tidak setuju dengan ketentuan ini, harap tidak menggunakan layanan kami.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">2. Deskripsi Layanan</h2>
                    <p class="mb-4">Dracin HD adalah platform streaming film drama china yang menyediakan:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Akses ke koleksi film drama china berkualitas HD</li>
                        <li>Subtitle Indonesia untuk semua konten</li>
                        <li>Streaming melalui bot Telegram</li>
                        <li>Berbagai paket langganan VIP</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">3. Akun dan Langganan</h2>
                    <p class="mb-4"><strong class="text-white">3.1 Registrasi</strong></p>
                    <ul class="list-disc list-inside space-y-2 ml-4 mb-4">
                        <li>Anda harus memiliki akun Telegram yang valid untuk menggunakan layanan kami</li>
                        <li>Anda bertanggung jawab untuk menjaga kerahasiaan informasi akun Anda</li>
                        <li>Anda harus memberikan informasi yang akurat dan terkini</li>
                    </ul>

                    <p class="mb-4"><strong class="text-white">3.2 Paket VIP</strong></p>
                    <ul class="list-disc list-inside space-y-2 ml-4 mb-4">
                        <li>Akses VIP berlaku sesuai durasi paket yang dipilih (1, 3, 7, atau 30 hari)</li>
                        <li>Pembayaran harus dilakukan di muka</li>
                        <li>VIP akan aktif otomatis setelah pembayaran berhasil diverifikasi</li>
                        <li>Tidak ada perpanjangan otomatis, Anda harus berlangganan ulang setelah masa aktif berakhir</li>
                    </ul>

                    <p class="mb-4"><strong class="text-white">3.3 Pembatasan Akun</strong></p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Satu akun VIP hanya untuk satu pengguna</li>
                        <li>Dilarang berbagi akun dengan orang lain</li>
                        <li>Pelanggaran dapat mengakibatkan penangguhan atau penghapusan akun</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">4. Pembayaran dan Pengembalian Dana</h2>
                    <p class="mb-4"><strong class="text-white">4.1 Metode Pembayaran</strong></p>
                    <p class="mb-4">Kami menerima pembayaran melalui QRIS dan Virtual Account dari berbagai bank (BCA, BNI, BRI, Mandiri).</p>

                    <p class="mb-4"><strong class="text-white">4.2 Kebijakan Pengembalian Dana</strong></p>
                    <ul class="list-disc list-inside space-y-2 ml-4 mb-4">
                        <li>Pembayaran yang sudah diproses tidak dapat dikembalikan</li>
                        <li>Pengembalian dana hanya berlaku jika terjadi kesalahan teknis dari pihak kami</li>
                        <li>Permintaan pengembalian dana harus diajukan maksimal 24 jam setelah pembayaran</li>
                        <li>Proses pengembalian dana memerlukan waktu 5-14 hari kerja</li>
                    </ul>

                    <p class="mb-4"><strong class="text-white">4.3 Harga</strong></p>
                    <p>Kami berhak mengubah harga kapan saja. Perubahan harga tidak akan mempengaruhi langganan yang sudah aktif.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">5. Penggunaan Layanan</h2>
                    <p class="mb-4"><strong class="text-white">5.1 Penggunaan yang Diperbolehkan</strong></p>
                    <ul class="list-disc list-inside space-y-2 ml-4 mb-4">
                        <li>Menonton film untuk keperluan pribadi</li>
                        <li>Streaming melalui bot Telegram resmi kami</li>
                        <li>Mengakses konten selama masa VIP aktif</li>
                    </ul>

                    <p class="mb-4"><strong class="text-white">5.2 Penggunaan yang Dilarang</strong></p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Mengunduh, merekam, atau mendistribusikan konten</li>
                        <li>Menggunakan bot atau tools otomatis untuk mengakses layanan</li>
                        <li>Berbagi akun VIP dengan pihak lain</li>
                        <li>Memodifikasi, reverse engineer, atau dekompilasi layanan</li>
                        <li>Melakukan aktivitas yang melanggar hukum</li>
                        <li>Menggunakan VPN atau proxy untuk menyembunyikan lokasi</li>
                        <li>Mempublikasikan atau menjual kembali konten kami</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">6. Hak Kekayaan Intelektual</h2>
                    <p class="mb-4">Semua konten yang tersedia di Dracin HD, termasuk film, subtitle, logo, dan desain website, dilindungi oleh hak cipta dan hak kekayaan intelektual lainnya. Anda tidak diperbolehkan untuk:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Menyalin, mereproduksi, atau mendistribusikan konten</li>
                        <li>Menggunakan konten untuk tujuan komersial</li>
                        <li>Menghapus atau memodifikasi watermark atau credit</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">7. Penangguhan dan Penghapusan Akun</h2>
                    <p class="mb-4">Kami berhak untuk menangguhkan atau menghapus akun Anda tanpa pemberitahuan sebelumnya jika:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Anda melanggar Ketentuan Layanan ini</li>
                        <li>Anda melakukan aktivitas penipuan atau ilegal</li>
                        <li>Anda berbagi akun dengan pihak lain</li>
                        <li>Kami menerima permintaan dari pihak berwenang</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">8. Batasan Tanggung Jawab</h2>
                    <p class="mb-4">Dracin HD tidak bertanggung jawab atas:</p>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Gangguan layanan karena masalah teknis di luar kendali kami</li>
                        <li>Kehilangan data atau konten</li>
                        <li>Kerusakan yang timbul dari penggunaan layanan</li>
                        <li>Konten yang mungkin dianggap tidak pantas oleh sebagian pengguna</li>
                        <li>Masalah koneksi internet pengguna</li>
                        <li>Perubahan atau penghapusan konten dari katalog kami</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">9. Perubahan Layanan</h2>
                    <p>Kami berhak untuk mengubah, memodifikasi, atau menghentikan layanan (atau bagian dari layanan) kapan saja tanpa pemberitahuan sebelumnya. Kami juga berhak untuk menambah atau mengurangi konten yang tersedia.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">10. Privasi</h2>
                    <p>Penggunaan layanan kami juga diatur oleh <a href="{{ route('privacy') }}" class="text-accent hover:underline">Kebijakan Privasi</a> kami. Harap baca Kebijakan Privasi untuk memahami bagaimana kami mengumpulkan dan menggunakan informasi Anda.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">11. Hukum yang Berlaku</h2>
                    <p>Ketentuan Layanan ini diatur dan ditafsirkan sesuai dengan hukum Republik Indonesia. Setiap sengketa yang timbul akan diselesaikan di pengadilan yang berwenang di Indonesia.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">12. Perubahan Ketentuan</h2>
                    <p>Kami dapat memperbarui Ketentuan Layanan ini dari waktu ke waktu. Perubahan akan berlaku segera setelah dipublikasikan di website. Penggunaan layanan setelah perubahan dianggap sebagai persetujuan terhadap ketentuan baru.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-white mb-4">13. Kontak</h2>
                    <p class="mb-4">Jika Anda memiliki pertanyaan tentang Ketentuan Layanan ini, silakan hubungi kami:</p>
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
