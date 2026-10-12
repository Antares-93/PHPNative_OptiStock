<?php
// public/login.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/validation.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kalau akun sudah login, langsung lempar ke dashboard
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'role'     => $user['role']
        ];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}

require_once __DIR__ . '/../views/header.php';
?>

<div class="form-container" style="max-width: 420px; margin-top: 3.5rem;">
    <h2>Masuk Sistem</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" autocomplete="off">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required placeholder="Masukkan username" autocomplete="off">
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Masukkan password" autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
    </form>

    <div style="text-align: center; margin-top: 1.25rem;">
        <a href="index.php" style="font-size: 0.85rem; color: var(--text-muted); text-decoration: none;">
            ← Kembali ke Katalog Publik
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>