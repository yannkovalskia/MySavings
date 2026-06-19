<?php
require_once '../config/koneksi.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

// Proses Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Validasi input
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        // Query cari user berdasarkan email
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['nama'] = $user['nama'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Email tidak ditemukan!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MySavings</title>
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

        .login-container {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-header h1 {
            font-size: 36px;
            color: #333;
            font-weight: 700;
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

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(74, 144, 226, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
        }

        .login-footer p {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .login-footer a {
            color: #4A90E2;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: #357ABD;
        }

        .divider {
            color: #CCC;
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Login</h1>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" name="login" class="btn-login">Login</button>
        </form>

        <div class="login-footer">
            <p><a href="forgot_password.php">Forgot Password?</a></p>
            <p>Don't have an account? <a href="register.php">Sign up</a></p>
        </div>
    </div>
</body>
</html>
