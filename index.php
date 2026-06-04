<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: katalog_publik.php");
    exit;
}

if ($_SESSION['user_role'] === 'pelanggan') {
    header("Location: katalog.php");
    exit;
}

require 'Config/database.php';

$page = $_GET['page'] ?? 'dashboard';

$allowed = [
    'dashboard',
    'pesanan_admin',
    'produk',
    'distribusi',
    'kelola_admin'
];

if (!in_array($page, $allowed)) {
    $page = 'dashboard';
}

$file_halaman = [
    'dashboard'     => 'Admin/dashboard.php',
    'pesanan_admin' => 'Admin/pesanan.php',
    'produk'        => 'Admin/produk.php',
    'distribusi'    => 'Admin/distribusi.php',
    'kelola_admin'  => 'Admin/Kelola_admin.php',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samudra Gas — CRM</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css?v=1">
    <style>
        * { font-family: 'DM Sans', sans-serif !important; }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include 'Tampilan/sidebar.php'; ?>
    <main class="main">
        <?php include 'Tampilan/header.php'; ?>
        <?php include $file_halaman[$page]; ?>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>