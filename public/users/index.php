<?php
// public/users/index.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../views/header.php';
require_role('admin');

$users = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY id ASC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Manajemen Pengguna (User)</h2>
    <a href="create.php" class="btn btn-primary">+ Tambah Pengguna Baru</a>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Peran (Role)</th>
                <th>Tanggal Dibuat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><strong><?= e($u['username']) ?></strong></td>
                    <td>
                        <span class="badge" style="background: <?= $u['role'] === 'admin' ? '#e0e7ff; color: #3730a3;' : '#f1f5f9; color: #475569;' ?>">
                            <?= strtoupper(e($u['role'])) ?>
                        </span>
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.85rem;"><?= e($u['created_at']) ?></td>
                    <td>
                        <?php if ($u['id'] !== $_SESSION['user']['id']): ?>
                            <form method="POST" action="delete.php" style="display: inline-block;" onsubmit="return confirm('Hapus pengguna ini?');">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">Hapus</button>
                            </form>
                        <?php else: ?>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">(Akun Anda)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../views/footer.php'; ?>