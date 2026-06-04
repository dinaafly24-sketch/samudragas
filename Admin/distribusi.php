<?php
$filter_dari   = isset($_GET['dari'])   && $_GET['dari']   !== '' ? $conn->real_escape_string($_GET['dari'])   : '';
$filter_sampai = isset($_GET['sampai']) && $_GET['sampai'] !== '' ? $conn->real_escape_string($_GET['sampai']) : '';

if ($filter_dari !== '' && strpos($filter_dari, '/') !== false) {
    $filter_dari = date('Y-m-d', strtotime($filter_dari));
}
if ($filter_sampai !== '' && strpos($filter_sampai, '/') !== false) {
    $filter_sampai = date('Y-m-d', strtotime($filter_sampai));
}

$where_filter = "WHERE p.status = 'Selesai'";
if ($filter_dari   !== '') $where_filter .= " AND DATE(p.created_at) >= '$filter_dari'";
if ($filter_sampai !== '') $where_filter .= " AND DATE(p.created_at) <= '$filter_sampai'";

$distribusi = $conn->query("
    SELECT p.id as pesanan_id,
           p.kode_pesanan,
           p.created_at as waktu_pesanan,
           pl.nama as nama_pembeli,
           GROUP_CONCAT(dp.nama_produk, '||', dp.jumlah ORDER BY dp.id SEPARATOR ';;') as produk_jumlah,
           SUM(dp.jumlah) as total_tabung,
           p.total as total_harga
    FROM pesanan p
    JOIN pelanggan pl ON p.pelanggan_id = pl.id
    JOIN detail_pesanan dp ON dp.pesanan_id = p.id
    $where_filter
    GROUP BY p.id, p.kode_pesanan, p.created_at, pl.nama, p.total
    ORDER BY p.created_at DESC
");
?>

<h2 class="page-title">Distribusi Harian</h2>
<p class="page-sub">Catatan pengiriman produk gas harian</p>

<div class="card" style="padding:20px; overflow: visible !important;">
    <h3 class="pr-section-title">Catatan Distribusi Harian</h3>

    <!-- FILTER -->
    <form method="GET" action="" class="dist-filter">
        <input type="hidden" name="page" value="distribusi">
        <div>
            <div class="pr-label">DARI TANGGAL</div>
            <input type="date" name="dari" value="<?= htmlspecialchars($filter_dari) ?>" class="pr-input">
        </div>
        <div>
            <div class="pr-label">SAMPAI TANGGAL</div>
            <input type="date" name="sampai" value="<?= htmlspecialchars($filter_sampai) ?>" class="pr-input">
        </div>
        <div class="dist-filter-btn">
            <button type="submit" class="pr-btn-tambah">Filter</button>
            <?php if ($filter_dari !== '' || $filter_sampai !== ''): ?>
                <a href="?page=distribusi" class="pr-btn-reset">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($distribusi->num_rows === 0): ?>
        <div class="dist-empty">Tidak ada data untuk rentang tanggal ini</div>
    <?php else: ?>

        <!-- WRAPPER SCROLL — ini kunci agar tabel bisa digeser di HP -->
        <div style="width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch;">
            <table class="pr-table dist-table" style="min-width:600px;">
                <thead>
                    <tr>
                        <th>Tanggal & Jam</th>
                        <th>Pembeli</th>
                        <th>Produk</th>
                        <th>Jumlah Tabung</th>
                        <th>Total</th>
                        <th>Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($dist = $distribusi->fetch_assoc()):
                        $produk_list = [];
                        foreach (explode(';;', $dist['produk_jumlah']) as $item) {
                            $parts = explode('||', $item);
                            if (count($parts) === 2) {
                                $produk_list[] = ['nama' => trim($parts[0]), 'jumlah' => (int)$parts[1]];
                            }
                        }
                    ?>
                        <tr>
                            <td style="white-space:nowrap;">
                                <?= date('d F Y', strtotime($dist['waktu_pesanan'])) ?><br>
                                <small style="color:#888;"><?= date('H:i', strtotime($dist['waktu_pesanan'])) ?></small>
                            </td>
                            <td class="pr-nama"><?= htmlspecialchars($dist['nama_pembeli']) ?></td>
                            <td>
                                <ul style="margin:0;padding-left:16px;font-size:13px;">
                                    <?php foreach ($produk_list as $p): ?>
                                        <li><?= htmlspecialchars($p['nama']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td>
                                <div style="font-size:13px;">
                                    <?php foreach ($produk_list as $p): ?>
                                        <div style="padding:1px 0;"><?= $p['jumlah'] ?> tabung</div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="pr-harga" style="white-space:nowrap;">Rp <?= number_format($dist['total_harga'], 0, ',', '.') ?></td>
                            <td>
                                <a href="invoice.php?id=<?= $dist['pesanan_id'] ?>"
                                    target="_blank"
                                    style="font-size:12px;color:#0e7c7b;font-weight:600;white-space:nowrap;text-decoration:none;">
                                    🧾 <?= $dist['kode_pesanan'] ?>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>