<?php
// public/users/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';
require_role('admin');

$errors = [];
$username = $role = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Token CSRF tidak valid.');
    }

    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'staff';

    if ($username === '' || mb_strlen($username) < 3) {
        $errors['username'] = 'Username minimal 3 karakter.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $errors['username'] = 'Username sudah dipakai.';
        }
    }

    if (mb_strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter.';
    }

    if (!in_array($role, ['admin', 'staff'])) {
        $errors['role'] = 'Role tidak valid.';
    }

    if (empty($errors)) {
        // Password hashing dengan algoritma default PHP (Slide 23)
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, $role]);

        set_flash('success', "Pengguna $username ($role) berhasil dibuat.");
        header('Location: index.php');
        exit;
    }
}
?>

<div class="form-container">
    <h2>+ Tambah Pengguna Baru</h2>
    <form method="POST" action="create.php" style="margin-top: 1rem;">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= e($username) ?>" required placeholder="Contoh: andi">
            <?php if (isset($errors['username'])): ?><div style="color:var(--danger); font-size:0.8rem;"><?= e($errors['username']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Minimal 6 karakter">
            <?php if (isset($errors['password'])): ?><div style="color:var(--danger); font-size:0.8rem;"><?= e($errors['password']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Peran (Role)</label>
            <select name="role">
                <option value="staff" <?= $role === 'staff' ? 'selected' : '' ?>>Staff (Hanya lihat stok & transaksi)</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin (Akses penuh master data)</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
            <a href="index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan User</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>