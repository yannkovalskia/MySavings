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

// Query statistik per kategori (bulan ini)
$query_kategori = "
    SELECT 
        kategori,
        jenis,
        COUNT(*) as jumlah_transaksi,
        SUM(jumlah) as total
    FROM transaksi 
    WHERE user_id = ? 
    AND MONTH(tanggal) = MONTH(CURDATE()) 
    AND YEAR(tanggal) = YEAR(CURDATE())
    GROUP BY kategori, jenis
    ORDER BY total DESC
";
$stmt = $koneksi->prepare($query_kategori);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_kategori = $stmt->get_result();

// Query total per jenis (6 bulan terakhir) untuk chart
$query_chart = "
    SELECT 
        DATE_FORMAT(tanggal, '%Y-%m') as bulan,
        jenis,
        SUM(jumlah) as total
    FROM transaksi 
    WHERE user_id = ? 
    AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(tanggal, '%Y-%m'), jenis
    ORDER BY bulan ASC
";
$stmt = $koneksi->prepare($query_chart);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_chart = $stmt->get_result();

function formatCurrency($value) {
    return 'Rp ' . number_format(abs($value), 0, ',', '.');
}

function getKategoriIcon($kategori) {
    $icons = [
        'Makanan & Minuman' => '🍔',
        'Transportasi' => '🚗',
        'Belanja Bulanan' => '🛒',
        'Hiburan' => '🎬',
        'Tagihan & Cicilan' => '📋',
        'Investasi' => '📈',
        'Kesehatan' => '🏥',
        'Pendidikan' => '📚',
        'Lainnya' => '📌',
        'Gaji Pokok' => '💰',
        'Bonus' => '🎁'
    ];
    return $icons[$kategori] ?? '📌';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - MySavings</title>
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
            margin-bottom: 30px;
        }

        .header-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .header-title p {
            font-size: 14px;
            color: #999;
            margin-top: 5px;
            font-weight: 400;
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
        }

        /* Report Section */
        .report-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }

        .section-subtitle {
            font-size: 13px;
            color: #999;
            margin-bottom: 20px;
        }

        .chart-placeholder {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 14px;
        }

        .kategori-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .kategori-card {
            padding: 20px;
            background-color: #F9F9F9;
            border-radius: 8px;
            border-left: 5px solid #4A90E2;
        }

        .kategori-card.income {
            border-left-color: #00D4AA;
        }

        .kategori-card.expense {
            border-left-color: #FF6B6B;
        }

        .kategori-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .kategori-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: white;
        }

        .kategori-info h3 {
            font-size: 14px;
            color: #333;
            font-weight: 600;
        }

        .kategori-info p {
            font-size: 12px;
            color: #999;
            margin-top: 2px;
        }

        .kategori-amount {
            font-size: 18px;
            font-weight: 700;
            color: #333;
        }

        .kategori-card.income .kategori-amount {
            color: #00D4AA;
        }

        .kategori-card.expense .kategori-amount {
            color: #FF6B6B;
        }

        .kategori-stat {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 180px;
            }

            .main-content {
                margin-left: 180px;
            }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 20px;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .kategori-list {
                grid-template-columns: 1fr;
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
                <a href="laporan_keuangan.php" class="menu-item active">
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
                <a href="#help" class="menu-item">
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

            <a href="transaksi.php" class="btn-add-transaction">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Transaction
            </a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1 class="header-title">Laporan Keuangan</h1>
                    <p>Analisis mendalam tentang pola pengeluaran dan pemasukan Anda.</p>
                </div>
                <div class="header-user">
                    <div class="user-avatar"><?php echo strtoupper(substr($user['nama'], 0, 1)); ?></div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="report-section">
                <h2 class="section-title">Trend 6 Bulan Terakhir</h2>
                <p class="section-subtitle">Perbandingan pemasukan dan pengeluaran dari bulan-bulan sebelumnya</p>
                <div class="chart-placeholder">
                    📊 Chart trend bulanan akan ditampilkan di sini (gunakan Chart.js atau Apex Charts)
                </div>
            </div>

            <!-- Category Breakdown -->
            <div class="report-section">
                <h2 class="section-title">Rincian Per Kategori (Bulan Ini)</h2>
                <p class="section-subtitle">Analisis pengeluaran dan pemasukan berdasarkan kategori</p>
                
                <?php if ($result_kategori->num_rows > 0): ?>
                    <div class="kategori-list">
                        <?php while ($kat = $result_kategori->fetch_assoc()): ?>
                            <div class="kategori-card <?php echo $kat['jenis']; ?>">
                                <div class="kategori-header">
                                    <div class="kategori-icon">
                                        <?php echo getKategoriIcon($kat['kategori']); ?>
                                    </div>
                                    <div class="kategori-info">
                                        <h3><?php echo htmlspecialchars($kat['kategori']); ?></h3>
                                        <p><?php echo ucfirst($kat['jenis']); ?></p>
                                    </div>
                                </div>
                                <div class="kategori-amount"><?php echo formatCurrency($kat['total']); ?></div>
                                <div class="kategori-stat">
                                    <?php echo $kat['jumlah_transaksi']; ?> transaksi
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>Belum ada data transaksi untuk ditampilkan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
