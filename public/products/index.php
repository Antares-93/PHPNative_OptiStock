<?php
// public/products/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';
check_login();

// 1. Parameter Input
$q        = trim($_GET['q'] ?? '');
$category = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT);
$sort     = $_GET['sort'] ?? 'p.id';
$dir      = strtoupper($_GET['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

// 2. Allowlist Sorting Keamanan
$allowed_sorts = [
    'p.sku'   => 'SKU',
    'p.name'  => 'Nama Produk',
    'p.price' => 'Harga',
    'p.stock' => 'Stok',
    'p.id'    => 'ID'
];
if (!array_key_exists($sort, $allowed_sorts)) {
    $sort = 'p.id';
}

// 3. Bangun Query Filter Dinamis
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

// 4. Hitung Pagination
$perPage = 5;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSql");
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

// 5. Query Ambil Data
$query = "
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON c.id = p.category_id 
    $whereSql 
    ORDER BY $sort $dir 
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($query);
foreach ($params as $key => $val) {
    $stmt->bindValue(":$key", $val);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <h2>Daftar Master Produk</h2>
    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
        <a href="create.php" class="btn btn-primary">+ Tambah Produk Baru</a>
    <?php endif; ?>
</div>

<!-- Search & Filter Form -->
<form method="GET" action="index.php" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari nama atau SKU..." style="max-width: 250px;">
    
    <select name="category" style="max-width: 200px;">
        <option value="">Semua Kategori</option>
        <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= $category == $c['id'] ? 'selected' : '' ?>>
                <?= e($c['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="sort" style="max-width: 160px;">
        <?php foreach ($allowed_sorts as $k => $label): ?>
            <option value="<?= e($k) ?>" <?= $sort === $k ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="dir" style="max-width: 100px;">
        <option value="ASC" <?= $dir === 'ASC' ? 'selected' : '' ?>>A-Z / Naik</option>
        <option value="DESC" <?= $dir === 'DESC' ? 'selected' : '' ?>>Z-A / Turun</option>
    </select>

    <button type="submit" class="btn btn-secondary">Filter</button>
    <a href="index.php" class="btn btn-secondary">Reset</a>
</form>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <th>Aksi</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Tidak ada produk ditemukan.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><strong><?= e($p['sku']) ?></strong></td>
                        <td><?= e($p['name']) ?></td>
                        <td><?= e($p['category_name']) ?></td>
                        <td>Rp <?= number_format($p['price'], 0, ',', '.') ?></td>
                        <td>
                            <?= (int)$p['stock'] ?>
                            <?php if ($p['stock'] <= $p['minimum_stock']): ?>
                                <span class="badge badge-low-stock">Stok Menipis</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                            <td>
                                <a href="edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-secondary" style="padding: 0.25rem 0.6rem;">Edit</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination Links -->
<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php 
                $queryStr = http_build_query(array_merge($_GET, ['page' => $i]));
            ?>
            <a href="?<?= e($queryStr) ?>" class="<?= $page === $i ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>