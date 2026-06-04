<?php
session_start();
require 'Config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pelanggan') {
    header("Location: login.php");
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
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f2f5;
            color: #1a1a1a;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* NAVBAR */
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .navbar-left { display: flex; align-items: center; gap: 12px; }
        .hamburger {
            background: none; border: none; cursor: pointer;
            display: flex; flex-direction: column; gap: 5px; padding: 4px;
        }
        .hamburger span { display: block; width: 22px; height: 2px; background: #1a1a1a; border-radius: 2px; }
        .nav-logo { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800; color: #1a1a1a; }
        .nav-logo span { color: #0e7c7b; }
        .nav-right { display: flex; align-items: center; gap: 12px; }
        .nav-user { font-size: 13px; color: #666; }
        .btn-keranjang {
            background: #0e7c7b; color: #fff; border: none;
            padding: 9px 18px; border-radius: 8px;
            font-size: 13.5px; font-family: 'DM Sans', sans-serif;
            font-weight: 600; cursor: pointer; position: relative;
        }
        .keranjang-count {
            position: absolute; top: -6px; right: -6px;
            background: #e53935; color: #fff; font-size: 10px;
            font-weight: 700; width: 18px; height: 18px;
            border-radius: 50%; display: flex;
            align-items: center; justify-content: center;
        }

        /* LAYOUT */
        .app-body { display: flex; flex: 1; }

        /* SIDEBAR */
        .sidebar {
            width: 220px;
            background: #3a3a3a;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: sticky;
            top: 53px;
            height: calc(100vh - 53px);
            overflow: hidden; /* sidebar tidak scroll */
        }
        .sidebar-nav {
            flex: 1;
            padding: 12px 0;
            overflow-y: auto; /* hanya nav yang scroll */
            -webkit-overflow-scrolling: touch;
        }

        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 13px 16px; cursor: pointer;
            color: rgba(255,255,255,0.75); font-size: 14px;
            border-left: 3px solid transparent;
            transition: background 0.15s; text-decoration: none;
        }
        .nav-item:hover { background: rgba(255,255,255,0.08); color: white; }
        .nav-item.active { background: rgba(255,255,255,0.13); color: white; border-left: 3px solid #aaa; }
        .nav-icon { font-size: 16px; flex-shrink: 0; width: 20px; text-align: center; }

        .sidebar-footer {
            border-top: 0.5px solid rgba(255,255,255,0.12);
            padding: 8px 0;
            flex-shrink: 0; /* selalu tampil di bawah */
            background: #3a3a3a;
        }
        .logout-btn {
            display: flex; align-items: center; gap: 10px;
            padding: 13px 16px; cursor: pointer;
            color: rgba(255,100,100,0.9); font-size: 14px;
            background: none; border: none; width: 100%;
            border-left: 3px solid transparent;
            transition: background 0.15s; text-decoration: none;
        }
        .logout-btn:hover { background: rgba(255,80,80,0.1); }

        /* KONTEN */
        .main-content { flex: 1; padding: 28px 20px; min-width: 0; }
        .judul { font-family: 'Syne', sans-serif; font-size: 24px; font-weight: 800; margin-bottom: 8px; }
        .subjudul { font-size: 14px; color: #999; margin-bottom: 28px; }

        .produk-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 18px;
        }
        .produk-card {
            background: #fff; border-radius: 14px; padding: 20px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .produk-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }

        .produk-img {
            width: 100%;
            height: 180px;
            overflow: hidden;
            border-radius: 8px;
            background: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }
        .produk-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }
        .produk-img-placeholder { font-size: 40px; }

        .produk-nama { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
        .produk-desc { font-size: 12px; color: #999; margin-bottom: 12px; }
        .produk-harga { font-family: 'DM Sans', sans-serif; font-size: 19px; font-weight: 600; color: #0f766e; letter-spacing: 0.3px; margin-bottom: 2px; }
        .produk-satuan { font-size: 11px; color: #999; margin-bottom: 10px; }
        .produk-stok { font-size: 12px; color: #666; margin-bottom: 14px; }
        .stok-habis { color: #e53935; }

        .jumlah-control { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .jumlah-control button {
            width: 32px; height: 32px; border: 1px solid #e0e0e0;
            background: #f0f2f5; border-radius: 6px; font-size: 16px;
            cursor: pointer; font-weight: 700;
        }
        .jumlah-control input {
            width: 50px; text-align: center; border: 1px solid #e0e0e0;
            border-radius: 6px; padding: 6px; font-size: 14px;
        }
        .btn-tambah {
            width: 100%; padding: 10px; background: #0e7c7b; color: #fff;
            border: none; border-radius: 8px; font-size: 13px;
            font-family: 'DM Sans', sans-serif; font-weight: 600; cursor: pointer;
        }
        .btn-tambah:hover { background: #0a5f5e; }
        .btn-tambah:disabled { background: #ccc; cursor: not-allowed; }

        /* KERANJANG */
        .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 400; }
        .overlay.open { display: block; }

        .keranjang-panel {
            position: fixed;
            top: 0;
            right: -420px;
            width: 400px;
            height: 100vh;
            height: 100dvh;
            background: #fff;
            box-shadow: -4px 0 20px rgba(0,0,0,0.1);
            z-index: 401;
            display: flex;
            flex-direction: column;
            transition: right 0.3s ease;
        }
        .keranjang-panel.open { right: 0; }

        .keranjang-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .keranjang-header h3 { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800; }
        .btn-tutup { background: none; border: none; font-size: 22px; cursor: pointer; color: #999; }

        .keranjang-body {
            flex: 1;
            overflow-y: auto;
            padding: 16px 24px;
            -webkit-overflow-scrolling: touch;
        }

        .keranjang-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f2f5; }
        .item-nama { font-size: 14px; font-weight: 600; }
        .item-detail { font-size: 12px; color: #999; margin-top: 2px; }
        .item-subtotal { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; color: #0e7c7b; }
        .btn-hapus-item { background: none; border: none; color: #e53935; cursor: pointer; font-size: 16px; margin-left: 8px; }
        .keranjang-kosong { text-align: center; color: #999; padding: 40px 0; font-size: 14px; }

        .keranjang-footer {
            padding: 20px 24px;
            border-top: 1px solid #e0e0e0;
            flex-shrink: 0;
            background: #fff;
            padding-bottom: max(20px, env(safe-area-inset-bottom));
        }

        .total-baris { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .total-label { font-size: 14px; color: #666; }
        .total-angka { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 800; color: #0e7c7b; }
        .btn-checkout {
            width: 100%; padding: 14px; background: #25D366; color: #fff;
            border: none; border-radius: 10px; font-size: 15px;
            font-family: 'DM Sans', sans-serif; font-weight: 700; cursor: pointer;
        }
        .btn-checkout:hover { background: #1da851; }

        .toast {
            position: fixed; bottom: 24px; left: 50%;
            transform: translateX(-50%); background: #1a1a1a; color: #fff;
            padding: 12px 24px; border-radius: 10px; font-size: 13.5px;
            display: none; z-index: 999;
        }
        .toast.show { display: block; }

        /* SIDEBAR OVERLAY HP */
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 250; }
        .sidebar-overlay.open { display: block; }

        /* RESPONSIVE HP */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -240px;
                height: 100vh;
                height: 100dvh;
                z-index: 400;
                transition: left 0.3s ease;
                width: 220px;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
            }
            .sidebar.open { left: 0; }
            .sidebar-nav {
                flex: 1;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch;
            }
            .sidebar-footer {
                flex-shrink: 0 !important;
                position: relative !important;
            }
            .sidebar-overlay { z-index: 399 !important; }
            .nav-user { display: none; }
            .produk-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
            .produk-card { padding: 14px; }
            .produk-img { height: 160px; }

            .keranjang-panel {
                width: 100%;
                right: -100%;
                height: 100vh;
                height: 100dvh;
            }
            .keranjang-panel.open { right: 0; }
            .main-content { padding: 16px 12px; }
        }

        @media (max-width: 400px) {
            .produk-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <div class="navbar d-flex align-items-center justify-content-between">
        <div class="navbar-left d-flex align-items-center gap-3">
            <button class="hamburger" onclick="toggleSidebar()">
                <span></span><span></span><span></span>
            </button>
            <div class="nav-logo">Samudra <span>Gas</span></div>
        </div>
        <div class="nav-right">
            <span class="nav-user">Halo, <?= $_SESSION['user_nama'] ?>!</span>
            <button class="btn-keranjang btn position-relative" onclick="bukaKeranjang()">
                Keranjang
                <span class="keranjang-count" id="keranjangCount">0</span>
            </button>
        </div>
    </div>

    <!-- SIDEBAR OVERLAY HP -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="app-body d-flex">

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-nav">
                <a href="katalog.php" class="nav-item active">
                    <span class="nav-icon">🛒</span>
                    <span>Katalog</span>
                </a>
                <a href="status.php" class="nav-item">
                    <span class="nav-icon">📋</span>
                    <span>Status Pemesanan</span>
                </a>
            </div>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn btn">
                    <span class="nav-icon">🚪</span>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="main-content flex-grow-1">
            <div class="judul">Daftar Produk</div>
            <div class="subjudul">Pilih produk dan masukkan ke keranjang</div>

            <div class="produk-grid">
                <?php foreach ($produk_list as $p): ?>
                    <div class="produk-card" id="produk_<?= $p['id'] ?>">
                        <div class="produk-img">
                            <?php if (!empty($p['gambar'])): ?>
                              <img src="img/produk/<?= htmlspecialchars($p['gambar']) ?>" alt="<?= htmlspecialchars($p['nama']) ?>">
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

    <!-- OVERLAY KERANJANG -->
    <div class="overlay" id="overlay" onclick="tutupKeranjang()"></div>

    <!-- KERANJANG PANEL -->
    <div class="keranjang-panel" id="keranjangPanel">
        <div class="keranjang-header">
            <h3>Keranjang Belanja</h3>
            <button class="btn-tutup btn" onclick="tutupKeranjang()">✕</button>
        </div>
        <div class="keranjang-body" id="keranjangBody">
            <div class="keranjang-kosong">Keranjang masih kosong</div>
        </div>
        <div class="keranjang-footer">
            <div class="total-baris">
                <span class="total-label">Total Pembayaran</span>
                <span class="total-angka" id="totalHarga">Rp 0</span>
            </div>
            <button class="btn-checkout btn w-100" onclick="checkout()">
                Konfirmasi via WhatsApp
            </button>
        </div>
    </div>

    <!-- TOAST -->
    <div class="toast" id="toast"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let simpan = sessionStorage.getItem('keranjang');
        let keranjang = simpan ? JSON.parse(simpan) : [];
        sessionStorage.removeItem('keranjang');
        renderKeranjang();

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
            if (ada) { ada.jumlah += qty; }
            else { keranjang.push({ id, nama, harga, satuan, jumlah: qty }); }
            renderKeranjang();
            showToast(nama + ' ditambahkan ke keranjang!');
        }

        function hapusItem(id) {
            keranjang = keranjang.filter(k => k.id !== id);
            renderKeranjang();
        }

        function renderKeranjang() {
            const body = document.getElementById('keranjangBody');
            const count = document.getElementById('keranjangCount');
            const totalItem = keranjang.reduce((a, k) => a + k.jumlah, 0);
            count.textContent = totalItem;

            if (keranjang.length === 0) {
                body.innerHTML = '<div class="keranjang-kosong">Keranjang masih kosong</div>';
                document.getElementById('totalHarga').textContent = 'Rp 0';
                return;
            }

            let total = 0;
            body.innerHTML = keranjang.map(k => {
                const subtotal = k.harga * k.jumlah;
                total += subtotal;
                return `<div class="keranjang-item">
                    <div>
                        <div class="item-nama">${k.nama}</div>
                        <div class="item-detail">${k.jumlah} ${k.satuan} x ${formatRupiah(k.harga)}</div>
                    </div>
                    <div style="display:flex;align-items:center;">
                        <span class="item-subtotal">${formatRupiah(subtotal)}</span>
                        <button class="btn-hapus-item btn" onclick="hapusItem(${k.id})">✕</button>
                    </div>
                </div>`;
            }).join('');

            document.getElementById('totalHarga').textContent = formatRupiah(total);
        }

        function bukaKeranjang() {
            document.getElementById('keranjangPanel').classList.add('open');
            document.getElementById('overlay').classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function tutupKeranjang() {
            document.getElementById('keranjangPanel').classList.remove('open');
            document.getElementById('overlay').classList.remove('open');
            document.body.style.overflow = '';
        }

        function showToast(pesan) {
            const t = document.getElementById('toast');
            t.textContent = pesan;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2500);
        }

        function checkout() {
            if (keranjang.length === 0) { showToast('Keranjang masih kosong!'); return; }
            sessionStorage.setItem('keranjang', JSON.stringify(keranjang));
            window.location.href = 'checkout.php';
        }
    </script>
</body>
</html>