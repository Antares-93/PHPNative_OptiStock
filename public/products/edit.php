<?php
// public/products/edit.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';
require_role('admin');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Produk tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$errors = [];
$sku           = $product['sku'];
$name          = $product['name'];
$category_id   = $product['category_id'];
$price         = $product['price'];
$minimum_stock = $product['minimum_stock'];

// Ambil supplier yang sudah terhubung dengan produk ini
$currentSuppliers = $pdo->prepare("SELECT supplier_id FROM product_suppliers WHERE product_id = ?");
$currentSuppliers->execute([$id]);
$selected_suppliers = $currentSuppliers->fetchAll(PDO::FETCH_COLUMN);

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

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE sku = ? AND id != ?");
    $stmt->execute([$sku, $id]);
    if ($stmt->fetchColumn() > 0) {
        $errors['sku'] = 'SKU sudah digunakan oleh produk lain.';
    }

    if ($name === '' || mb_strlen($name) < 3) {
        $errors['name'] = 'Nama produk minimal 3 karakter.';
    }

    if (!is_numeric($price) || (float)$price <= 0) {
        $errors['price'] = 'Harga harus angka lebih dari 0.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                UPDATE products 
                SET sku = ?, name = ?, category_id = ?, price = ?, minimum_stock = ? 
                WHERE id = ?
            ");
            $stmt->execute([$sku, $name, $category_id, (float)$price, (int)$minimum_stock, $id]);

            // Sinkronisasi Many-to-Many di tabel junction
            $pdo->prepare("DELETE FROM product_suppliers WHERE product_id = ?")->execute([$id]);
            if (!empty($selected_suppliers)) {
                $psStmt = $pdo->prepare("INSERT INTO product_suppliers (product_id, supplier_id, supplier_price) VALUES (?, ?, ?)");
                foreach ($selected_suppliers as $sId) {
                    $psStmt->execute([$id, (int)$sId, (float)$price]);
                }
            }

            $pdo->commit();
            set_flash('success', 'Data produk dan supplier berhasil diperbarui.');
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors['general'] = 'Gagal menyimpan perubahan: ' . $e->getMessage();
        }
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$allSuppliers = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC")->fetchAll();
?>

<div class="form-container">
    <h2>Edit Produk</h2>
    <?php if (isset($errors['general'])): ?><div class="alert alert-error"><?= e($errors['general']) ?></div><?php endif; ?>

    <form method="POST" action="edit.php">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" value="<?= (int)$id ?>">

        <div class="form-group">
            <label>Kode SKU</label>
            <input type="text" name="sku" value="<?= e($sku) ?>" required>
            <?php if (isset($errors['sku'])): ?><div style="color:var(--danger); font-size:0.8rem;"><?= e($errors['sku']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Nama Produk</label>
            <input type="text" name="name" value="<?= e($name) ?>" required>
            <?php if (isset($errors['name'])): ?><div style="color:var(--danger); font-size:0.8rem;"><?= e($errors['name']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Kategori</label>
            <select name="category_id">
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $category_id == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Harga Jual (Rp)</label>
            <input type="number" step="0.01" name="price" value="<?= e($price) ?>" required>
            <?php if (isset($errors['price'])): ?><div style="color:var(--danger); font-size:0.8rem;"><?= e($errors['price']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Batas Minimum Stok</label>
            <input type="number" name="minimum_stock" value="<?= e($minimum_stock) ?>">
        </div>

        <div class="form-group">
            <label>Pilih Supplier Terkait (Many-to-Many)</label>
            <div style="border: 1px solid var(--border-color); padding: 0.75rem; border-radius: 6px; max-height: 120px; overflow-y: auto;">
                <?php foreach ($allSuppliers as $sup): ?>
                    <label style="font-weight: normal; font-size: 0.9rem; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem; cursor: pointer;">
                        <input type="checkbox" name="suppliers[]" value="<?= (int)$sup['id'] ?>" <?= in_array($sup['id'], (array)$selected_suppliers) ? 'checked' : '' ?> style="width: auto;">
                        <?= e($sup['name']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.5rem;">
            <a href="index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>