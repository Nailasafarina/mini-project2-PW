<?php
session_start();
require '../config/db.php';

// Generate CSRF Token untuk fitur Delete
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

// Menangani Pencarian (GET)
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    // Ubah :q menjadi :name dan :category
    $sql = "SELECT * FROM products WHERE name LIKE :name OR category LIKE :category ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    
    // Kirimkan nilai untuk masing-masing parameter
    $stmt->execute([
        'name' => "%$q%",
        'category' => "%$q%"
    ]);
} else {
    $sql = "SELECT * FROM products ORDER BY id DESC";
    $stmt = $pdo->query($sql);
}
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Cemilan</title>
    <!-- Font Friendly & Ceria untuk Snack -->
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Variabel Warna Tema Coksu (Coklat Susu / Karamel / Biskuit) */
        :root {
            --bg-body: #FDF8F4;       /* Coksu sangat muda (seperti susu) */
            --bg-card: #FFFFFF;       /* Putih untuk bungkus snack */
            --text-dark: #5C4033;     /* Coklat gelap (Dark Chocolate) */
            --text-muted: #9E7D65;    /* Coklat susu pudar */
            --border-color: #EAD8C8;  
            
            --btn-primary: #C19A6B;   /* Coksu pekat / Karamel */
            --btn-primary-hover: #A67B5B;
            --btn-edit: #E6B971;      /* Kuning biskuit / keju */
            --btn-edit-hover: #D1A35C;
            --btn-delete: #E58D83;    /* Merah bata lembut (rasa balado/pedas) */
            --btn-delete-hover: #CC7369;
            
            --shadow: 0 8px 24px rgba(166, 123, 91, 0.12);
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
            margin: 0;
            padding: 40px 5%;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Tipografi Ceria */
        h1 {
            font-family: 'Fredoka', sans-serif;
            font-size: 2.8rem;
            margin-bottom: 5px;
            color: var(--text-dark);
            text-align: center;
            letter-spacing: 0.5px;
        }

        .subtitle {
            text-align: center;
            color: var(--text-muted);
            margin-bottom: 40px;
            font-size: 1.1rem;
            font-weight: 600;
        }

        /* Notifikasi Alert */
        .alert {
            background-color: #FFF;
            color: var(--text-dark);
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 16px;
            border-left: 6px solid var(--btn-primary);
            box-shadow: var(--shadow);
            font-weight: 700;
            text-align: center;
        }

        /* Toolbar Header */
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
            background: #fff;
            padding: 20px 25px;
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .btn {
            padding: 10px 22px;
            border: none;
            border-radius: 25px; /* Membulat ceria */
            color: white;
            font-size: 15px;
            font-weight: 700;
            font-family: 'Nunito', sans-serif;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        .btn:active {
            transform: translateY(0) scale(0.98);
        }

        .btn-primary { background-color: var(--btn-primary); color: #fff;}
        .btn-primary:hover { background-color: var(--btn-primary-hover); }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-form input {
            padding: 12px 20px;
            border: 2px solid var(--border-color);
            border-radius: 25px;
            background-color: var(--bg-body);
            color: var(--text-dark);
            width: 260px;
            font-family: inherit;
            font-weight: 600;
            outline: none;
            transition: all 0.3s;
        }

        .search-form input::placeholder {
            color: #C2A897;
            font-weight: 600;
        }

        .search-form input:focus {
            border-color: var(--btn-primary);
            background-color: #FFF;
            box-shadow: 0 0 0 4px rgba(193, 154, 107, 0.15);
        }

        /* Grid Produk Cemilan */
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
        }

        .product-card {
            background-color: var(--bg-card);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            border: 2px solid transparent;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 32px rgba(166, 123, 91, 0.2);
            border-color: var(--border-color);
        }

        /* Area Gambar Produk */
        .product-img-container {
            width: 100%;
            height: 220px;
            background-color: #F8EFE6; 
            overflow: hidden;
            position: relative;
        }

        .product-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .product-img-container img {
            transform: scale(1.08) rotate(1deg); /* Efek zoom sedikit memutar agar fun */
        }

        .category-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--btn-edit);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 800;
            color: #FFF;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 10;
        }

        /* Info Produk */
        .product-info {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-info h3 {
            margin: 0 0 5px 0;
            font-size: 1.3rem;
            font-family: 'Fredoka', sans-serif;
            color: var(--text-dark);
            line-height: 1.2;
        }

        .product-info .price {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--btn-primary-hover);
            margin: 8px 0;
        }

        .product-info .stock {
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: auto; 
            background: var(--bg-body);
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            width: fit-content;
        }

        /* Tombol Aksi dalam Card */
        .card-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .card-actions .btn {
            flex: 1;
            text-align: center;
            padding: 10px 0;
            font-size: 0.9rem;
        }

        .btn-edit { background-color: var(--btn-edit); color: #fff; }
        .btn-edit:hover { background-color: var(--btn-edit-hover); }
        
        .btn-danger { background-color: var(--btn-delete); color: #fff; }
        .btn-danger:hover { background-color: var(--btn-delete-hover); }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px;
            color: var(--text-muted);
            background: #fff;
            border-radius: 24px;
            border: 2px dashed var(--btn-primary);
        }
        
        .empty-state h3 {
            font-family: 'Fredoka', sans-serif;
            color: var(--btn-primary-hover);
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Pojok Cemilan 🍪</h1>
        <div class="subtitle">Katalog Jajanan, Keripik, dan Minuman Manis</div>
        
        <?php if(($_GET['status'] ?? '') === 'created'): ?>
            <div class="alert">🍿 Yeay! Cemilan baru berhasil ditambahkan ke rak.</div>
        <?php elseif(($_GET['status'] ?? '') === 'deleted'): ?>
            <div class="alert">🗑️ Cemilan berhasil dihapus dari daftar.</div>
        <?php elseif(($_GET['status'] ?? '') === 'updated'): ?>
            <div class="alert">🍩 Info cemilan berhasil diperbarui.</div>
        <?php endif; ?>

        <div class="header-actions">
            <a href="create.php" class="btn btn-primary">+ Tambah Cemilan</a>
            <form method="GET" class="search-form">
                <input type="text" name="q" placeholder="Cari basreng, makaroni..." value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit" class="btn btn-primary">Cari</button>
            </form>
        </div>

        <div class="products">
            <?php if(count($products) > 0): ?>
                <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <!-- Area Gambar -->
                        <div class="product-img-container">
                            <div class="category-badge"><?= htmlspecialchars($p['category'], ENT_QUOTES, "UTF-8") ?></div>
                            <?php 
                                // Jika tidak ada gambar produk di database, gunakan placeholder gambar cemilan/cookies
                                $imgSrc = !empty($p['image']) ? "uploads/" . htmlspecialchars($p['image']) : "https://images.unsplash.com/photo-1566478989037-eec170784d0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80"; 
                            ?>
                            <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, "UTF-8") ?>">
                        </div>

                        <!-- Area Info Produk -->
                        <div class="product-info">
                            <h3><?= htmlspecialchars($p['name'], ENT_QUOTES, "UTF-8") ?></h3>
                            <p class="price">Rp <?= number_format($p['price'], 0, ",", ".") ?></p>
                            <p class="stock">Sisa di rak: <?= (int)$p['stock'] ?> pcs</p>
                            
                            <div class="card-actions">
                                <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-edit">Edit</a>
                                <form method="POST" action="delete.php" style="margin:0; flex:1;" onsubmit="return confirm('Yakin mau menghapus cemilan ini?');">
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                    <button type="submit" class="btn btn-danger" style="width:100%;">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <h3>Yah, Raknya Kosong! 😢</h3>
                    <p>Mulai tambahkan daftar cemilan dan jajanan Anda sekarang.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>