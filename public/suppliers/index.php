<?php
// public/suppliers/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';
require_role('admin');

$suppliers = $pdo->query("SELECT * FROM suppliers ORDER BY id DESC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Daftar Master Supplier</h2>
    <a href="create.php" class="btn btn-primary">+ Tambah Supplier</a>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Supplier</th>
                <th>Telepon</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($suppliers)): ?>
                <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">Belum ada data supplier.</td></tr>
            <?php else: ?>
                <?php foreach ($suppliers as $s): ?>
                    <tr>
                        <td><?= (int)$s['id'] ?></td>
                        <td><strong><?= e($s['name']) ?></strong></td>
                        <td><?= e($s['phone'] ?: '-') ?></td>
                        <td><?= e($s['address'] ?: '-') ?></td>
                        <td>
                            <a href="edit.php?id=<?= (int)$s['id'] ?>" class="btn btn-secondary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">Edit</a>
                            <form method="POST" action="delete.php" style="display: inline-block;" onsubmit="return confirm('Hapus supplier ini?');">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>