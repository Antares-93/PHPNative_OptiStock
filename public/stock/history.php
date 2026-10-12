<?php
// public/stock/history.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';
check_login();

$stmt = $pdo->query("
    SELECT sm.*, p.sku, p.name AS product_name, u.username 
    FROM stock_movements sm
    JOIN products p ON p.id = sm.product_id
    JOIN users u ON u.id = sm.user_id
    ORDER BY sm.id DESC
    LIMIT 100
");
$history = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Riwayat Mutasi Stok</h2>
    <div>
        <a href="in.php" class="btn btn-success">+ Masuk</a>
        <a href="out.php" class="btn btn-danger">- Keluar</a>
    </div>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>SKU</th>
                <th>Nama Produk</th>
                <th>Tipe</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
                <th>User Petugas</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($history)): ?>
                <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Belum ada riwayat pergerakan stok.</td></tr>
            <?php else: ?>
                <?php foreach ($history as $h): ?>
                    <tr>
                        <td style="font-size: 0.8rem; color: var(--text-muted);"><?= e($h['created_at']) ?></td>
                        <td><strong><?= e($h['sku']) ?></strong></td>
                        <td><?= e($h['product_name']) ?></td>
                        <td>
                            <span class="badge <?= $h['type'] === 'IN' ? 'badge-in' : 'badge-out' ?>">
                                <?= e($h['type']) ?>
                            </span>
                        </td>
                        <td style="font-weight: bold;"><?= (int)$h['quantity'] ?></td>
                        <td><?= e($h['notes'] ?: '-') ?></td>
                        <td><?= e($h['username']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>