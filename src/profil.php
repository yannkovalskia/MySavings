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

// Query data user
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profil'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    
    // Validasi
    if (empty($nama) || empty($email)) {
        $error = 'Nama dan email harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek email apakah sudah dipakai user lain
        $query_check = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt_check = $koneksi->prepare($query_check);
        $stmt_check->bind_param("si", $email, $user_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $error = 'Email sudah digunakan oleh user lain!';
        } else {
            // Update profil
            $query_update = "UPDATE users SET nama = ?, email = ? WHERE id = ?";
            $stmt_update = $koneksi->prepare($query_update);
            $stmt_update->bind_param("ssi", $nama, $email, $user_id);
            
            if ($stmt_update->execute()) {
                $_SESSION['email'] = $email;
                $_SESSION['nama'] = $nama;
                $user['nama'] = $nama;
                $user['email'] = $email;
                $success = 'Profil berhasil diperbarui!';
            } else {
                $error = 'Terjadi kesalahan saat update profil!';
            }
        }
    }
}

function getInitials($nama) {
    $parts = explode(' ', trim($nama));
    $initials = '';
    foreach ($parts as $part) {
        $initials .= strtoupper($part[0]);
    }
    return strlen($initials) > 2 ? substr($initials, 0, 2) : $initials;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - MySavings</title>
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

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .user-profile-mini {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .avatar-mini {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #4A90E2, #8B5CF6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }

        .user-info-mini {
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

        /* Settings Card */
        .settings-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .settings-card h3 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #666;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            font-size: 14px;
            color: #333;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4A90E2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .form-group input::placeholder {
            color: #CCC;
        }

        /* Profile Section */
        .profile-header {
            display: flex;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 30px;
        }

        .avatar-large {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #4A90E2, #8B5CF6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 40px;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
        }

        .profile-info {
            flex: 1;
        }

        .profile-info h2 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #333;
        }

        .profile-info p {
            color: #999;
            font-size: 14px;
        }

        .alert {
            padding: 15px 20px;
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

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(90deg, #4A90E2 0%, #8B5CF6 100%);
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 180px;
                padding: 20px 15px;
            }

            .main-content {
                margin-left: 180px;
                padding: 20px;
            }

            .profile-header {
                flex-direction: column;
                align-items: flex-start;
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
                <a href="dashboard.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard
                </a>
                <a href="transaksi.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Transaksi
                </a>
                <a href="riwayat_transaksi.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Riwayat
                </a>
                <a href="laporan_keuangan.php" class="menu-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="2" x2="12" y2="22"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    Laporan
                </a>
                <a href="profil.php" class="menu-item active">
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
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Profil</h1>
                <div class="user-profile-mini">
                    <div class="avatar-mini"><?php echo getInitials($user['nama']); ?></div>
                    <div class="user-info-mini">
                        <div class="user-name"><?php echo htmlspecialchars($user['nama']); ?></div>
                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Edit Profil Card -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="settings-card">
                <div class="profile-header">
                    <div class="avatar-large"><?php echo getInitials($user['nama']); ?></div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['nama']); ?></h2>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="button-group">
                        <button type="submit" name="update_profil" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
