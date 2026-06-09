<?php
require_once '../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Filter period
$period = $_GET['period'] ?? 'bulan_ini';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;

// Tentukan kondisi tanggal berdasarkan period
$date_condition = "";
switch ($period) {
    case 'hari_ini':
        $date_condition = "AND DATE(tanggal) = CURDATE()";
        break;
    case 'minggu_ini':
        $date_condition = "AND YEARWEEK(tanggal) = YEARWEEK(CURDATE())";
        break;
    case 'bulan_ini':
    default:
        $date_condition = "AND MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())";
        break;
}

// Query total pemasukan
$query_pemasukan = "
    SELECT COALESCE(SUM(jumlah), 0) as total 
    FROM transaksi 
    WHERE user_id = ? AND jenis = 'pemasukan' $date_condition
";
$stmt = $koneksi->prepare($query_pemasukan);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pemasukan = $result->fetch_assoc()['total'];

// Query total pengeluaran
$query_pengeluaran = "
    SELECT COALESCE(SUM(jumlah), 0) as total 
    FROM transaksi 
    WHERE user_id = ? AND jenis = 'pengeluaran' $date_condition
";
$stmt = $koneksi->prepare($query_pengeluaran);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pengeluaran = $result->fetch_assoc()['total'];

$saldo_bersih = $pemasukan - $pengeluaran;

// Query total records
$query_count = "
    SELECT COUNT(*) as total 
    FROM transaksi 
    WHERE user_id = ? $date_condition
";
$stmt = $koneksi->prepare($query_count);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_records = $result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $per_page);

// Query transaksi dengan pagination
$offset = ($page - 1) * $per_page;
$query_transaksi = "
    SELECT * FROM transaksi 
    WHERE user_id = ? $date_condition
    ORDER BY tanggal DESC, id DESC
    LIMIT ? OFFSET ?
";
$stmt = $koneksi->prepare($query_transaksi);
$stmt->bind_param("iii", $user_id, $per_page, $offset);
$stmt->execute();
$result_transaksi = $stmt->get_result();

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

function getPeriodLabel($period) {
    $labels = [
        'hari_ini' => 'Hari Ini',
        'minggu_ini' => 'Minggu Ini',
        'bulan_ini' => 'Bulan Ini'
    ];
    return $labels[$period] ?? 'Bulan Ini';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - MySavings</title>
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

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .filter-btn {
            padding: 10px 20px;
            background: white;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            color: #666;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .filter-btn:hover {
            border-color: #4A90E2;
            color: #4A90E2;
        }

        .filter-btn.active {
            background: white;
            border-color: #4A90E2;
            color: #4A90E2;
            box-shadow: 0 2px 8px rgba(74, 144, 226, 0.2);
        }

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
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

        .summary-card-title {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .summary-card-value {
            font-size: 26px;
            font-weight: 700;
            color: #333;
        }

        .summary-card.income .summary-card-value {
            color: #00D4AA;
        }

        .summary-card.expense .summary-card-value {
            color: #FF6B6B;
        }

        /* Table Section */
        .table-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .table-title {
            font-size: 18px;
            font-weight: 700;
            color: #333;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 12px;
            background: white;
            border: 1px solid #E0E0E0;
            border-radius: 6px;
            color: #666;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-btn:hover {
            border-color: #4A90E2;
            color: #4A90E2;
        }

        /* Table */
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
        }

        .transaction-table thead {
            border-bottom: 2px solid #E0E0E0;
        }

        .transaction-table th {
            padding: 15px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .transaction-table td {
            padding: 15px;
            border-bottom: 1px solid #E0E0E0;
        }

        .transaction-table tbody tr:hover {
            background-color: #F9F9F9;
        }

        .table-date {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        .table-category {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            background-color: #F0F0F0;
        }

        .category-name {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        .table-description {
            font-size: 13px;
            color: #666;
        }

        .table-amount {
            font-size: 14px;
            font-weight: 700;
            text-align: right;
        }

        .table-amount.income {
            color: #00D4AA;
        }

        .table-amount.expense {
            color: #FF6B6B;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E0E0E0;
        }

        .pagination-btn {
            padding: 8px 12px;
            background: white;
            border: 1px solid #E0E0E0;
            border-radius: 6px;
            color: #666;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .pagination-btn:hover {
            border-color: #4A90E2;
            color: #4A90E2;
        }

        .pagination-btn.active {
            background: #4A90E2;
            border-color: #4A90E2;
            color: white;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-info {
            color: #999;
            font-size: 13px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 180px;
            }

            .main-content {
                margin-left: 180px;
            }

            .summary-cards {
                grid-template-columns: 1fr;
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

            .transaction-table {
                font-size: 12px;
            }

            .transaction-table th,
            .transaction-table td {
                padding: 10px;
            }

            .table-actions {
                flex-direction: column;
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
                    <h1 class="header-title">Riwayat Transaksi</h1>
                    <p>Lacak dan kelola arus kas keuangan Anda secara detail.</p>
                </div>
                <div class="header-user">
                    <div class="user-avatar">B</div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="?period=hari_ini" class="filter-btn <?php echo $period === 'hari_ini' ? 'active' : ''; ?>">Hari Ini</a>
                <a href="?period=minggu_ini" class="filter-btn <?php echo $period === 'minggu_ini' ? 'active' : ''; ?>">Minggu Ini</a>
                <a href="?period=bulan_ini" class="filter-btn <?php echo $period === 'bulan_ini' ? 'active' : ''; ?>">Bulan Ini</a>
            </div>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card income">
                    <div class="summary-card-title">Total Pemasukan</div>
                    <div class="summary-card-value"><?php echo formatCurrency($pemasukan); ?></div>
                </div>

                <div class="summary-card expense">
                    <div class="summary-card-title">Total Pengeluaran</div>
                    <div class="summary-card-value"><?php echo formatCurrency($pengeluaran); ?></div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-title">Saldo Bersih</div>
                    <div class="summary-card-value"><?php echo formatCurrency($saldo_bersih); ?></div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="table-section">
                <div class="table-header">
                    <h2 class="table-title">Daftar Transaksi Terbaru</h2>
                    <div class="table-actions">
                        <button class="action-btn">📊 Filter</button>
                        <button class="action-btn">⬇️ Download</button>
                    </div>
                </div>

                <?php if ($result_transaksi->num_rows > 0): ?>
                    <table class="transaction-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th style="text-align: right;">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($trans = $result_transaksi->fetch_assoc()): ?>
                                <tr>
                                    <td class="table-date"><?php echo date('d M Y', strtotime($trans['tanggal'])); ?></td>
                                    <td>
                                        <div class="table-category">
                                            <div class="category-icon">
                                                <?php echo getKategoriIcon($trans['kategori']); ?>
                                            </div>
                                            <div class="category-name"><?php echo htmlspecialchars($trans['kategori']); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="table-description"><?php echo htmlspecialchars($trans['keterangan']); ?></div>
                                    </td>
                                    <td class="table-amount <?php echo $trans['jenis']; ?>">
                                        <?php echo ($trans['jenis'] === 'pemasukan' ? '+' : '-') . ' ' . formatCurrency($trans['jumlah']); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?period=<?php echo $period; ?>&page=<?php echo $page - 1; ?>" class="pagination-btn">← Sebelumnya</a>
                            <?php else: ?>
                                <button class="pagination-btn" disabled>← Sebelumnya</button>
                            <?php endif; ?>

                            <span class="pagination-info">
                                Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
                                (<?php echo $total_records; ?> transaksi)
                            </span>

                            <?php if ($page < $total_pages): ?>
                                <a href="?period=<?php echo $period; ?>&page=<?php echo $page + 1; ?>" class="pagination-btn">Selanjutnya →</a>
                            <?php else: ?>
                                <button class="pagination-btn" disabled>Selanjutnya →</button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <p>Belum ada transaksi di periode ini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
