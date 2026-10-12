<?php
// public/categories/edit.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';
require_role('admin');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    set_flash('error', 'Kategori tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Token CSRF tidak valid.');
    }

    $name = trim($_POST['name'] ?? '');
    if ($name === '' || mb_strlen($name) < 2) {
        $error = 'Nama kategori minimal 2 karakter.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ? AND id != ?");
        $stmt->execute([$name, $id]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Nama kategori sudah digunakan.';
        } else {
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            set_flash('success', 'Kategori berhasil diubah.');
            header('Location: index.php');
            exit;
        }
    }
}
?>

<div class="form-container">
    <h2>Edit Kategori</h2>
    <?php if ($error): ?><div class="alert alert-error" style="margin-top: 1rem;"><?= e($error) ?></div><?php endif; ?>
    <form method="POST" action="edit.php" style="margin-top: 1rem;">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= (int)$id ?>">
        <div class="form-group">
            <label>Nama Kategori</label>
            <input type="text" name="name" value="<?= e($category['name']) ?>" required>
        </div>
        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
            <a href="index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>