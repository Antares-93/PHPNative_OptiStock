<?php
// public/stock/out.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';
check_login();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Akses tidak sah: Token CSRF tidak valid.');
    }

    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity   = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $notes      = trim($_POST['notes'] ?? '');

    if (!$product_id) {
        $errors[] = 'Pilih produk yang valid.';
    }
    if ($quantity === false || $quantity <= 0) {
        $errors[] = 'Jumlah barang keluar harus lebih dari 0.';
    }

    if (empty($errors)) {
        try {
            // DATABASE TRANSACTION (Slide 26)
            $pdo->beginTransaction();

            // 1. Kunci baris dengan SELECT ... FOR UPDATE
            $stmt = $pdo->prepare("SELECT stock, name FROM products WHERE id = :id FOR UPDATE");
            $stmt->execute(['id' => $product_id]);
            $product = $stmt->fetch();

            if (!$product) {
                throw new Exception('Produk tidak ditemukan.');
            }

            // 2. Business Logic: Barang keluar tidak boleh melebihi stok
            if ($quantity > $product['stock']) {
                throw new Exception("Stok tidak mencukupi! Sisa stok untuk {$product['name']} hanya {$product['stock']} unit.");
            }

            // 3. Kurangi stok produk
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id");
            $stmt->execute(['qty' => $quantity, 'id' => $product_id]);

            // 4. Catat histori ke stock_movements
            $stmt = $pdo->prepare("
                INSERT INTO stock_movements (product_id, user_id, type, quantity, notes) 
                VALUES (?, ?, 'OUT', ?, ?)
            ");
            $stmt->execute([$product_id, $_SESSION['user']['id'], $quantity, $notes]);

            $pdo->commit();

            set_flash('success', "Berhasil mencatat pengeluaran $quantity barang.");
            header('Location: history.php');
            exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
}

$products = $pdo->query("SELECT id, sku, name, stock FROM products ORDER BY name ASC")->fetchAll();
?>

<div class="form-container">
    <h2 style="margin-bottom: 1.5rem; color: var(--danger);">- Catat Barang Keluar</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?>
                <div><?= e($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="out.php">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label>Pilih Produk</label>
            <select name="product_id" required>
                <option value="">-- Pilih Produk --</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= (int)$p['id'] ?>">
                        <?= e($p['sku']) ?> - <?= e($p['name']) ?> (Tersedia: <?= (int)$p['stock'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Jumlah Keluar</label>
            <input type="number" name="quantity" min="1" required placeholder="0">
        </div>

        <div class="form-group">
            <label>Alasan / Keterangan</label>
            <input type="text" name="notes" placeholder="Misal: Penjualan ke Pelanggan / Barang Rusak">
        </div>

        <button type="submit" class="btn btn-danger" style="width: 100%; justify-content: center;">Simpan Barang Keluar</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>