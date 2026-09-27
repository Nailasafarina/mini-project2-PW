<?php
session_start();
require '../config/db.php';

// Generate CSRF Token untuk fitur Delete
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

// Menangani Pencarian (GET)
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $sql = "SELECT * FROM products WHERE name LIKE :q OR category LIKE :q ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['q' => "%$q%"]);
} else {
    $sql = "SELECT * FROM products ORDER BY id DESC";
    $stmt = $pdo->query($sql);
}
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Product Manager</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h1>Manajemen Produk</h1>
        
        <?php if(($_GET['status'] ?? '') === 'created'): ?>
            <div class="alert">Produk berhasil disimpan.</div>
        <?php elseif(($_GET['status'] ?? '') === 'deleted'): ?>
            <div class="alert">Produk berhasil dihapus.</div>
        <?php elseif(($_GET['status'] ?? '') === 'updated'): ?>
            <div class="alert">Produk berhasil diubah.</div>
        <?php endif; ?>

        <div class="header-actions">
            <a href="create.php" class="btn btn-primary">+ Tambah Produk</a>
            <form method="GET" class="search-form">
                <input type="text" name="q" placeholder="Cari produk..." value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>
        </div>

        <div class="products">
            <?php if(count($products) > 0): ?>
                <?php foreach ($products as $p): ?>
                    <div class="card">
                        <!-- Menghindari XSS dengan htmlspecialchars -->
                        <h3><?= htmlspecialchars($p['name'], ENT_QUOTES, "UTF-8") ?></h3>
                        <p><small><?= htmlspecialchars($p['category'], ENT_QUOTES, "UTF-8") ?></small></p>
                        <p><strong>Rp <?= number_format($p['price'], 0, ",", ".") ?></strong></p>
                        <p>Stok: <?= (int)$p['stock'] ?></p>
                        
                        <div class="card-actions">
                            <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-edit">Edit</a>
                            <!-- Hapus menggunakan POST + CSRF -->
                            <form method="POST" action="delete.php" style="margin:0;" onsubmit="return confirm('Yakin ingin menghapus?');">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Produk tidak ditemukan.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>