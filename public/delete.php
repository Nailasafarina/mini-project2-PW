<?php
session_start();
require '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifikasi CSRF Token
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit("Token tidak valid");
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
    
    header("Location: index.php?status=deleted");
    exit;
} else {
    // Tolak request GET secara langsung
    http_response_code(405);
    exit("Metode tidak diizinkan. Gunakan form POST untuk menghapus.");
}
?>