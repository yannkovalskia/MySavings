<?php
require_once '../config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Query untuk kategori
$kategori_list = [
    'Makanan & Minuman',
    'Transportasi',
    'Belanja Bulanan',
    'Hiburan',
    'Tagihan & Cicilan',
    'Investasi',
    'Kesehatan',
    'Pendidikan',
    'Lainnya'
];

// Proses hapus transaksi
if (isset($_GET['hapus'])) {
    $transaksi_id = (int)$_GET['hapus'];
    
    // Verifikasi bahwa transaksi milik user
    $query_check = "SELECT id FROM transaksi WHERE id = ? AND user_id = ?";
    $stmt_check = $koneksi->prepare($query_check);
    $stmt_check->bind_param("ii", $transaksi_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 1) {
        $query_delete = "DELETE FROM transaksi WHERE id = ? AND user_id = ?";
        $stmt_delete = $koneksi->prepare($query_delete);
        $stmt_delete->bind_param("ii", $transaksi_id, $user_id);
        
        if ($stmt_delete->execute()) {
            $success = 'Transaksi berhasil dihapus!';
            header("Refresh: 1");
        } else {
            $error = 'Gagal menghapus transaksi!';
        }
    } else {
        $error = 'Transaksi tidak ditemukan!';
    }
}

// Proses tambah atau update transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_transaksi'])) {
    $jenis = trim($_POST['jenis']);
    $judul = trim($_POST['judul']);
    $jumlah = str_replace('.', '', str_replace(',', '.', trim($_POST['jumlah'])));
    $kategori = trim($_POST['kategori']);
    $tanggal = trim($_POST['tanggal']);
    $catatan = trim($_POST['catatan']);
    $transaksi_id = isset($_POST['transaksi_id']) ? (int)$_POST['transaksi_id'] : 0;
    
    // Validasi
    if (empty($judul) || empty($jumlah) || empty($kategori) || empty($tanggal)) {
        $error = 'Semua field wajib diisi!';
    } elseif (!is_numeric($jumlah) || $jumlah <= 0) {
        $error = 'Jumlah harus berupa angka positif!';
    } else {
        if ($transaksi_id > 0) {
            // Update transaksi
            // Verifikasi bahwa transaksi milik user
            $query_check = "SELECT id FROM transaksi WHERE id = ? AND user_id = ?";
            $stmt_check = $koneksi->prepare($query_check);
            $stmt_check->bind_param("ii", $transaksi_id, $user_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows === 1) {
                $query = "UPDATE transaksi SET jenis = ?, jumlah = ?, keterangan = ?, kategori = ?, tanggal = ?, catatan = ? WHERE id = ? AND user_id = ?";
                $stmt = $koneksi->prepare($query);
                $stmt->bind_param("sdssssii", $jenis, $jumlah, $judul, $kategori, $tanggal, $catatan, $transaksi_id, $user_id);
                
                if ($stmt->execute()) {
                    $success = 'Transaksi berhasil diperbarui!';
                    $_POST = [];
                    $transaksi_id = 0;
                } else {
                    $error = 'Terjadi kesalahan saat update transaksi!';
                }
            } else {
                $error = 'Transaksi tidak ditemukan atau bukan milik Anda!';
            }
        } else {
            // Insert transaksi baru
            $query = "INSERT INTO transaksi (user_id, jenis, jumlah, keterangan, kategori, tanggal, catatan) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("isdssss", $user_id, $jenis, $jumlah, $judul, $kategori, $tanggal, $catatan);
            
            if ($stmt->execute()) {
                $success = 'Transaksi berhasil dicatat!';
                $_POST = [];
            } else {
                $error = 'Terjadi kesalahan saat menyimpan transaksi!';
            }
        }
    }
}

// Jika ada parameter edit, ambil data transaksi
$edit_transaksi = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $query = "SELECT * FROM transaksi WHERE id = ? AND user_id = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $edit_transaksi = $result->fetch_assoc();
    }
}

// Query total saldo
$query_saldo = "
    SELECT COALESCE(SUM(CASE WHEN jenis = 'pemasukan' THEN jumlah ELSE -jumlah END), 0) as total_saldo
    FROM transaksi WHERE user_id = ?
";
$stmt = $koneksi->prepare($query_saldo);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_saldo = $stmt->get_result();
$saldo_data = $result_saldo->fetch_assoc();
$total_saldo = $saldo_data['total_saldo'];

// Query transaksi terakhir
$query_terakhir = "
    SELECT * FROM transaksi WHERE user_id = ? ORDER BY tanggal DESC, id DESC LIMIT 3
";
$stmt = $koneksi->prepare($query_terakhir);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_terakhir = $stmt->get_result();

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
    <title>Catat Transaksi - MySavings</title>
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

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        /* Form Section */
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .tabs {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid #E0E0E0;
        }

        .tab-btn {
            padding: 12px 20px;
            background: none;
            border: none;
            color: #999;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }

        .tab-btn:hover {
            color: #666;
        }

        .tab-btn.active {
            color: #333;
            border-bottom-color: #FF6B6B;
        }

        .tab-btn.active.income {
            border-bottom-color: #00D4AA;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4A90E2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #FFE5E5;
            color: #D32F2F;
            border-left: 4px solid #D32F2F;
        }

        .alert-success {
            background-color: #E5F5E5;
            color: #388E3C;
            border-left: 4px solid #388E3C;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(90deg, #4A90E2 0%, #8B5CF6 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(74, 144, 226, 0.4);
        }

        /* Right Sidebar */
        .right-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .saldo-card {
            background: linear-gradient(135deg, #1A2B4A 0%, #0F1620 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .saldo-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .saldo-card-title {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 600;
            text-transform: uppercase;
        }

        .saldo-card-value {
            font-size: 26px;
            font-weight: 700;
        }

        .saldo-card-desc {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 10px;
            line-height: 1.5;
        }

        .recent-transactions {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .recent-title {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }

        .transaction-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background-color: #F9F9F9;
            border-radius: 8px;
            margin-bottom: 12px;
        }

        .transaction-card:last-child {
            margin-bottom: 0;
        }

        .transaction-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
            background-color: rgba(74, 144, 226, 0.1);
        }

        .transaction-card-info {
            flex: 1;
        }

        .transaction-card-name {
            font-weight: 600;
            font-size: 13px;
            color: #333;
        }

        .transaction-card-category {
            font-size: 11px;
            color: #999;
        }

        .transaction-card-amount {
            font-weight: 700;
            font-size: 13px;
        }

        .transaction-card-amount.income {
            color: #00D4AA;
        }

        .transaction-card-amount.expense {
            color: #FF6B6B;
        }

        .empty-state {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 180px;
            }

            .main-content {
                margin-left: 180px;
            }

            .content-grid {
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

            .content-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
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
                <a href="transaksi.php" class="menu-item active">
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
                <div>
                    <h1 class="header-title">Catat Transaksi</h1>
                    <p>Pantau setiap aliran keuangan Anda dengan presisi.</p>
                </div>
                <div class="header-user">
                    <div class="user-avatar">B</div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Form Section -->
                <div class="form-section">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <div class="tabs">
                        <button class="tab-btn <?php echo (!$edit_transaksi || $edit_transaksi['jenis'] === 'pengeluaran') ? 'active expense' : 'expense'; ?>" onclick="setJenis('pengeluaran')">Pengeluaran</button>
                        <button class="tab-btn <?php echo ($edit_transaksi && $edit_transaksi['jenis'] === 'pemasukan') ? 'active income' : 'income'; ?>" onclick="setJenis('pemasukan')">Pemasukan</button>
                    </div>

                    <form method="POST" action="">
                        <?php if ($edit_transaksi): ?>
                            <input type="hidden" name="transaksi_id" value="<?php echo $edit_transaksi['id']; ?>">
                            <div style="padding: 12px; background-color: #FFF9E5; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #FF9800;">
                                <p style="color: #E65100; font-size: 14px; font-weight: 600; margin: 0;">Mode Edit Transaksi</p>
                                <a href="transaksi.php" style="color: #FF9800; font-size: 12px; text-decoration: none;">← Kembali ke form baru</a>
                            </div>
                        <?php endif; ?>
                        
                        <input type="hidden" name="jenis" id="jenis" value="<?php echo $edit_transaksi ? $edit_transaksi['jenis'] : 'pengeluaran'; ?>">

                        <div class="form-group">
                            <label>Judul Transaksi</label>
                            <input type="text" name="judul" placeholder="Contoh: Belanja Bulanan" required value="<?php echo $edit_transaksi ? htmlspecialchars($edit_transaksi['keterangan']) : (isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>Jumlah (Nominal)</label>
                            <input type="text" name="jumlah" id="jumlah" placeholder="Rp 0" required value="<?php echo $edit_transaksi ? number_format($edit_transaksi['jumlah'], 0, ',', '.') : (isset($_POST['jumlah']) ? htmlspecialchars($_POST['jumlah']) : ''); ?>" inputmode="numeric">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($kategori_list as $kat): ?>
                                        <?php $selected = ($edit_transaksi && $edit_transaksi['kategori'] === $kat) || (isset($_POST['kategori']) && $_POST['kategori'] === $kat); ?>
                                        <option value="<?php echo $kat; ?>" <?php echo $selected ? 'selected' : ''; ?>>
                                            <?php echo $kat; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="date" name="tanggal" required value="<?php echo $edit_transaksi ? $edit_transaksi['tanggal'] : (isset($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d')); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Catatan (Opsional)</label>
                            <textarea name="catatan" placeholder="Tambahkan deskripsi detail..."><?php echo $edit_transaksi ? htmlspecialchars($edit_transaksi['catatan'] ?? '') : (isset($_POST['catatan']) ? htmlspecialchars($_POST['catatan']) : ''); ?></textarea>
                        </div>

                        <button type="submit" name="simpan_transaksi" class="btn-submit"><?php echo $edit_transaksi ? 'Update Transaksi' : 'Simpan Transaksi'; ?></button>
                    </form>
                </div>

                <!-- Right Sidebar -->
                <div class="right-sidebar">
                    <!-- Saldo Card -->
                    <div class="saldo-card">
                        <div class="saldo-card-header">
                            <span>💰</span>
                            <div class="saldo-card-title">Saldo Estimasi</div>
                        </div>
                        <div class="saldo-card-value"><?php echo formatCurrency($total_saldo); ?></div>
                        <div class="saldo-card-desc">Selesaikan transaksi ini untuk memperbaharui laporan bulanan Anda.</div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="recent-transactions">
                        <div class="recent-title">Transaksi Terakhir</div>
                        <?php if ($result_terakhir->num_rows > 0): ?>
                            <?php while ($trans = $result_terakhir->fetch_assoc()): ?>
                                <div class="transaction-card">
                                    <div class="transaction-card-icon">
                                        <?php echo getKategoriIcon($trans['kategori']); ?>
                                    </div>
                                    <div class="transaction-card-info">
                                        <div class="transaction-card-name"><?php echo htmlspecialchars($trans['keterangan']); ?></div>
                                        <div class="transaction-card-category"><?php echo htmlspecialchars($trans['kategori']); ?></div>
                                    </div>
                                    <div class="transaction-card-amount <?php echo $trans['jenis']; ?>">
                                        <?php echo ($trans['jenis'] === 'pemasukan' ? '+' : '-') . ' ' . formatCurrency($trans['jumlah']); ?>
                                    </div>
                                    <div style="display: flex; gap: 5px; margin-left: 10px;">
                                        <a href="transaksi.php?edit=<?php echo $trans['id']; ?>" style="padding: 4px 8px; background: #E3F2FD; color: #1976D2; border-radius: 4px; text-decoration: none; font-size: 11px; font-weight: 600; cursor: pointer; border: none;">Edit</a>
                                        <button onclick="if(confirm('Yakin hapus transaksi ini?')) window.location.href='transaksi.php?hapus=<?php echo $trans['id']; ?>';" style="padding: 4px 8px; background: #FFEBEE; color: #C62828; border-radius: 4px; text-decoration: none; font-size: 11px; font-weight: 600; cursor: pointer; border: none;">Hapus</button>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">Belum ada transaksi</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setJenis(jenis) {
            document.getElementById('jenis').value = jenis;
            const tabs = document.querySelectorAll('.tab-btn');
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');
        }

        // Format currency input
        document.getElementById('jumlah').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value) {
                value = new Intl.NumberFormat('id-ID').format(value);
                this.value = value;
            }
        });
    </script>
</body>
</html>
