<?php
require '../config/db.php';
$errors = [];
$name = $category = '';
$price = $stock = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Normalisasi
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    // Validasi Sesuai Slide 5 & 17
    if (mb_strlen($name) < 3) $errors['name'] = "Nama minimal 3 karakter.";
    if ($price === false || $price <= 0) $errors['price'] = "Harga harus lebih dari 0.";
    if ($stock === false || $stock < 0) $errors['stock'] = "Stok tidak boleh negatif.";

    // Cek Nama Unik
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM products WHERE name = :name");
        $check->execute(['name' => $name]);
        if ($check->fetch()) $errors['name'] = "Nama produk sudah terdaftar.";
    }

    // Jika Lolos Validasi (PRG Pattern)
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock) VALUES (:name, :category, :price, :stock)");
        $stmt->execute(compact('name', 'category', 'price', 'stock'));
        header("Location: index.php?status=created");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah Produk</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <h2>Tambah Produk Baru</h2>
        <form method="POST" action="create.php" style="max-width: 400px; background: white; padding: 20px; border-radius: 8px;">
            <div class="form-group">
                <label for="name">Nama Produk</label>
                <input id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES) ?>" required>
                <?php if(isset($errors['name'])) echo "<span class='error-text'>{$errors['name']}</span>"; ?>
            </div>
            <div class="form-group">
                <label for="category">Kategori</label>
                <input id="category" name="category" value="<?= htmlspecialchars($category, ENT_QUOTES) ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Harga</label>
                <input type="number" id="price" name="price" value="<?= $price ?>" min="1" required>
                <?php if(isset($errors['price'])) echo "<span class='error-text'>{$errors['price']}</span>"; ?>
            </div>
            <div class="form-group">
                <label for="stock">Stok</label>
                <input type="number" id="stock" name="stock" value="<?= $stock ?>" min="0" required>
                <?php if(isset($errors['stock'])) echo "<span class='error-text'>{$errors['stock']}</span>"; ?>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Produk</button>
            <a href="index.php" class="btn" style="color: #475569;">Batal</a>
        </form>
    </div>
</body>
</html>