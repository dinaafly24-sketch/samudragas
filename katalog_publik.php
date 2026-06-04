<?php
session_start();
require 'Config/database.php';

if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
    header("Location: index.php?page=dashboard");
    exit;
}

if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'pelanggan') {
    header("Location: katalog.php");
    exit;
}

$produk = $conn->query("SELECT * FROM produk WHERE aktif=1 ORDER BY nama ASC");
$produk_list = [];
while ($p = $produk->fetch_assoc()) {
    $produk_list[] = $p;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko — Samudra Gas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f2f5;
            color: #1a1a1a;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 300;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .hamburger {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 4px;
        }

        .hamburger span {
            display: block;
            width: 22px;
            height: 2px;
            background: #1a1a1a;
            border-radius: 2px;
        }

        .nav-logo {
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            font-weight: 800;
            color: #1a1a1a;
        }

        .nav-logo span {
            color: #0e7c7b;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-masuk {
            background: #fff;
            color: #0e7c7b;
            border: 1.5px solid #0e7c7b;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-daftar {
            background: #0e7c7b;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .app-body {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 220px;
            background: #3a3a3a;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: sticky;
            top: 53px;
            height: calc(100vh - 53px);
            overflow-y: auto;
        }

        .sidebar-nav {
            flex: 1;
            padding: 12px 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.75);
            font-size: 14px;
            border-left: 3px solid transparent;
            transition: background 0.15s;
            text-decoration: none;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.13);
            color: white;
            border-left: 3px solid #aaa;
        }

        .nav-icon {
            font-size: 16px;
            flex-shrink: 0;
            width: 20px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            padding: 28px 20px;
            min-width: 0;
        }

        .judul {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .subjudul {
            font-size: 14px;
            color: #999;
            margin-bottom: 28px;
        }

        .produk-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 18px;
        }

        .produk-card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .produk-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .produk-img {
            width: 100%;
            height: 180px;
            overflow: hidden;
            border-radius: 8px;
            background: #f9f9f9;
            display: block;
        }

        .produk-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .produk-img-placeholder {
            font-size: 40px;
        }

        .produk-nama {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .produk-desc {
            font-size: 12px;
            color: #999;
            margin-bottom: 12px;
        }

        .produk-harga {
            font-family: 'DM Sans', sans-serif;
            font-size: 19px;
            font-weight: 600;
            color: #0f766e;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }

        .produk-satuan {
            font-size: 11px;
            color: #999;
            margin-bottom: 10px;
        }

        .produk-stok {
            font-size: 12px;
            color: #666;
            margin-bottom: 14px;
        }

        .stok-habis {
            color: #e53935;
        }

        .jumlah-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .jumlah-control button {
            width: 32px;
            height: 32px;
            border: 1px solid #e0e0e0;
            background: #f0f2f5;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 700;
        }

        .jumlah-control input {
            width: 50px;
            text-align: center;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 6px;
            font-size: 14px;
        }

        .btn-tambah {
            width: 100%;
            padding: 10px;
            background: #0e7c7b;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-tambah:hover {
            background: #0a5f5e;
        }

        .btn-tambah:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: #1a1a1a;
            color: #fff;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 13.5px;
            display: none;
            z-index: 999;
        }

        .toast.show {
            display: block;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 250;
        }

        .sidebar-overlay.open {
            display: block;
        }

        .modal-login-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 500;
            align-items: center;
            justify-content: center;
        }

        .modal-login-overlay.open {
            display: flex;
        }

        .modal-login-box {
            background: #fff;
            border-radius: 16px;
            padding: 32px 28px;
            width: 340px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-login-box h4 {
            font-family: 'Syne', sans-serif;
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .modal-login-box p {
            font-size: 13.5px;
            color: #666;
            margin-bottom: 24px;
        }

        .modal-btn-login {
            display: block;
            width: 100%;
            padding: 12px;
            background: #0e7c7b;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            text-decoration: none;
            margin-bottom: 10px;
        }

        .modal-btn-daftar {
            display: block;
            width: 100%;
            padding: 12px;
            background: #fff;
            color: #0e7c7b;
            border: 1.5px solid #0e7c7b;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            text-decoration: none;
            margin-bottom: 10px;
        }

        .modal-btn-batal {
            background: none;
            border: none;
            font-size: 13px;
            color: #999;
            cursor: pointer;
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -240px;
                height: 100vh;
                z-index: 400;
                transition: left 0.3s ease;
                width: 220px;
            }

            .sidebar.open {
                left: 0;
            }

            .sidebar-overlay {
                z-index: 399 !important;
            }

            .produk-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }

            .produk-card {
                padding: 14px;
            }

            .produk-img {
                height: 160px;
            }

            .main-content {
                padding: 16px 12px;
            }

            .btn-masuk,
            .btn-daftar {
                padding: 7px 10px;
                font-size: 12px;
            }
        }

        @media (max-width: 400px) {
            .produk-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="navbar d-flex align-items-center justify-content-between">
        <div class="navbar-left d-flex align-items-center gap-3">
            <button class="hamburger" onclick="toggleSidebar()">
                <span></span><span></span><span></span>
            </button>
            <div class="nav-logo">Samudra <span>Gas</span></div>
        </div>
        <div class="nav-right">
            <a href="login.php" class="btn-masuk">Masuk</a>
            <a href="register.php" class="btn-daftar">Daftar</a>
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="app-body d-flex">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-nav">
                <a href="katalog_publik.php" class="nav-item active">
                    <span class="nav-icon">🛒</span>
                    <span>Katalog</span>
                </a>
            </div>
        </aside>

        <main class="main-content flex-grow-1">
            <div class="judul">Daftar Produk</div>
            <div class="subjudul">Pilih produk dan masukkan ke keranjang</div>

            <div class="produk-grid">
                <?php foreach ($produk_list as $p): ?>
                    <div class="produk-card" id="produk_<?= $p['id'] ?>">
                        <div class="produk-img">
                            <?php if (!empty($p['gambar'])): ?>
                                <img src="img/produk/<?= htmlspecialchars($p['gambar']) ?>" style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;">
                            <?php else: ?>
                                <span class="produk-img-placeholder">📦</span>
                            <?php endif; ?>
                        </div>
                        <div class="produk-nama fw-bold"><?= htmlspecialchars($p['nama']) ?></div>
                        <div class="produk-desc text-muted"><?= htmlspecialchars($p['deskripsi']) ?></div>
                        <div class="produk-harga fw-semibold">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                        <div class="produk-satuan">per <?= $p['satuan'] ?></div>
                        <div class="produk-stok <?= $p['stok'] == 0 ? 'stok-habis' : '' ?>">
                            Stok: <?= $p['stok'] == 0 ? 'Habis' : $p['stok'] . ' ' . $p['satuan'] ?>
                        </div>
                        <?php if ($p['stok'] > 0): ?>
                            <div class="jumlah-control">
                                <button onclick="kurang(<?= $p['id'] ?>)">-</button>
                                <input type="number" id="qty_<?= $p['id'] ?>" value="1" min="1" max="<?= $p['stok'] ?>">
                                <button onclick="tambah(<?= $p['id'] ?>, <?= $p['stok'] ?>)">+</button>
                            </div>
                            <button class="btn-tambah btn w-100" onclick="tambahKeranjang(
                            <?= $p['id'] ?>,
                            '<?= addslashes($p['nama']) ?>',
                            <?= $p['harga'] ?>,
                            '<?= $p['satuan'] ?>'
                        )">+ Tambah ke Keranjang</button>
                        <?php else: ?>
                            <button class="btn-tambah btn w-100" disabled>Stok Habis</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- MODAL LOGIN PROMPT -->
    <div class="modal-login-overlay" id="modalLogin">
        <div class="modal-login-box">
            <h4 style="font-family:'DM Sans',sans-serif;font-weight:700;">Masuk untuk Memesan</h4>
            <p>Silakan masuk atau daftar terlebih dahulu untuk melanjutkan pemesanan.</p>
            <a href="login.php?redirect=katalog" class="modal-btn-login">Masuk ke Akun</a>
            <a href="register.php" class="modal-btn-daftar">Daftar Akun Baru</a>
            <button class="modal-btn-batal" onclick="tutupModal()">Batal</button>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let keranjang = [];

        function formatRupiah(n) {
            return 'Rp ' + Number(n).toLocaleString('id-ID');
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('open');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('open');
        }

        function kurang(id) {
            const inp = document.getElementById('qty_' + id);
            if (parseInt(inp.value) > 1) inp.value = parseInt(inp.value) - 1;
        }

        function tambah(id, stok) {
            const inp = document.getElementById('qty_' + id);
            if (parseInt(inp.value) < stok) inp.value = parseInt(inp.value) + 1;
        }

        function tambahKeranjang(id, nama, harga, satuan) {
            const qty = parseInt(document.getElementById('qty_' + id).value);
            const ada = keranjang.find(k => k.id === id);
            if (ada) {
                ada.jumlah += qty;
            } else {
                keranjang.push({
                    id,
                    nama,
                    harga,
                    satuan,
                    jumlah: qty
                });
            }
            sessionStorage.setItem('keranjang', JSON.stringify(keranjang));
            document.getElementById('modalLogin').classList.add('open');
        }

        function tutupModal() {
            document.getElementById('modalLogin').classList.remove('open');
        }

        function showToast(pesan) {
            const t = document.getElementById('toast');
            t.textContent = pesan;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2500);
        }
    </script>
</body>

</html>