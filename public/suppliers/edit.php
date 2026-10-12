<?php
// public/suppliers/edit.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';
require_role('admin');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    set_flash('error', 'Supplier tidak ditemukan.');
    header('Location: index.php');
    exit;
}

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
        $stmt = $pdo->prepare("UPDATE suppliers SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $id]);
        set_flash('success', 'Data supplier berhasil diperbarui.');
        header('Location: index.php');
        exit;
    }
}
?>

<div class="form-container">
    <h2>Edit Supplier</h2>
    <?php if ($error): ?><div class="alert alert-error" style="margin-top: 1rem;"><?= e($error) ?></div><?php endif; ?>
    <form method="POST" action="edit.php" style="margin-top: 1rem;">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= (int)$id ?>">
        <div class="form-group">
            <label>Nama Supplier</label>
            <input type="text" name="name" value="<?= e($supplier['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Nomor Telepon</label>
            <input type="text" name="phone" value="<?= e($supplier['phone']) ?>">
        </div>
        <div class="form-group">
            <label>Alamat Lengkap</label>
            <textarea name="address" rows="3"><?= e($supplier['address']) ?></textarea>
        </div>
        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
            <a href="index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>