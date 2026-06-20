<?php
require_once '../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Query data user
$query_user = "SELECT * FROM users WHERE id = ?";
$stmt = $koneksi->prepare($query_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user = $result_user->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantuan - MySavings</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #F5F7FA;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            background: linear-gradient(180deg, #1A2B4A 0%, #0F1620 100%);
            color: white;
            padding: 30px 20px;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
        }

        .sidebar-header {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header .title {
            display: block;
            margin-bottom: 5px;
        }

        .sidebar-header .subtitle {
            font-size: 12px;
            font-weight: 400;
            opacity: 0.7;
            display: block;
            margin-bottom: 30px;
        }

        .menu-section {
            margin-bottom: 30px;
        }

        .menu-section-title {
            font-size: 11px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 15px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 8px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #B8C5D6;
            font-size: 14px;
        }

        .menu-item:hover {
            background-color: rgba(74, 144, 226, 0.2);
            color: #4A90E2;
        }

        .menu-item.active {
            background-color: #4A90E2;
            color: white;
        }

        .menu-item svg {
            width: 20px;
            height: 20px;
            margin-right: 12px;
        }

        .menu-divider {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin: 20px 0;
        }

        .btn-add-transaction {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #4A90E2 0%, #8B5CF6 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            transition: transform 0.2s ease;
        }

        .btn-add-transaction:hover {
            transform: translateY(-2px);
        }

        .btn-add-transaction svg {
            width: 16px;
            height: 16px;
            margin-right: 8px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 220px;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .header-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4A90E2 0%, #8B5CF6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .user-name {
            font-size: 14px;
            font-weight: 500;
        }

        /* Help Content */
        .help-content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        .intro-section {
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 2px solid #E5E7EB;
        }

        .intro-section h2 {
            font-size: 24px;
            color: #1A2B4A;
            margin-bottom: 15px;
        }

        .intro-section p {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 10px;
        }

        .help-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
        }

        .help-section-title {
            display: flex;
            align-items: center;
            font-size: 22px;
            font-weight: 700;
            color: #1A2B4A;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #4A90E2;
        }

        .help-section-title::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #4A90E2;
            border-radius: 50%;
            margin-right: 12px;
        }

        .help-item {
            background: #F9FAFB;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #4A90E2;
        }

        .help-item-title {
            font-size: 16px;
            font-weight: 600;
            color: #1A2B4A;
            margin-bottom: 10px;
        }

        .help-item-description {
            font-size: 14px;
            color: #555;
            line-height: 1.6;
        }

        .help-item-features {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #E5E7EB;
        }

        .feature-list {
            list-style: none;
            margin-top: 8px;
        }

        .feature-list li {
            font-size: 14px;
            color: #555;
            margin-bottom: 6px;
            padding-left: 20px;
            position: relative;
        }

        .feature-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #10B981;
            font-weight: bold;
        }

        .tips-section {
            background: linear-gradient(135deg, #E3F2FD 0%, #F3E5F5 100%);
            padding: 25px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #8B5CF6;
        }

        .tips-section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1A2B4A;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        .tips-section-title::before {
            content: '💡';
            margin-right: 8px;
        }

        .tips-list {
            list-style: none;
        }

        .tips-list li {
            font-size: 14px;
            color: #333;
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
        }

        .tips-list li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: #8B5CF6;
            font-weight: bold;
        }

        .keyboard-shortcut {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 40px;
        }

        .keyboard-shortcut-title {
            font-size: 18px;
            font-weight: 700;
            color: #1A2B4A;
            margin-bottom: 20px;
        }

        .shortcut-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #E5E7EB;
        }

        .shortcut-key {
            background: #F3F4F6;
            padding: 6px 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            font-weight: 600;
            color: #1F2937;
        }

        .faq-section {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #E5E7EB;
        }

        .faq-title {
            font-size: 24px;
            font-weight: 700;
            color: #1A2B4A;
            margin-bottom: 30px;
        }

        .faq-item {
            margin-bottom: 20px;
        }

        .faq-question {
            font-size: 16px;
            font-weight: 600;
            color: #1A2B4A;
            cursor: pointer;
            padding: 15px;
            background: #F9FAFB;
            border-radius: 8px;
            user-select: none;
            transition: all 0.3s ease;
        }

        .faq-question:hover {
            background: #F3F4F6;
        }

        .faq-question::before {
            content: '+';
            display: inline-block;
            margin-right: 10px;
            color: #4A90E2;
            font-weight: bold;
            width: 20px;
        }

        .faq-question.active::before {
            content: '−';
        }

        .faq-answer {
            display: none;
            padding: 15px 20px;
            background: white;
            border-left: 4px solid #4A90E2;
            color: #555;
            font-size: 14px;
            line-height: 1.6;
        }

        .faq-answer.active {
            display: block;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
                padding: 20px 10px;
            }

            .sidebar-header .subtitle,
            .menu-section-title,
            .menu-item {
                display: none;
            }

            .menu-item svg {
                margin-right: 0;
            }

            .main-content {
                margin-left: 80px;
                padding: 20px;
            }

            .header-title {
                font-size: 20px;
            }

            .help-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <span class="title">MySavings</span>
                <span class="subtitle">MyMineTabungan</span>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">Management</div>
                <a href="dashboard.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>
                <a href="transaksi.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Transactions
                </a>
                <a href="laporan_keuangan.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <polyline points="5 12 12 19 19 12"></polyline>
                    </svg>
                    Reports
                </a>
                <a href="profil.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Profil
                </a>
            </div>

            <div class="menu-divider"></div>

            <div class="menu-section">
                <a href="help.php" class="menu-item active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 16v-4m0-4h.01"></path>
                    </svg>
                    Help
                </a>
                <a href="logout.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </a>
            </div>

            <button class="btn-add-transaction" onclick="window.location.href='transaksi.php'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Transaction
            </button>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="header-title">📚 Bantuan & Panduan</div>
                <div class="header-user">
                    <div class="user-avatar"><?php echo strtoupper(substr($user['nama'], 0, 1)); ?></div>
                    <div class="user-name"><?php echo htmlspecialchars($user['nama']); ?></div>
                </div>
            </div>

            <div class="help-content">
                <!-- Intro Section -->
                <div class="intro-section">
                    <h2>Selamat Datang di MySavings!</h2>
                    <p>MySavings adalah aplikasi manajemen keuangan pribadi yang dirancang untuk membantu Anda melacak pendapatan dan pengeluaran dengan mudah.</p>
                    <p>Halaman ini berisi panduan lengkap tentang cara menggunakan setiap fitur dalam aplikasi. Silakan jelajahi berbagai menu di bawah ini.</p>
                </div>

                <!-- Dashboard Section -->
                <div class="help-section">
                    <div class="help-section-title">Dashboard</div>
                    <div class="help-item">
                        <div class="help-item-title">Apa itu Dashboard?</div>
                        <div class="help-item-description">
                            Dashboard adalah halaman utama yang menampilkan ringkasan keuangan Anda secara keseluruhan. Ini adalah tempat terbaik untuk memulai dan mendapatkan gambaran cepat tentang posisi keuangan Anda.
                        </div>
                        <div class="help-item-features">
                            <strong>Fitur-fitur yang ditampilkan:</strong>
                            <ul class="feature-list">
                                <li><strong>Total Saldo:</strong> Saldo akhir Anda (Total Pemasukan - Total Pengeluaran)</li>
                                <li><strong>Pemasukan Bulan Ini:</strong> Jumlah total uang masuk selama bulan berjalan</li>
                                <li><strong>Pengeluaran Bulan Ini:</strong> Jumlah total uang keluar selama bulan berjalan</li>
                                <li><strong>5 Transaksi Terbaru:</strong> Daftar transaksi terakhir Anda</li>
                            </ul>
                        </div>
                        <div class="tips-section">
                            <div class="tips-section-title">Tips Berguna</div>
                            <ul class="tips-list">
                                <li>Kunjungi dashboard setiap hari untuk memantau keuangan Anda</li>
                                <li>Perhatikan rasio pemasukan dan pengeluaran untuk mengatur budget</li>
                                <li>Gunakan angka pemasukan dan pengeluaran bulanan sebagai referensi perencanaan</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Transaksi Section -->
                <div class="help-section">
                    <div class="help-section-title">Transaksi</div>
                    <div class="help-item">
                        <div class="help-item-title">Cara Menambah Transaksi Baru</div>
                        <div class="help-item-description">
                            Halaman Transaksi adalah tempat Anda mencatat setiap pemasukan atau pengeluaran. Anda dapat menambahkan transaksi baru dengan mengisi formulir yang tersedia.
                        </div>
                        <div class="help-item-features">
                            <strong>Langkah-langkah menambah transaksi:</strong>
                            <ul class="feature-list">
                                <li>Pilih tipe transaksi: <strong>Pemasukan</strong> atau <strong>Pengeluaran</strong></li>
                                <li>Masukkan jumlah nominal (angka saja, tanpa simbol Rp)</li>
                                <li>Pilih kategori yang sesuai (misal: Gaji, Makan, Transport, dll)</li>
                                <li>Tambahkan keterangan/deskripsi transaksi (opsional)</li>
                                <li>Klik tombol <strong>Simpan Transaksi</strong> untuk menyimpan</li>
                            </ul>
                        </div>
                        <div class="tips-section">
                            <div class="tips-section-title">Tips Berguna</div>
                            <ul class="tips-list">
                                <li>Kategori membantu Anda menganalisis pengeluaran per jenis</li>
                                <li>Gunakan keterangan yang deskriptif untuk referensi di masa depan</li>
                                <li>Catat setiap transaksi segera agar tidak lupa</li>
                                <li>Pemasukan otomatis akan ditampilkan di dashboard dengan warna hijau</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Transaksi Section -->
                <div class="help-section">
                    <div class="help-section-title">Riwayat Transaksi</div>
                    <div class="help-item">
                        <div class="help-item-title">Melihat Riwayat Transaksi Lengkap</div>
                        <div class="help-item-description">
                            Halaman Riwayat Transaksi menampilkan semua transaksi yang telah Anda catat dalam format tabel. Anda dapat memfilter berdasarkan periode waktu dan melihat detail setiap transaksi.
                        </div>
                        <div class="help-item-features">
                            <strong>Fitur-fitur utama:</strong>
                            <ul class="feature-list">
                                <li><strong>Filter Periode:</strong> Pilih tanggal mulai dan akhir untuk melihat transaksi dalam rentang waktu tertentu</li>
                                <li><strong>Tombol Filter:</strong> Klik untuk menerapkan filter dan melihat hasil</li>
                                <li><strong>Tabel Transaksi:</strong> Menampilkan tanggal, tipe, kategori, jumlah, dan keterangan</li>
                                <li><strong>Pagination:</strong> Navigasi antar halaman untuk melihat lebih banyak transaksi</li>
                                <li><strong>Penanda Warna:</strong> Pemasukan (hijau), Pengeluaran (merah) untuk kemudahan identifikasi</li>
                            </ul>
                        </div>
                        <div class="tips-section">
                            <div class="tips-section-title">Tips Berguna</div>
                            <ul class="tips-list">
                                <li>Gunakan filter untuk menganalisis pola pengeluaran per bulan</li>
                                <li>Periksa riwayat setiap minggu untuk memastikan tidak ada transaksi yang terlewat</li>
                                <li>Bandingkan periode yang berbeda untuk melihat tren keuangan Anda</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Laporan Keuangan Section -->
                <div class="help-section">
                    <div class="help-section-title">Laporan Keuangan</div>
                    <div class="help-item">
                        <div class="help-item-title">Analisis Keuangan Detail</div>
                        <div class="help-item-description">
                            Halaman Laporan Keuangan menyediakan analisis mendalam tentang pengeluaran dan pemasukan Anda, disertai dengan visualisasi dalam bentuk grafik dan tabel analisis per kategori.
                        </div>
                        <div class="help-item-features">
                            <strong>Fitur-fitur analisis:</strong>
                            <ul class="feature-list">
                                <li><strong>Grafik Perbandingan:</strong> Visualisasi perbandingan pemasukan dan pengeluaran dalam periode tertentu</li>
                                <li><strong>Analisis Per Kategori:</strong> Breakdown pengeluaran untuk setiap kategori</li>
                                <li><strong>Tabel Detail:</strong> Data terperinci dalam format tabel untuk referensi</li>
                                <li><strong>Persentase Pengeluaran:</strong> Melihat kategori mana yang paling banyak menyerap budget</li>
                            </ul>
                        </div>
                        <div class="tips-section">
                            <div class="tips-section-title">Tips Berguna</div>
                            <ul class="tips-list">
                                <li>Gunakan laporan untuk mengidentifikasi area pengeluaran yang dapat dipangkas</li>
                                <li>Bandingkan laporan bulan-ke-bulan untuk melacak kemajuan keuangan</li>
                                <li>Fokus pada kategori dengan persentase tertinggi untuk optimasi budget</li>
                                <li>Laporan ini sangat berguna untuk perencanaan keuangan jangka panjang</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Profil Section -->
                <div class="help-section">
                    <div class="help-section-title">Profil</div>
                    <div class="help-item">
                        <div class="help-item-title">Kelola Informasi Akun Anda</div>
                        <div class="help-item-description">
                            Halaman Profil memungkinkan Anda untuk melihat dan mengelola informasi akun pribadi Anda, termasuk nama dan email.
                        </div>
                        <div class="help-item-features">
                            <strong>Apa yang dapat Anda lakukan:</strong>
                            <ul class="feature-list">
                                <li><strong>Lihat Data Profil:</strong> Informasi nama dan email yang terdaftar</li>
                                <li><strong>Edit Nama:</strong> Perbarui nama profil Anda kapan saja</li>
                                <li><strong>Ubah Password:</strong> Ganti password untuk keamanan akun</li>
                                <li><strong>Verifikasi Email:</strong> Pastikan email Anda valid dan aktif</li>
                            </ul>
                        </div>
                        <div class="tips-section">
                            <div class="tips-section-title">Tips Berguna</div>
                            <ul class="tips-list">
                                <li>Selalu gunakan password yang kuat (kombinasi huruf, angka, dan simbol)</li>
                                <li>Perbarui informasi profil Anda secara berkala</li>
                                <li>Jangan bagikan password Anda kepada siapapun</li>
                                <li>Gunakan email yang masih aktif untuk memastikan keamanan akun</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="faq-section">
                    <h2 class="faq-title">❓ Pertanyaan yang Sering Diajukan (FAQ)</h2>
                    
                    <div class="faq-item">
                        <div class="faq-question">Bagaimana cara memulai menggunakan MySavings?</div>
                        <div class="faq-answer">
                            <strong>Langkah 1:</strong> Login dengan email dan password Anda.<br>
                            <strong>Langkah 2:</strong> Kunjungi halaman Dashboard untuk melihat ringkasan.<br>
                            <strong>Langkah 3:</strong> Mulai tambahkan transaksi dari menu Transaksi.<br>
                            <strong>Langkah 4:</strong> Pantau perkembangan keuangan Anda secara berkala.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">Apa perbedaan antara Pemasukan dan Pengeluaran?</div>
                        <div class="faq-answer">
                            <strong>Pemasukan:</strong> Uang yang masuk ke rekening Anda (gaji, bonus, hadiah, dll). Ditampilkan dengan warna hijau.<br>
                            <strong>Pengeluaran:</strong> Uang yang keluar dari rekening Anda (belanja, bayar tagihan, dll). Ditampilkan dengan warna merah.<br>
                            Saldo akhir dihitung dari Total Pemasukan - Total Pengeluaran.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">Bisakah saya mengedit atau menghapus transaksi yang salah?</div>
                        <div class="faq-answer">
                            Fitur edit dan hapus transaksi sedang dalam pengembangan dan akan segera hadir di versi berikutnya. Untuk saat ini, jika ada transaksi yang salah, Anda dapat membuat transaksi koreksi dengan jumlah berlawanan untuk menyesuaikan saldo.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">Bagaimana cara mengubah password akun saya?</div>
                        <div class="faq-answer">
                            1. Klik menu <strong>Profil</strong> di sidebar<br>
                            2. Scroll ke bagian "Ubah Password"<br>
                            3. Masukkan password lama dan password baru<br>
                            4. Klik tombol <strong>Ubah Password</strong><br>
                            5. Password akan segera diperbarui
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">Apakah data saya aman?</div>
                        <div class="faq-answer">
                            Ya, keamanan data Anda adalah prioritas kami. Kami menggunakan:<br>
                            - Enkripsi password dengan algoritma bcrypt<br>
                            - Validasi input untuk mencegah serangan XSS<br>
                            - Prepared Statements untuk mencegah SQL Injection<br>
                            - Session management yang aman<br>
                            Pastikan Anda juga melakukan hal ini untuk keamanan maksimal: gunakan password yang kuat, jangan bagikan akun Anda, dan logout setelah selesai menggunakan aplikasi.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">Apa itu kategori transaksi dan mengapa penting?</div>
                        <div class="faq-answer">
                            Kategori adalah pengelompokan transaksi berdasarkan jenisnya (misal: Gaji, Makan, Transport, Hiburan, dll). Kategori penting karena:<br>
                            - Membantu Anda mengorganisir transaksi<br>
                            - Memudahkan analisis pengeluaran per jenis<br>
                            - Menjadi dasar laporan keuangan yang detail<br>
                            - Membantu identifikasi area pengeluaran yang besar<br>
                            Gunakan kategori yang konsisten untuk hasil analisis yang lebih baik.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">Bagaimana cara menggunakan fitur filter di Riwayat Transaksi?</div>
                        <div class="faq-answer">
                            1. Klik menu <strong>Riwayat Transaksi</strong><br>
                            2. Cari bagian "Filter Periode"<br>
                            3. Pilih tanggal mulai (From) dengan klik pada field tanggal<br>
                            4. Pilih tanggal akhir (To)<br>
                            5. Klik tombol <strong>Filter</strong><br>
                            6. Tabel akan menampilkan transaksi sesuai periode yang Anda pilih<br>
                            Tip: Filter bulanan sangat berguna untuk analisis per bulan!
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">Bagaimana interpretasi laporan keuangan?</div>
                        <div class="faq-answer">
                            <strong>Grafik Perbandingan:</strong> Menunjukkan tren pemasukan vs pengeluaran. Jika garis pengeluaran terus naik, pertimbangkan untuk mengurangi pengeluaran.<br>
                            <strong>Tabel Analisis:</strong> Menunjukkan breakdown per kategori. Kategori dengan nominal tertinggi adalah prioritas untuk optimasi.<br>
                            <strong>Persentase:</strong> Membantu Anda melihat proporsi pengeluaran. Target ideal adalah pengeluaran ≤ 80% dari pemasukan.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">Apa yang harus saya lakukan jika lupa password?</div>
                        <div class="faq-answer">
                            1. Di halaman login, klik link <strong>"Lupa Password?"</strong><br>
                            2. Masukkan email yang terdaftar<br>
                            3. Kami akan mengirimkan link reset password ke email Anda<br>
                            4. Klik link tersebut dan buat password baru<br>
                            5. Login dengan password baru Anda<br>
                            <strong>Note:</strong> Pastikan email Anda masih aktif dan periksa folder spam jika email tidak masuk.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">Bagaimana tips untuk manajemen keuangan yang baik?</div>
                        <div class="faq-answer">
                            <strong>1. Catat Setiap Transaksi:</strong> Jangan lewatkan transaksi kecil sekalipun<br>
                            <strong>2. Monitor Rutin:</strong> Periksa dashboard dan laporan setiap minggu<br>
                            <strong>3. Analisis Kategori:</strong> Fokus pada kategori dengan pengeluaran tertinggi<br>
                            <strong>4. Buat Budget:</strong> Tentukan target pengeluaran per kategori<br>
                            <strong>5. Evaluasi Berkala:</strong> Bandingkan bulan-ke-bulan untuk melacak progress<br>
                            <strong>6. Aturan 50/30/20:</strong> Alokasikan 50% untuk kebutuhan, 30% keinginan, 20% investasi/tabungan
                        </div>
                    </div>
                </div>

                <!-- Contact Section -->
                <div style="margin-top: 50px; padding: 30px; background: #F9FAFB; border-radius: 8px; border-left: 4px solid #4A90E2; text-align: center;">
                    <h3 style="font-size: 18px; color: #1A2B4A; margin-bottom: 10px;">Perlu Bantuan Lebih Lanjut?</h3>
                    <p style="color: #666; font-size: 14px;">Jika Anda masih memiliki pertanyaan atau menemukan masalah, jangan ragu untuk menghubungi tim support kami atau mengunjungi dashboard untuk bantuan lebih lanjut.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // FAQ Toggle Functionality
        const faqQuestions = document.querySelectorAll('.faq-question');
        
        faqQuestions.forEach(question => {
            question.addEventListener('click', function() {
                const answer = this.nextElementSibling;
                const isActive = this.classList.contains('active');
                
                // Close all answers
                document.querySelectorAll('.faq-question').forEach(q => {
                    q.classList.remove('active');
                });
                document.querySelectorAll('.faq-answer').forEach(a => {
                    a.classList.remove('active');
                });
                
                // Open clicked answer
                if (!isActive) {
                    this.classList.add('active');
                    answer.classList.add('active');
                }
            });
        });

        // Smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
