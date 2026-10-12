<?php
// public/suppliers/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';
require_role('admin');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Token CSRF tidak valid.');
    }

    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '' || mb_strlen($name) < 2) {
        $error = 'Nama supplier minimal 2 karakter.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, phone, address) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $address]);
        set_flash('success', 'Supplier berhasil ditambahkan.');
        header('Location: index.php');
        exit;
    }
}
?>

<div class="form-container">
    <h2>+ Tambah Supplier</h2>
    <?php if ($error): ?><div class="alert alert-error" style="margin-top: 1rem;"><?= e($error) ?></div><?php endif; ?>
    <form method="POST" action="create.php" style="margin-top: 1rem;">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group">
            <label>Nama Supplier / Perusahaan</label>
            <input type="text" name="name" required placeholder="PT Sumber Berkah">
        </div>
        <div class="form-group">
            <label>Nomor Telepon</label>
            <input type="text" name="phone" placeholder="08xxxxxxxxxx">
        </div>
        <div class="form-group">
            <label>Alamat Lengkap</label>
            <textarea name="address" rows="3" placeholder="Alamat supplier"></textarea>
        </div>
        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
            <a href="index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>