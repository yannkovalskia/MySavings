<?php
require_once '../config/koneksi.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$message = '';
$is_sent = false;

// Proses forgot password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = 'Email harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Format email tidak valid!';
    } else {
        // Cek apakah email terdaftar
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Simulasi: dalam production, kirim email dengan reset link
            // Untuk sekarang, arahkan ke login dengan pesan
            $is_sent = true;
            $message = 'Link reset password telah dikirim ke email Anda. Silakan cek inbox Anda.';
        } else {
            $message = 'Email tidak ditemukan dalam sistem.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - MySavings</title>
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

        .reset-container {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .reset-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .reset-header h1 {
            font-size: 32px;
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .reset-header p {
            color: #999;
            font-size: 14px;
            line-height: 1.6;
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

        .message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .message-success {
            background-color: #E5F5E5;
            color: #388E3C;
            border-left: 4px solid #388E3C;
        }

        .message-error {
            background-color: #FFE5E5;
            color: #D32F2F;
            border-left: 4px solid #D32F2F;
        }

        .btn-reset {
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

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(74, 144, 226, 0.4);
        }

        .btn-reset:active {
            transform: translateY(0);
        }

        .reset-footer {
            text-align: center;
            margin-top: 30px;
        }

        .reset-footer p {
            font-size: 14px;
            color: #666;
        }

        .reset-footer a {
            color: #4A90E2;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .reset-footer a:hover {
            color: #357ABD;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h1>Lupa Password?</h1>
            <p>Masukkan email Anda dan kami akan mengirimkan link untuk reset password.</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $is_sent ? 'message-success' : 'message-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!$is_sent): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Anda" required>
                </div>

                <button type="submit" name="reset" class="btn-reset">Kirim Link Reset</button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 20px 0;">
                <p style="color: #666; margin-bottom: 20px;">Silakan kembali ke halaman login dan gunakan link yang telah kami kirimkan.</p>
            </div>
        <?php endif; ?>

        <div class="reset-footer">
            <p><a href="login.php">← Kembali ke Login</a></p>
        </div>
    </div>
</body>
</html>
