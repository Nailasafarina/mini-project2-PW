<?php
require '../config/db.php';
$errors = [];

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("ID tidak valid.");
}

// Ambil Data Awal
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch();
    if (!$product) die("Produk tidak ditemukan.");
    
    $name = $product['name'];
    $category = $product['category'];
    $price = $product['price'];
    $stock = $product['stock'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    if (mb_strlen($name) < 3) $errors['name'] = "Nama minimal 3 karakter.";
    if ($price === false || $price <= 0) $errors['price'] = "Harga harus > 0.";
    if ($stock === false || $stock < 0) $errors['stock'] = "Stok tidak boleh negatif.";

    // Cek Unik (kecuali id ini sendiri)
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM products WHERE name = :name AND id != :id");
        $check->execute(['name' => $name, 'id' => $id]);
        if ($check->fetch()) $errors['name'] = "Nama produk sudah dipakai produk lain.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE products SET name=:name, category=:category, price=:price, stock=:stock WHERE id=:id");
        $stmt->execute(compact('name', 'category', 'price', 'stock', 'id'));
        header("Location: index.php?status=updated");
        exit;
    }
}
?>
<!-- Gunakan format HTML formulir yang sama dengan create.php, cukup ubah tujuan POST dan tambahkan <input type="hidden" name="id" value="<?= $id ?>"> -->
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Produk</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Produk</h2>
        <form method="POST" action="edit.php" style="max-width: 400px; background: white; padding: 20px; border-radius: 8px;">
            <input type="hidden" name="id" value="<?= $id ?>">
            <!-- Input text disingkat untuk memuat contoh yang sama dengan Create -->
            <div class="form-group">
                <label>Nama Produk</label>
                <input name="name" value="<?= htmlspecialchars($name, ENT_QUOTES) ?>" required>
                <?php if(isset($errors['name'])) echo "<span class='error-text'>{$errors['name']}</span>"; ?>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <input name="category" value="<?= htmlspecialchars($category, ENT_QUOTES) ?>" required>
            </div>
            <div class="form-group">
                <label>Harga</label>
                <input type="number" name="price" value="<?= $price ?>" required>
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stock" value="<?= $stock ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Produk</button>
            <a href="index.php" class="btn" style="color: #475569;">Batal</a>
        </form>
    </div>
</body>
</html>