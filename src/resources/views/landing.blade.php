<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroSangapati - Sistem Informasi Manajemen Gapoktan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fas fa-seedling text-green-600 text-2xl mr-3"></i>
                    <span class="text-xl font-bold text-gray-800">AgroSangapati</span>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="#home" class="text-gray-700 hover:text-green-600 transition">Beranda</a>
                    <a href="#features" class="text-gray-700 hover:text-green-600 transition">Fitur</a>
                    <a href="#about" class="text-gray-700 hover:text-green-600 transition">Tentang</a>
                    <a href="#contact" class="text-gray-700 hover:text-green-600 transition">Kontak</a>
                </div>
                <div>
                    <a href="/admin/login" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="pt-20 pb-16 bg-gradient-to-br from-green-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-5xl font-bold text-gray-900 mb-6">
                        Sistem Manajemen Keuangan <span class="text-green-600">Gapoktan</span> Modern
                    </h1>
                    <p class="text-xl text-gray-600 mb-8">
                        Kelola keuangan, transaksi, dan laporan Gabungan Kelompok Tani Anda dengan mudah, transparan, dan efisien.
                    </p>
                    <div class="flex space-x-4">
                        <a href="/admin/login" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition text-lg font-semibold">
                            Mulai Sekarang
                        </a>
                        <a href="#features" class="bg-white text-green-600 px-8 py-3 rounded-lg hover:bg-gray-50 transition text-lg font-semibold border-2 border-green-600">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="bg-white rounded-2xl shadow-2xl p-8">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-money-bill-wave text-green-600 text-2xl mr-3"></i>
                                    <div>
                                        <p class="text-sm text-gray-600">Pendapatan Bulan Ini</p>
                                        <p class="text-2xl font-bold text-gray-900">Rp 46.150.000</p>
                                    </div>
                                </div>
                                <span class="text-green-600 font-semibold">+0%</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-shopping-cart text-blue-600 text-2xl mr-3"></i>
                                    <div>
                                        <p class="text-sm text-gray-600">Pengeluaran Bulan Ini</p>
                                        <p class="text-2xl font-bold text-gray-900">Rp 8.800.000</p>
                                    </div>
                                </div>
                                <span class="text-green-600 font-semibold">-37%</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-clock text-yellow-600 text-2xl mr-3"></i>
                                    <div>
                                        <p class="text-sm text-gray-600">Menunggu Persetujuan</p>
                                        <p class="text-2xl font-bold text-gray-900">0 Transaksi</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Fitur Unggulan</h2>
                <p class="text-xl text-gray-600">Solusi lengkap untuk manajemen Gapoktan Anda</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-green-600 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Dashboard Interaktif</h3>
                    <p class="text-gray-600">
                        Pantau pendapatan, pengeluaran, dan tren keuangan dalam satu tampilan yang mudah dipahami dengan grafik real-time.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-blue-600 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-exchange-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Manajemen Transaksi</h3>
                    <p class="text-gray-600">
                        Catat semua transaksi pemasukan dan pengeluaran dengan sistem approval berlapis untuk transparansi maksimal.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-purple-600 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-file-invoice text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Laporan Keuangan</h3>
                    <p class="text-gray-600">
                        Generate laporan keuangan lengkap dengan saldo kas, rincian transaksi, dan analisis keuangan bulanan.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-yellow-600 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Multi-level User</h3>
                    <p class="text-gray-600">
                        Sistem role-based access untuk Admin, Ketua, Bendahara, dan Anggota dengan hak akses yang berbeda.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-red-600 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-shopping-bag text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Manajemen Produk</h3>
                    <p class="text-gray-600">
                        Kelola produk pertanian, stok, harga, dan pesanan dengan sistem yang terintegrasi penuh.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-indigo-600 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Keamanan Data</h3>
                    <p class="text-gray-600">
                        Data Anda terlindungi dengan enkripsi dan sistem backup otomatis untuk mencegah kehilangan data.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 bg-green-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 text-center">
                <div>
                    <i class="fas fa-users text-5xl mb-4 opacity-80"></i>
                    <p class="text-4xl font-bold mb-2">100+</p>
                    <p class="text-lg opacity-90">Anggota Gapoktan</p>
                </div>
                <div>
                    <i class="fas fa-exchange-alt text-5xl mb-4 opacity-80"></i>
                    <p class="text-4xl font-bold mb-2">1000+</p>
                    <p class="text-lg opacity-90">Transaksi Tercatat</p>
                </div>
                <div>
                    <i class="fas fa-box text-5xl mb-4 opacity-80"></i>
                    <p class="text-4xl font-bold mb-2">50+</p>
                    <p class="text-lg opacity-90">Produk Pertanian</p>
                </div>
                <div>
                    <i class="fas fa-chart-line text-5xl mb-4 opacity-80"></i>
                    <p class="text-4xl font-bold mb-2">99.9%</p>
                    <p class="text-lg opacity-90">Akurasi Laporan</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl font-bold text-gray-900 mb-6">Tentang AgroSangapati</h2>
                    <p class="text-lg text-gray-600 mb-6">
                        AgroSangapati adalah sistem informasi manajemen yang dirancang khusus untuk Gabungan Kelompok Tani (Gapoktan) guna meningkatkan efisiensi pengelolaan keuangan dan operasional.
                    </p>
                    <p class="text-lg text-gray-600 mb-6">
                        Dengan teknologi modern dan antarmuka yang user-friendly, kami membantu petani dan kelompok tani mengelola bisnis pertanian mereka dengan lebih profesional dan transparan.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 text-xl mr-3 mt-1"></i>
                            <div>
                                <p class="font-semibold text-gray-900">Mudah Digunakan</p>
                                <p class="text-gray-600">Antarmuka intuitif yang dapat digunakan tanpa pelatihan khusus</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 text-xl mr-3 mt-1"></i>
                            <div>
                                <p class="font-semibold text-gray-900">Transparan & Akuntabel</p>
                                <p class="text-gray-600">Semua transaksi tercatat dengan audit trail lengkap</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 text-xl mr-3 mt-1"></i>
                            <div>
                                <p class="font-semibold text-gray-900">Laporan Real-time</p>
                                <p class="text-gray-600">Akses laporan keuangan kapan saja, dimana saja</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="bg-gradient-to-br from-green-100 to-blue-100 rounded-2xl p-8">
                        <img src="https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=600" alt="Farmers" class="rounded-lg shadow-xl w-full">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-green-600 to-blue-600 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold mb-6">Siap Modernisasi Manajemen Gapoktan Anda?</h2>
            <p class="text-xl mb-8 opacity-90">
                Bergabunglah dengan ratusan Gapoktan yang telah menggunakan AgroSangapati untuk meningkatkan efisiensi dan transparansi keuangan mereka.
            </p>
            <a href="/admin/login" class="inline-block bg-white text-green-600 px-10 py-4 rounded-lg hover:bg-gray-100 transition text-lg font-bold shadow-lg">
                Coba Sekarang - Gratis!
            </a>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Hubungi Kami</h2>
                <p class="text-xl text-gray-600">Punya pertanyaan? Tim kami siap membantu Anda</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                    <i class="fas fa-map-marker-alt text-green-600 text-4xl mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Alamat</h3>
                    <p class="text-gray-600">Jl. Pertanian No. 123<br>Kota, Provinsi 12345</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                    <i class="fas fa-phone text-green-600 text-4xl mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Telepon</h3>
                    <p class="text-gray-600">+62 812-3456-7890<br>Senin - Jumat, 08:00 - 17:00</p>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                    <i class="fas fa-envelope text-green-600 text-4xl mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Email</h3>
                    <p class="text-gray-600">info@agrosangapati.com<br>support@agrosangapati.com</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-seedling text-green-500 text-3xl mr-3"></i>
                        <span class="text-2xl font-bold">AgroSangapati</span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        Sistem Informasi Manajemen Gapoktan yang modern, efisien, dan transparan untuk mendukung pertanian Indonesia yang lebih maju.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-green-500 transition"><i class="fab fa-facebook text-2xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-green-500 transition"><i class="fab fa-twitter text-2xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-green-500 transition"><i class="fab fa-instagram text-2xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-green-500 transition"><i class="fab fa-linkedin text-2xl"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Menu</h4>
                    <ul class="space-y-2">
                        <li><a href="#home" class="text-gray-400 hover:text-green-500 transition">Beranda</a></li>
                        <li><a href="#features" class="text-gray-400 hover:text-green-500 transition">Fitur</a></li>
                        <li><a href="#about" class="text-gray-400 hover:text-green-500 transition">Tentang</a></li>
                        <li><a href="#contact" class="text-gray-400 hover:text-green-500 transition">Kontak</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-bold mb-4">Lainnya</h4>
                    <ul class="space-y-2">
                        <li><a href="/admin/login" class="text-gray-400 hover:text-green-500 transition">Login</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-green-500 transition">Dokumentasi</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-green-500 transition">Kebijakan Privasi</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-green-500 transition">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 AgroSangapati. All rights reserved. Developed with <i class="fas fa-heart text-red-500"></i> for Indonesian Farmers.</p>
            </div>
        </div>
    </footer>

    <!-- Smooth Scroll Script -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
