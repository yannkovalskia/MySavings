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

// Query total saldo (total pemasukan - total pengeluaran)
$query_saldo = "
    SELECT 
        COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE -jumlah END), 0) as total_saldo
    FROM transaksi 
    WHERE user_id = ?
";
$stmt = $koneksi->prepare($query_saldo);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_saldo = $stmt->get_result();
$saldo_data = $result_saldo->fetch_assoc();
$total_saldo = $saldo_data['total_saldo'];

// Query pemasukan bulan ini
$query_pemasukan = "
    SELECT COALESCE(SUM(jumlah), 0) as total_pemasukan 
    FROM transaksi 
    WHERE user_id = ? 
    AND jenis = 'pemasukan' 
    AND MONTH(tanggal) = MONTH(CURDATE()) 
    AND YEAR(tanggal) = YEAR(CURDATE())
";
$stmt = $koneksi->prepare($query_pemasukan);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_pemasukan = $stmt->get_result();
$pemasukan_data = $result_pemasukan->fetch_assoc();
$total_pemasukan = $pemasukan_data['total_pemasukan'];

// Query pengeluaran bulan ini
$query_pengeluaran = "
    SELECT COALESCE(SUM(jumlah), 0) as total_pengeluaran 
    FROM transaksi 
    WHERE user_id = ? 
    AND jenis = 'pengeluaran' 
    AND MONTH(tanggal) = MONTH(CURDATE()) 
    AND YEAR(tanggal) = YEAR(CURDATE())
";
$stmt = $koneksi->prepare($query_pengeluaran);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_pengeluaran = $stmt->get_result();
$pengeluaran_data = $result_pengeluaran->fetch_assoc();
$total_pengeluaran = $pengeluaran_data['total_pengeluaran'];

// Query transaksi terbaru (5 transaksi terakhir)
$query_transaksi = "
    SELECT * FROM transaksi 
    WHERE user_id = ? 
    ORDER BY tanggal DESC, id DESC 
    LIMIT 5
";
$stmt = $koneksi->prepare($query_transaksi);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_transaksi = $stmt->get_result();

// Format currency
function formatCurrency($value) {
    return 'Rp ' . number_format(abs($value), 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MySavings</title>
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
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }

        .user-email {
            font-size: 12px;
            color: #999;
        }

        /* Summary Cards */
        .summary-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .summary-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #4A90E2;
        }

        .summary-card.income {
            border-left-color: #00D4AA;
        }

        .summary-card.expense {
            border-left-color: #FF6B6B;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 14px;
            color: #999;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-badge {
            display: flex;
            align-items: center;
            font-size: 12px;
            font-weight: 600;
            gap: 5px;
            padding: 4px 8px;
            background-color: #F0F0F0;
            border-radius: 4px;
        }

        .card-badge.positive {
            color: #00D4AA;
            background-color: #E5FBF7;
        }

        .card-badge.negative {
            color: #FF6B6B;
            background-color: #FFE5E5;
        }

        .card-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .card-subtitle {
            font-size: 12px;
            color: #999;
        }

        /* Chart Section */
        .chart-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .section-subtitle {
            font-size: 13px;
            color: #999;
            margin-bottom: 20px;
        }

        .chart-placeholder {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 14px;
        }

        /* Transactions Section */
        .transactions-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .transaction-header .section-title {
            margin-bottom: 0;
        }

        .link-view-all {
            color: #4A90E2;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .link-view-all:hover {
            text-decoration: underline;
        }

        .transaction-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .transaction-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background-color: #F9F9F9;
            border-radius: 8px;
            transition: background-color 0.2s ease;
        }

        .transaction-item:hover {
            background-color: #F0F0F0;
        }

        .transaction-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .transaction-icon.income {
            background-color: #E5FBF7;
            color: #00D4AA;
        }

        .transaction-icon.expense {
            background-color: #FFE5E5;
            color: #FF6B6B;
        }

        .transaction-details {
            flex: 1;
        }

        .transaction-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .transaction-date {
            font-size: 12px;
            color: #999;
        }

        .transaction-amount {
            text-align: right;
        }

        .transaction-amount-value {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .transaction-amount.income .transaction-amount-value {
            color: #00D4AA;
        }

        .transaction-amount.expense .transaction-amount-value {
            color: #FF6B6B;
        }

        .transaction-status {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 600;
        }

        .transaction-status.success {
            background-color: #E5FBF7;
            color: #00D4AA;
        }

        .transaction-status.pending {
            background-color: #FFF3E0;
            color: #F57C00;
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

            .summary-section {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
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
                <a href="dashboard.php" class="menu-item active">
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
                <a href="#settings" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m3.08 3.08l4.24 4.24M1 12h6m6 0h6m-1.78-7.22l-4.24 4.24m-3.08 3.08l-4.24 4.24"></path>
                    </svg>
                    Settings
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

            <button class="btn-add-transaction">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add Transaction
            </button>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1 class="header-title">Ringkasan Keuangan</h1>
                <div class="header-user">
                    <div class="user-avatar"><?php echo strtoupper(substr($user['nama'], 0, 1)); ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($user['nama']); ?></div>
                        <div class="user-email">Premium Account</div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="summary-section">
                <div class="summary-card">
                    <div class="card-header">
                        <div class="card-title">Total Saldo</div>
                        <div class="card-badge">
                            <span>📊</span>
                            <?php 
                            $change = $total_saldo > 0 ? '+' : '';
                            $percentage = $total_saldo > 1000000 ? '+2.4%' : '-1.2%';
                            ?>
                            <?php echo $percentage; ?>
                        </div>
                    </div>
                    <div class="card-value"><?php echo formatCurrency($total_saldo); ?></div>
                    <div class="card-subtitle">Estimasi akhir bulan: <?php echo formatCurrency($total_saldo * 1.1); ?></div>
                </div>

                <div class="summary-card income">
                    <div class="card-header">
                        <div class="card-title">Pemasukan Bulan Ini</div>
                        <div class="card-badge positive">
                            <span>✓</span>
                            +12%
                        </div>
                    </div>
                    <div class="card-value"><?php echo formatCurrency($total_pemasukan); ?></div>
                    <div class="card-subtitle">Terbesar: Gaji Bulanan</div>
                </div>

                <div class="summary-card expense">
                    <div class="card-header">
                        <div class="card-title">Pengeluaran Bulan Ini</div>
                        <div class="card-badge negative">
                            <span>↓</span>
                            -5%
                        </div>
                    </div>
                    <div class="card-value"><?php echo formatCurrency($total_pengeluaran); ?></div>
                    <div class="card-subtitle">Sisa anggaran: Rp 5.1M</div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="chart-section">
                <h2 class="section-title">Arus Kas Real-time</h2>
                <p class="section-subtitle">Statistik pengeluaran dana 30 hari terakhir</p>
                <div class="chart-placeholder">
                    📈 Chart akan ditampilkan di sini (gunakan Chart.js, Apex Charts, atau library serupa)
                </div>
            </div>

            <!-- Transactions Section -->
            <div class="transactions-section">
                <div class="transaction-header">
                    <h2 class="section-title">Transaksi Terakhir</h2>
                    <a href="riwayat_transaksi.php" class="link-view-all">Lihat Semua →</a>
                </div>

                <div class="transaction-list">
                    <?php if ($result_transaksi->num_rows > 0): ?>
                        <?php while ($transaksi = $result_transaksi->fetch_assoc()): ?>
                            <div class="transaction-item">
                                <div class="transaction-icon <?php echo $transaksi['jenis']; ?>">
                                    <?php echo $transaksi['jenis'] === 'pemasukan' ? '📥' : '📤'; ?>
                                </div>
                                <div class="transaction-details">
                                    <div class="transaction-name"><?php echo htmlspecialchars($transaksi['keterangan']); ?></div>
                                    <div class="transaction-date"><?php echo date('d M Y H:i', strtotime($transaksi['tanggal'])); ?></div>
                                </div>
                                <div class="transaction-amount <?php echo $transaksi['jenis']; ?>">
                                    <div class="transaction-amount-value">
                                        <?php echo formatCurrency($transaksi['jumlah']); ?>
                                    </div>
                                    <span class="transaction-status success">Selesai</span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Belum ada transaksi. Mulai dengan menambahkan transaksi pertama Anda!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
