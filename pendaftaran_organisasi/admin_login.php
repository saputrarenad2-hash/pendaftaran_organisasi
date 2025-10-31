<?php
require_once 'config.php';
session_start();

// Jika sudah login, redirect ke admin panel
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_panel.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin) {
                if (password_verify($password, $admin['password'])) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username'] = $admin['username'];
                    header('Location: admin_panel.php');
                    exit;
                } else {
                    $error = 'Username atau password salah!';
                }
            } else {
                $error = 'Username tidak ditemukan!';
            }
        } catch(PDOException $e) {
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-login-body">
<div class="admin-login-container">
    <h1 class="admin-login-title">Login Admin</h1>
    
    <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
    
    <div class="demo-credentials">
        <strong>Demo Credentials:</strong><br>
        Username: <code>admin</code><br>
        Password: <code>admin123</code>
    </div>

    <form class="admin-login-form" method="post">
        <div class="admin-form-group">
            <label class="admin-label" for="username">Username:</label>
            <input class="admin-input" type="text" id="username" name="username" value="admin" required>
        </div>
        
        <div class="admin-form-group">
            <label class="admin-label" for="password">Password:</label>
            <input class="admin-input" type="password" id="password" name="password" value="admin123" required>
        </div>
        
        <button class="admin-button" type="submit" name="login">Login</button>
    </form>
    
    <div class="back-link">
        <a href="index.php">Kembali ke Form Pendaftaran</a>
    </div>
</div>
</body>
</html>