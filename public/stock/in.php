<?php
// public/stock/in.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/validation.php';
require_once __DIR__ . '/../../helpers/escape.php';

check_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Token CSRF tidak valid.');
    }

    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity   = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $notes      = trim($_POST['notes'] ?? '');

    if (!$product_id) {
        $errors[] = 'Silakan pilih produk yang valid.';
    }

    if (!$quantity || $quantity <= 0) {
        $errors[] = 'Jumlah masuk harus lebih besar dari 0.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Kunci baris data produk selama transaksi (ACID isolation)
            $stmt = $pdo->prepare("SELECT id, stock FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception('Produk tidak ditemukan.');
            }

            // Tambah stok fisik barang
            $updateStmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            $updateStmt->execute([$quantity, $product_id]);

            // Catat mutasi stok
            $moveStmt = $pdo->prepare("
                INSERT INTO stock_movements (product_id, user_id, type, quantity, notes)
                VALUES (?, ?, 'in', ?, ?)
            ");
            $moveStmt->execute([$product_id, $_SESSION['user']['id'], $quantity, $notes]);

            $pdo->commit();
            set_flash('success', "Stok berhasil ditambahkan sebanyak {$quantity} unit.");
            header('Location: /inventory_app/public/stock/history.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = 'Gagal mencatat barang masuk: ' . $e->getMessage();
        }
    }
}

// Ambil daftar produk untuk pilihan dropdown
$products = $pdo->query("SELECT id, name, sku, stock FROM products ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/../../views/header.php';
?>

<div class="form-container">
    <h2 style="color: var(--success); text-align: left; margin-bottom: 1.5rem;">+ Catat Barang Masuk</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?>
                <div><?= e($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="in.php">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label>Pilih Produk</label>
            <select name="product_id" required>
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= (int)$p['id'] ?>">
                        [<?= e($p['sku']) ?>] <?= e($p['name']) ?> (Stok saat ini: <?= (int)$p['stock'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Jumlah Masuk</label>
            <input type="number" name="quantity" min="1" value="1" required>
        </div>

        <div class="form-group">
            <label>Catatan / Keterangan (Opsional)</label>
            <input type="text" name="notes" placeholder="Misal: Dari Supplier PT Sabang">
        </div>

        <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.5rem;">
            <a href="/inventory_app/public/products/index.php" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" style="background: var(--success);">
                Simpan Barang Masuk
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>