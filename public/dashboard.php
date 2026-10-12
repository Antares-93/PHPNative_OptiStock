<?php
// public/dashboard.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../views/header.php';
check_login();

// 1. Total Produk & Total Nilai Inventaris
$stats = $pdo->query("
    SELECT 
        COUNT(id) AS total_products, 
        COALESCE(SUM(stock * price), 0) AS total_value 
    FROM products
")->fetch();

// 2. Total Barang Masuk & Keluar dari Transaksi
$movements = $pdo->query("
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'IN' THEN quantity ELSE 0 END), 0) AS total_in,
        COALESCE(SUM(CASE WHEN type = 'OUT' THEN quantity ELSE 0 END), 0) AS total_out
    FROM stock_movements
")->fetch();

// 3. Daftar Produk Stok Rendah (stock <= minimum_stock)
$lowStock = $pdo->query("
    SELECT p.*, c.name AS category_name 
    FROM products p
    JOIN categories c ON c.id = p.category_id
    WHERE p.stock <= p.minimum_stock
    ORDER BY p.stock ASC
")->fetchAll();
?>

<h2 style="margin-bottom: 1.5rem;">Dashboard Inventaris</h2>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-title">Total Produk</div>
        <div class="stat-value"><?= number_format($stats['total_products']) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Total Nilai Barang</div>
        <div class="stat-value" style="font-size: 1.4rem;">Rp <?= number_format($stats['total_value'], 0, ',', '.') ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Total Barang Masuk</div>
        <div class="stat-value" style="color: var(--success);"><?= number_format($movements['total_in']) ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-title">Total Barang Keluar</div>
        <div class="stat-value" style="color: var(--danger);"><?= number_format($movements['total_out']) ?></div>
    </div>
</div>

<h3 style="margin-bottom: 1rem; color: var(--danger);">Peringatan Stok Rendah / Menipis</h3>
<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Sisa Stok</th>
                <th>Batas Minimum</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($lowStock)): ?>
                <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Semua stok produk dalam kondisi aman.</td></tr>
            <?php else: ?>
                <?php foreach ($lowStock as $row): ?>
                    <tr>
                        <td><strong><?= e($row['sku']) ?></strong></td>
                        <td><?= e($row['name']) ?></td>
                        <td><?= e($row['category_name']) ?></td>
                        <td style="font-weight: bold; color: var(--danger);"><?= (int)$row['stock'] ?></td>
                        <td><?= (int)$row['minimum_stock'] ?></td>
                        <td><span class="badge badge-low-stock">Perlu Restock</span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>