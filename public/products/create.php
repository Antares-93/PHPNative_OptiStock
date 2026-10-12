<?php
// public/products/create.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';
require_role('admin');

$errors = [];
$sku = $name = $price = $category_id = $minimum_stock = '';
$selected_suppliers = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Token CSRF tidak valid.');
    }

    $sku                = strtoupper(trim($_POST['sku'] ?? ''));
    $name               = trim($_POST['name'] ?? '');
    $category_id        = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $price              = trim($_POST['price'] ?? '');
    $minimum_stock      = trim($_POST['minimum_stock'] ?? '5');
    $selected_suppliers = $_POST['suppliers'] ?? [];

    if ($sku === '') {
        $errors['sku'] = 'SKU wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ?");
        $stmt->execute([$sku]);
        if ($stmt->fetchColumn() > 0) {
            $errors['sku'] = 'SKU sudah dipakai produk lain.';
        }
    }

    if ($name === '' || mb_strlen($name) < 3) {
        $errors['name'] = 'Nama produk minimal 3 karakter.';
    }

    if (!$category_id) {
        $errors['category_id'] = 'Pilih kategori yang valid.';
    }

    if (!is_numeric($price) || (float)$price <= 0) {
        $errors['price'] = 'Harga harus angka dan lebih dari 0.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // 1. Simpan Produk
            $stmt = $pdo->prepare("
                INSERT INTO products (sku, name, category_id, price, stock, minimum_stock) 
                VALUES (?, ?, ?, ?, 0, ?)
            ");
            $stmt->execute([$sku, $name, $category_id, (float)$price, (int)$minimum_stock]);
            $productId = $pdo->lastInsertId();

            // 2. Simpan Relasi Many-to-Many ke Tabel Junction product_suppliers (Slide 8)
            if (!empty($selected_suppliers)) {
                $psStmt = $pdo->prepare("INSERT INTO product_suppliers (product_id, supplier_id, supplier_price) VALUES (?, ?, ?)");
                foreach ($selected_suppliers as $sId) {
                    $psStmt->execute([$productId, (int)$sId, (float)$price]);
                }
            }

            $pdo->commit();
            set_flash('success', 'Produk dan relasi supplier berhasil disimpan.');
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors['general'] = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$allSuppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();
?>

<div class="form-container">
    <h2 style="margin-bottom: 1.5rem;">Tambah Produk</h2>
    <?php if (isset($errors['general'])): ?><div class="alert alert-error"><?= e($errors['general']) ?></div><?php endif; ?>
    
    <form method="POST" action="create.php">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label>Kode SKU (Unik)</label>
            <input type="text" name="sku" value="<?= e($sku) ?>" placeholder="Contoh: ELEK-001">
            <?php if (isset($errors['sku'])): ?><div style="color:var(--danger); font-size:0.8rem;"><?= e($errors['sku']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Nama Produk</label>
            <input type="text" name="name" value="<?= e($name) ?>" placeholder="Nama barang">
            <?php if (isset($errors['name'])): ?><div style="color:var(--danger); font-size:0.8rem;"><?= e($errors['name']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Kategori</label>
            <select name="category_id">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $category_id == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['category_id'])): ?><div style="color:var(--danger); font-size:0.8rem;"><?= e($errors['category_id']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Harga Jual (Rp)</label>
            <input type="number" step="0.01" name="price" value="<?= e($price) ?>" placeholder="0">
            <?php if (isset($errors['price'])): ?><div style="color:var(--danger); font-size:0.8rem;"><?= e($errors['price']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Batas Minimum Stok</label>
            <input type="number" name="minimum_stock" value="<?= e($minimum_stock ?: '5') ?>">
        </div>

        <!-- Pilihan Relasi Many-to-Many Supplier -->
        <div class="form-group">
            <label>Pilih Supplier Terkait (Many-to-Many)</label>
            <div style="border: 1px solid var(--border-color); padding: 0.75rem; border-radius: 6px; max-height: 120px; overflow-y: auto;">
                <?php if (empty($allSuppliers)): ?>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">Belum ada data supplier. Tambahkan di menu Supplier.</span>
                <?php else: ?>
                    <?php foreach ($allSuppliers as $sup): ?>
                        <label style="font-weight: normal; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem; cursor: pointer;">
                            <input type="checkbox" name="suppliers[]" value="<?= (int)$sup['id'] ?>" <?= in_array($sup['id'], (array)$selected_suppliers) ? 'checked' : '' ?> style="width: auto;">
                            <?= e($sup['name']) ?>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.5rem;">
            <a href="index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>