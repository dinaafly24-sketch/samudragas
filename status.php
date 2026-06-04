<?php
session_start();
require 'Config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pelanggan') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$pesanan = $conn->query("SELECT * FROM pesanan WHERE pelanggan_id = '$user_id' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pesanan — Samudra Gas</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f2f5;
            color: #1a1a1a;
        }

        /* NAVBAR */
        .navbar-custom {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 14px 20px;
            position: sticky;
            top: 0;
            z-index: 300;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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

        /* SIDEBAR */
        .sidebar {
            width: 220px;
            background: #3a3a3a;
            position: sticky;
            top: 53px;
            height: calc(100vh - 53px);
            overflow-y: auto;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .nav-item-side {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px;
            color: rgba(255, 255, 255, 0.75);
            font-size: 14px;
            border-left: 3px solid transparent;
            transition: background 0.15s;
            text-decoration: none;
        }

        .nav-item-side:hover {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .nav-item-side.active {
            background: rgba(255, 255, 255, 0.13);
            color: white;
            border-left: 3px solid #aaa;
        }

        .sidebar-footer {
            border-top: 0.5px solid rgba(255, 255, 255, 0.12);
            padding: 8px 0;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px;
            color: rgba(255, 100, 100, 0.9);
            font-size: 14px;
            background: none;
            border: none;
            width: 100%;
            border-left: 3px solid transparent;
            transition: background 0.15s;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: rgba(255, 80, 80, 0.1);
            color: rgba(255, 100, 100, 0.9);
        }

        /* CARD PESANAN */
        .pesanan-card {
            border-radius: 14px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .kode-pesanan {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .badge-pending {
            background: #fff3e0 !important;
            color: #e65100 !important;
        }

        .badge-diproses {
            background: #e3f2fd !important;
            color: #1565c0 !important;
        }

        .badge-selesai {
            background: #e8f5e9 !important;
            color: #2e7d32 !important;
        }

        .item-harga {
            color: #0f766e;
            font-weight: 600;
        }

        .total-angka {
            font-family: 'Syne', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: #0e7c7b;
        }

        /* PROGRESS */
        .progress-track {
            display: flex;
            align-items: center;
            margin: 16px 0;
        }

        .progress-step {
            flex: 1;
            text-align: center;
            font-size: 11px;
            color: #999;
            position: relative;
        }

        .progress-step::before {
            content: '';
            display: block;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #e0e0e0;
            margin: 0 auto 6px;
            border: 2px solid #e0e0e0;
        }

        .progress-step.aktif::before {
            background: #0e7c7b;
            border-color: #0e7c7b;
        }

        .progress-step.aktif {
            color: #0e7c7b;
            font-weight: 700;
        }

        .progress-line {
            flex: 1;
            height: 2px;
            background: #e0e0e0;
            margin-bottom: 22px;
        }

        .progress-line.aktif {
            background: #0e7c7b;
        }

        /* KOSONG */
        .kosong {
            text-align: center;
            padding: 60px 20px;
            color: #999;
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e0e0e0;
        }

        .kosong a {
            color: #0e7c7b;
            font-weight: 600;
            text-decoration: none;
        }

        /* TOMBOL WA */
        .btn-wa {
            display: inline-block;
            margin-top: 12px;
            padding: 8px 16px;
            background: #25D366;
            color: #fff;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-wa:hover {
            background: #1ebe5d;
            color: #fff;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -240px;
                height: 100vh;
                z-index: 400;
                transition: left 0.3s ease;
            }

            .sidebar.open {
                left: 0;
            }

            .sidebar-overlay {
                z-index: 399 !important;
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar-custom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light btn-sm" onclick="toggleSidebar()">☰</button>
            <div class="nav-logo">Samudra <span>Gas</span></div>
        </div>
    </nav>

    <!-- SIDEBAR OVERLAY HP -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:250;"></div>

    <div class="d-flex">

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <nav class="pt-2">
                <a href="katalog.php" class="nav-item-side">
                    <span>🛒</span>
                    <span>Katalog</span>
                </a>
                <a href="status.php" class="nav-item-side active">
                    <span>📋</span>
                    <span>Status Pemesanan</span>
                </a>
            </nav>
            <div class="sidebar-footer mt-auto">
                <a href="logout.php" class="logout-btn">
                    <span>🚪</span>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>

        <!-- KONTEN -->
        <main class="flex-grow-1 p-4">
            <div class="container" style="max-width:760px;">
                <h4 class="fw-bold mb-1">Status Pesanan Saya</h4>
                <p class="text-muted small mb-4">Halo <?= $_SESSION['user_nama'] ?>, berikut daftar pesanan kamu</p>

                <?php if ($pesanan->num_rows === 0): ?>
                    <div class="kosong">
                        <p style="font-size:40px;margin-bottom:12px;">📦</p>
                        <p style="font-size:16px;font-weight:700;margin-bottom:8px;">Belum ada pesanan</p>
                        <p class="mb-3">Yuk mulai pesan produk gas kami!</p>
                        <a href="katalog.php" class="btn btn-sm" style="background:#0e7c7b;color:#fff;">Lihat Katalog</a>
                    </div>

                <?php else: ?>
                    <?php while ($p = $pesanan->fetch_assoc()): ?>
                        <?php
                        $badge = '';
                        if ($p['status'] === 'Pending') $badge = 'badge-pending';
                        elseif ($p['status'] === 'Diproses') $badge = 'badge-diproses';
                        else $badge = 'badge-selesai';

                        $detail = $conn->query("SELECT * FROM detail_pesanan WHERE pesanan_id = '{$p['id']}'");
                        ?>

                        <div class="card pesanan-card mb-3">
                            <div class="card-body">

                                <!-- Top: kode + badge -->
                                <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
                                    <div>
                                        <div class="kode-pesanan"><?= $p['kode_pesanan'] ?></div>
                                        <small class="text-muted"><?= date('d M Y, H:i', strtotime($p['created_at'])) ?></small>
                                    </div>
                                    <span class="badge rounded-pill <?= $badge ?>"><?= $p['status'] ?></span>
                                </div>

                                <!-- Progress -->
                                <div class="progress-track">
                                    <div class="progress-step aktif">Pending</div>
                                    <div class="progress-line <?= in_array($p['status'], ['Diproses', 'Selesai']) ? 'aktif' : '' ?>"></div>
                                    <div class="progress-step <?= in_array($p['status'], ['Diproses', 'Selesai']) ? 'aktif' : '' ?>">Diproses</div>
                                    <div class="progress-line <?= $p['status'] === 'Selesai' ? 'aktif' : '' ?>"></div>
                                    <div class="progress-step <?= $p['status'] === 'Selesai' ? 'aktif' : '' ?>">Selesai</div>
                                </div>

                                <!-- Detail produk -->
                                <?php while ($d = $detail->fetch_assoc()): ?>
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <div>
                                            <div class="fw-semibold small"><?= $d['nama_produk'] ?></div>
                                            <div class="text-muted" style="font-size:12px;"><?= $d['jumlah'] ?> tabung x Rp <?= number_format($d['harga'], 0, ',', '.') ?></div>
                                        </div>
                                        <div class="item-harga small">Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></div>
                                    </div>
                                <?php endwhile; ?>

                                <!-- Total -->
                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <span class="fw-bold small">Total Pembayaran</span>
                                    <span class="total-angka">Rp <?= number_format($p['total'], 0, ',', '.') ?></span>
                                </div>

                                <!-- Alamat & catatan -->
                                <div class="mt-2 text-muted" style="font-size:13px;">
                                    Alamat: <?= $p['alamat'] ?>
                                    <?php if ($p['catatan']): ?>
                                        <br>Catatan: <?= $p['catatan'] ?>
                                    <?php endif; ?>
                                </div>

                                <a href="https://wa.me/6281345222415?text=Halo admin, saya ingin tanya status pesanan *<?= $p['kode_pesanan'] ?>*"
                                    class="btn-wa" target="_blank">
                                    Tanya Status via WhatsApp
                                </a>

                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </main>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            const overlay = document.getElementById('sidebarOverlay');
            overlay.style.display = overlay.style.display === 'block' ? 'none' : 'block';
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').style.display = 'none';
        }
    </script>
</body>

</html>