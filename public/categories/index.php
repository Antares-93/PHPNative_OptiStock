<?php
// public/categories/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';
require_role('admin');

// Proses Tambah Kategori
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Token CSRF tidak valid.');
    }

    $name = trim($_POST['name'] ?? '');
    if ($name === '' || mb_strlen($name) < 2) {
        $errors[] = 'Nama kategori minimal 2 karakter.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Kategori dengan nama tersebut sudah ada.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            set_flash('success', 'Kategori baru berhasil ditambahkan.');
            header('Location: index.php');
            exit;
        }
    }
}

// Ambil data kategori beserta jumlah produknya (JOIN & COUNT)
$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) AS total_products 
    FROM categories c 
    LEFT JOIN products p ON p.category_id = c.id 
    GROUP BY c.id, c.name 
    ORDER BY c.name ASC
")->fetchAll();
?>

<div style="display: flex; gap: 2rem; flex-wrap: wrap;">
    <!-- Form Tambah Kategori -->
    <div style="flex: 1; min-width: 280px;">
        <div class="form-container" style="margin: 0; max-width: 100%;">
            <h3>+ Tambah Kategori</h3>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" style="margin-top: 1rem;">
                    <?php foreach ($errors as $e): ?><div><?= e($e) ?></div><?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="index.php" style="margin-top: 1rem;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label>Nama Kategori</label>
                    <input type="text" name="name" required placeholder="Misal: Perangkat Jaringan">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Kategori -->
    <div style="flex: 2; min-width: 320px;">
        <h3 style="margin-bottom: 1rem;">Daftar Kategori</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Produk</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= (int)$cat['id'] ?></td>
                            <td><strong><?= e($cat['name']) ?></strong></td>
                            <td><?= (int)$cat['total_products'] ?> produk</td>
                            <td>
                                <a href="edit.php?id=<?= (int)$cat['id'] ?>" class="btn btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">Edit</a>
                                <form method="POST" action="delete.php" style="display: inline-block;" onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>