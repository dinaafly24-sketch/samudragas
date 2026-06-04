<?php
session_start();
require 'Config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) die('ID tidak valid.');

$p = $conn->query("SELECT p.*, pl.nama, pl.telepon 
FROM pesanan p 
LEFT JOIN pelanggan pl ON p.pelanggan_id = pl.id WHERE p.id = $id")->fetch_assoc();
if (!$p) die('Pesanan tidak ditemukan.');

$details = $conn->query("SELECT * FROM detail_pesanan WHERE pesanan_id = $id");
$detail_rows = [];
$total_tabung = 0;
while ($d = $details->fetch_assoc()) {
    $detail_rows[] = $d;
    $total_tabung += $d['jumlah'];
}

$no_sj = str_pad($p['no_sj'], 4, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk <?= $p['kode_pesanan'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #d1d5db;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0 40px;
            color: #111;
        }

        .btn-bar {
            width: 302px;
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        .btn-bar button {
            flex: 1;
            padding: 9px 0;
            border: 1px solid #999;
            border-radius: 6px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            background: #fff;
            color: #111;
        }
        .btn-bar button:hover { background: #f3f4f6; }

        .struk {
            width: 302px;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
            font-size: 12px;
            color: #111;
        }

        /* HEADER */
        .struk-header {
            text-align: center;
            padding: 16px 14px 12px;
            border-bottom: 1px dashed #aaa;
        }
        .nama-toko {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .sub {
            font-size: 10px;
            color: #444;
            margin-top: 4px;
            line-height: 1.7;
        }
        .sj-badge {
            display: inline-block;
            border: 1px solid #111;
            padding: 2px 12px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 8px;
            letter-spacing: 0.5px;
        }

        /* SECTION */
        .section {
            padding: 10px 14px;
            border-bottom: 1px dashed #aaa;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
            gap: 8px;
        }
        .info-row:last-child { margin-bottom: 0; }
        .info-label {
            font-size: 10px;
            color: #666;
            white-space: nowrap;
            flex-shrink: 0;
            padding-top: 1px;
        }
        .info-val {
            font-size: 12px;
            font-weight: 600;
            text-align: right;
        }

        /* TABUNG */
        .tabung-box {
            padding: 8px 14px;
            border-bottom: 1px dashed #aaa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .tabung-label { font-size: 10px; color: #666; }
        .tabung-val { font-size: 18px; font-weight: 700; }
        .tabung-unit { font-size: 10px; color: #888; }

        /* PRODUK */
        .produk-title {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 14px 4px;
        }
        .produk-row {
            padding: 6px 14px;
            border-bottom: 1px solid #f0f0f0;
        }
        .produk-row:last-of-type { border-bottom: none; }
        .produk-nama {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .produk-detail {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #555;
        }
        .produk-subtotal { font-weight: 700; color: #111; }

        /* TOTAL */
        .total-row {
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 2px solid #111;
            border-bottom: 1px dashed #aaa;
        }
        .total-label { font-size: 13px; font-weight: 700; }
        .total-val { font-size: 18px; font-weight: 700; }

        /* CATATAN */
        .catatan-box {
            padding: 7px 14px;
            border-bottom: 1px dashed #aaa;
            font-size: 10px;
            color: #444;
        }

        /* TTD */
        .ttd-section {
            padding: 10px 14px 14px;
            border-bottom: 1px dashed #aaa;
        }
        .ttd-title {
            font-size: 10px;
            color: #666;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        .ttd-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }
        .ttd-item { text-align: center; }
        .ttd-space {
            height: 40px;
            border-bottom: 1px solid #555;
            margin-bottom: 4px;
        }
        .ttd-name { font-size: 9px; color: #666; }

        /* PERHATIAN */
        .perhatian {
            padding: 8px 14px;
            border-bottom: 1px dashed #aaa;
        }
        .per-title { font-size: 10px; font-weight: 700; margin-bottom: 3px; }
        .per-item { font-size: 9px; color: #444; line-height: 1.7; }

        /* FOOTER */
        .struk-footer {
            text-align: center;
            padding: 10px 14px;
            font-size: 10px;
            color: #666;
            line-height: 1.7;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .btn-bar { display: none; }
            .struk { box-shadow: none; width: 80mm; }
        }
    </style>
</head>
<body>

<div class="btn-bar">
    <button onclick="window.print()">Cetak / Simpan PDF</button>
    <button onclick="window.close()">Tutup</button>
</div>

<div class="struk">

    <!-- HEADER -->
    <div class="struk-header">
        <div class="nama-toko">Samudra Gas</div>
        <div class="sub">
            Jl. Teluk Tiram Gg. ABC No. 28 RT. 13, Banjarmasin<br>
            0813-4937-3531 / 0877-1120-1170
        </div>
        <div class="sj-badge">No. SJ : <?= $no_sj ?></div>
    </div>

    <!-- INFO PESANAN -->
    <div class="section">
        <div class="info-row">
            <span class="info-label">Kode Pesanan</span>
            <span class="info-val" style="font-size:10px;"><?= $p['kode_pesanan'] ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal</span>
            <span class="info-val"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="info-val"><?= $p['status'] ?></span>
        </div>
    </div>

    <!-- INFO PELANGGAN -->
    <div class="section">
        <div class="info-row">
            <span class="info-label">Pelanggan</span>
            <span class="info-val"><?= htmlspecialchars($p['nama']) ?></span>
        </div>
        <?php if ($p['telepon']): ?>
        <div class="info-row">
            <span class="info-label">Telepon</span>
            <span class="info-val"><?= htmlspecialchars($p['telepon']) ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="info-label">Alamat</span>
            <span class="info-val" style="font-size:11px;">
                <?= $p['alamat'] ? htmlspecialchars($p['alamat']) : '-' ?>
            </span>
        </div>
    </div>

    <!-- TABUNG ISI -->
    <div class="tabung-box">
        <span class="tabung-label">Jumlah Tabung Isi</span>
        <div>
            <span class="tabung-val"><?= $p['tabung_isi'] ?: $total_tabung ?></span>
            <span class="tabung-unit"> tabung</span>
        </div>
    </div>

    <!-- PRODUK -->
    <div class="produk-title">Detail Produk</div>
    <?php foreach ($detail_rows as $d): ?>
    <div class="produk-row">
        <div class="produk-nama"><?= htmlspecialchars($d['nama_produk']) ?></div>
        <div class="produk-detail">
            <span><?= $d['jumlah'] ?> tabung &times; Rp <?= number_format($d['harga'], 0, ',', '.') ?></span>
            <span class="produk-subtotal">Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></span>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- TOTAL -->
    <div class="total-row">
        <span class="total-label">Total Pembayaran</span>
        <span class="total-val">Rp <?= number_format($p['total'], 0, ',', '.') ?></span>
    </div>

    <!-- CATATAN -->
    <?php if ($p['catatan']): ?>
    <div class="catatan-box">
        <strong>Catatan:</strong> <?= htmlspecialchars($p['catatan']) ?>
    </div>
    <?php endif; ?>

    <!-- TANDA TANGAN -->
    <div class="ttd-section">
        <div class="ttd-title">Tanda Tangan</div>
        <div class="ttd-grid">
            <div class="ttd-item">
                <div class="ttd-space"></div>
                <div class="ttd-name">Pelanggan</div>
            </div>
            <div class="ttd-item">
                <div class="ttd-space"></div>
                <div class="ttd-name">Pengangkut</div>
            </div>
            <div class="ttd-item">
                <div class="ttd-space"></div>
                <div class="ttd-name">Samudera Gas</div>
            </div>
        </div>
    </div>

    <!-- PERHATIAN -->
    <div class="perhatian">
        <div class="per-title">Perhatian:</div>
        <div class="per-item">
            1. Tabung rusak/terbakar: ganti rugi Rp 1.870.000/botol<br>
            2. Aspen patah ganti: Rp 25.000<br>
            3. Kran botol valev rusak ganti: Rp 250.000
        </div>
    </div>

    <!-- FOOTER -->
    <div class="struk-footer">
        Terima kasih atas kepercayaan Anda!<br>
        Dicetak: <?= date('d/m/Y H:i') ?> WIB
    </div>

</div>
</body>
</html>