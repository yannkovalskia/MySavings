<?php
require_once '../config/koneksi.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

// Proses Register
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validasi input
    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sesuai!';
    } else {
        // Cek apakah email sudah terdaftar
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user baru
            $query = "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("sss", $nama, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login dengan akun Anda.';
                $_POST = []; // Clear form
            } else {
                $error = 'Terjadi kesalahan saat registrasi. Coba lagi!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MySavings</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4A90E2 0%, #357ABD 50%, #8B5CF6 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .register-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .register-header h1 {
            font-size: 36px;
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .register-header p {
            color: #999;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #999;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-bottom: 2px solid #E0E0E0;
            font-size: 15px;
            color: #333;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-bottom: 2px solid #4A90E2;
        }

        .form-group input::placeholder {
            color: #CCC;
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

        .btn-register {
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
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(74, 144, 226, 0.4);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .register-footer {
            text-align: center;
            margin-top: 30px;
        }

        .register-footer p {
            font-size: 14px;
            color: #666;
        }

        .register-footer a {
            color: #4A90E2;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-footer a:hover {
            color: #357ABD;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h1>Sign Up</h1>
            <p>Buat akun baru untuk memulai</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="nama" placeholder="Nama Lengkap" required value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
            </div>

            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
            </div>

            <button type="submit" name="register" class="btn-register">Create Account</button>
        </form>

        <div class="register-footer">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</body>
</html>
