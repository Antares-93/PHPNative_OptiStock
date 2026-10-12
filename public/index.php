<?php
// public/index.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../views/header.php';

$q        = trim($_GET['q'] ?? '');
$category = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);

$where = [];
$params = [];

if ($q !== '') {
    $where[] = "(p.name LIKE :q_name OR p.sku LIKE :q_sku)";
    $params['q_name'] = "%$q%";
    $params['q_sku']  = "%$q%";
}
if ($category) {
    $where[] = "p.category_id = :cat_id";
    $params['cat_id'] = $category;
}
$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("
    SELECT p.sku, p.name, p.price, p.stock, c.name AS category_name
    FROM products p
    JOIN categories c ON c.id = p.category_id
    $whereSql
    ORDER BY p.name ASC
");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<!-- Hero Section -->
<div class="catalog-hero">
    <div class="hero-pill">Katalog Inventaris Real-Time</div>
    <h1>Katalog Persediaan Barang</h1>
    <p>Pantau ketersediaan stok fisik barang dan informasi harga secara langsung tanpa login.</p>
</div>

<!-- Search & Filter Bar Card -->
<div class="filter-card">
    <form method="GET" action="index.php" class="filter-form">
        <div class="filter-input-wrap">
            <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari berdasarkan nama produk atau SKU...">
        </div>
        
        <select name="category" class="filter-select">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= $category == $c['id'] ? 'selected' : '' ?>>
                    <?= e($c['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($q !== '' || $category): ?>
            <a href="index.php" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Table Card -->
<div class="table-card">
    <div class="table-header-info">
        <h3>Daftar Produk (<?= count($products) ?> item)</h3>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 140px;">KODE SKU</th>
                    <th>NAMA PRODUK</th>
                    <th style="width: 200px;">KATEGORI</th>
                    <th style="width: 180px;">HARGA SATUAN</th>
                    <th style="width: 170px;">STATUS STOK</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="5" class="empty-state">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">🔍</div>
                            <div style="font-weight: 600; color: var(--text-main);">Tidak ada produk yang cocok</div>
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">Coba gunakan kata kunci atau kategori lain.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><span class="sku-tag"><?= e($p['sku']) ?></span></td>
                            <td class="product-title"><?= e($p['name']) ?></td>
                            <td><span class="cat-tag"><?= e($p['category_name']) ?></span></td>
                            <td class="price-text">Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                            <td>
                                <?php if ($p['stock'] > 0): ?>
                                    <span class="stock-badge stock-available">
                                        <span class="dot"></span> Tersedia (<?= (int)$p['stock'] ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="stock-badge stock-empty">
                                        <span class="dot"></span> Habis
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../views/footer.php'; ?>