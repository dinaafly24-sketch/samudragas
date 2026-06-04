<?php
// ── AKSI UPDATE STATUS & HAPUS ───────────────────────────────
if (isset($_GET['update_status'])) {
    $id             = (int)$_GET['update_status'];
    $status_pesanan = $conn->real_escape_string($_GET['status']);
    $conn->query("UPDATE pesanan SET status='$status_pesanan' WHERE id=$id");
    header("Location: index.php?page=pesanan_admin");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM detail_pesanan WHERE pesanan_id=$id");
    $conn->query("DELETE FROM pesanan WHERE id=$id");
    header("Location: index.php?page=pesanan_admin");
    exit;
}

// ── QUERY SEMUA PESANAN ──────────────────────────────────────
$pesanan = $conn->query("
    SELECT p.*, pl.nama, pl.telepon
    FROM pesanan p
    LEFT JOIN pelanggan pl ON p.pelanggan_id = pl.id
    ORDER BY p.created_at DESC
");

// ── SIAPKAN DATA DETAIL PER PESANAN ─────────────────────────
$semua_detail = [];
$detail_res = $conn->query("SELECT * FROM detail_pesanan");
while ($d = $detail_res->fetch_assoc()) {
    $semua_detail[$d['pesanan_id']][] = $d;
}

$rows = [];
while ($p = $pesanan->fetch_assoc()) $rows[] = $p;
?>

<h2 class="page-title">📦 Pesanan Masuk</h2>
<p class="page-sub">Kelola dan update status pesanan pelanggan</p>
<p style="font-size:12px;color:#aaa;margin-top:-10px;">💡 Klik baris pesanan untuk melihat detail</p>

<?php if (count($rows) === 0): ?>
    <div class="card text-center" style="padding:60px;">
        <p style="font-size:15px;font-weight:600;color:#999;">Belum ada pesanan masuk</p>
    </div>
<?php else: ?>

<!-- ── TABEL LIST PESANAN ─────────────────────────────────── -->
<div class="card mb-3" style="overflow:hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tabel-pesanan">
            <thead style="background:#f7faf9;">
                <tr>
                    <th style="padding:12px 16px;font-size:12px;color:#888;font-weight:600;">NO. ORDER</th>
                    <th style="padding:12px 16px;font-size:12px;color:#888;font-weight:600;">NAMA</th>
                    <th style="padding:12px 16px;font-size:12px;color:#888;font-weight:600;">TELEPON</th>
                    <th style="padding:12px 16px;font-size:12px;color:#888;font-weight:600;">TANGGAL</th>
                    <th style="padding:12px 16px;font-size:12px;color:#888;font-weight:600;">STATUS</th>
                    <th style="padding:12px 16px;font-size:12px;color:#888;font-weight:600;">AKSI</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $p):
                if ($p['status'] === 'Pending')      { $bc = 'badge bg-warning text-dark'; }
                elseif ($p['status'] === 'Diproses') { $bc = 'badge bg-info text-dark'; }
                else                                 { $bc = 'badge bg-success'; }
            ?>
            <tr class="po-row" onclick="bukaDetail(<?= $p['id'] ?>)" style="cursor:pointer;" title="Klik untuk lihat detail">
                <td style="padding:12px 16px;"><strong style="color:#0e7c7b;"><?= $p['kode_pesanan'] ?></strong></td>
                <td style="padding:12px 16px;"><?= htmlspecialchars($p['nama'] ?? '-') ?></td>
                <td style="padding:12px 16px;">
                    <a href="https://wa.me/62<?= ltrim($p['telepon'] ?? '', '0') ?>" target="_blank"
                       onclick="event.stopPropagation()" style="color:#0e7c7b;">
                        <?= $p['telepon'] ?? '-' ?>
                    </a>
                </td>
                <td style="padding:12px 16px;font-size:12px;color:#666;">
                    <?= date('d M Y, H:i', strtotime($p['created_at'])) ?>
                </td>
                <td style="padding:12px 16px;">
                    <span class="<?= $bc ?>"><?= $p['status'] ?></span>
                </td>
                <td style="padding:12px 16px;" onclick="event.stopPropagation()">
                    <?php if ($p['status'] === 'Pending'): ?>
                    <a href="?page=pesanan_admin&update_status=<?= $p['id'] ?>&status=Diproses"
                       onclick="return confirm('Proses pesanan ini?')"
                       class="btn btn-sm btn-warning text-dark py-0 me-1">Proses</a>
                    <?php endif; ?>
                    <?php if ($p['status'] === 'Diproses'): ?>
                    <a href="?page=pesanan_admin&update_status=<?= $p['id'] ?>&status=Selesai"
                       onclick="return confirm('Tandai selesai?')"
                       class="btn btn-sm btn-success py-0 me-1">Selesai</a>
                    <?php endif; ?>
                    <a href="?page=pesanan_admin&hapus=<?= $p['id'] ?>"
                       onclick="return confirm('Hapus pesanan ini?')"
                       class="btn btn-sm btn-outline-danger py-0">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── PANEL DETAIL ───────────────────────────────────────── -->
<div id="po-detail-panel" style="display:none;" class="card mb-4">
    <div style="display:flex;justify-content:space-between;align-items:center;
                padding:12px 16px;background:#f7faf9;border-bottom:1px solid #e0e0e0;">
        <span style="font-weight:600;font-size:14px;" id="po-detail-judul">Detail Pesanan</span>
        <button onclick="tutupDetail()"
                style="border:none;background:none;color:#888;cursor:pointer;font-size:13px;
                       padding:4px 10px;border-radius:6px;">✕ Tutup</button>
    </div>
    <div style="padding:16px;" id="po-detail-isi"></div>
</div>

<!-- ── DATA JSON UNTUK JS ─────────────────────────────────── -->
<script>
var dataPesanan = <?php
    $arr = [];
    foreach ($rows as $p) {
        $detail_items = $semua_detail[$p['id']] ?? [];
        $arr[] = [
            'id'         => $p['id'],
            'kode'       => $p['kode_pesanan'],
            'nama'       => $p['nama'] ?? '-',
            'telepon'    => $p['telepon'] ?? '-',
            'alamat'     => $p['alamat'] ?? '-',
            'catatan'    => $p['catatan'] ?? '',
            'total'      => $p['total'],
            'status'     => $p['status'],
            'created_at' => date('d F Y, H:i', strtotime($p['created_at'])),
            'detail'     => array_map(fn($d) => [
                'nama'     => $d['nama_produk'],
                'jumlah'   => $d['jumlah'],
                'subtotal' => $d['subtotal'],
            ], $detail_items),
        ];
    }
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
?>;

function bukaDetail(id) {
    var p = dataPesanan.find(function(x){ return x.id == id; });
    if (!p) return;

    document.querySelectorAll('.po-row').forEach(function(r){ r.style.background=''; });
    var aktif = document.querySelector('.po-row[onclick="bukaDetail('+id+')"]');
    if (aktif) aktif.style.background = '#f0fbf7';

    document.getElementById('po-detail-judul').textContent = '📦 ' + p.kode;

    var bc = p.status === 'Pending'   ? 'badge bg-warning text-dark'
           : p.status === 'Diproses'  ? 'badge bg-info text-dark'
           : 'badge bg-success';

    var produkHTML = '';
    p.detail.forEach(function(d) {
        produkHTML += '<div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f0f0;">'
            + '<div><span style="font-weight:500;">' + d.nama + '</span>'
            + ' <span style="color:#888;font-size:12px;">x ' + d.jumlah + ' tabung</span></div>'
            + '<span style="color:#0e7c7b;font-weight:600;">Rp ' + Number(d.subtotal).toLocaleString('id-ID') + '</span>'
            + '</div>';
    });

    var noWA = p.telepon.replace(/^0/, '62');
    var pesan = encodeURIComponent('Halo ' + p.nama + ', pesanan kamu sudah kami ' + p.status + '!');

    var aksiHTML = '';
    if (p.status === 'Pending') {
        aksiHTML += '<a href="?page=pesanan_admin&update_status='+p.id+'&status=Diproses" '
            + 'onclick="return confirm(\'Proses pesanan ini?\')" '
            + 'class="btn btn-warning text-dark btn-sm me-2">Proses Pesanan</a>';
    }
    if (p.status === 'Diproses') {
        aksiHTML += '<a href="?page=pesanan_admin&update_status='+p.id+'&status=Selesai" '
            + 'onclick="return confirm(\'Tandai selesai?\')" '
            + 'class="btn btn-success btn-sm me-2">Tandai Selesai</a>';
    }
    aksiHTML += '<a href="https://wa.me/' + noWA + '?text=' + pesan + '" target="_blank" '
        + 'class="btn btn-success btn-sm me-2">Balas via WA</a>';
    aksiHTML += '<a href="invoice.php?id='+p.id+'" target="_blank" '
        + 'class="btn btn-outline-secondary btn-sm me-2">Cetak Invoice</a>';
    aksiHTML += '<a href="?page=pesanan_admin&hapus='+p.id+'" '
        + 'onclick="return confirm(\'Hapus pesanan ini?\')" '
        + 'class="btn btn-outline-danger btn-sm">Hapus</a>';

    document.getElementById('po-detail-isi').innerHTML =
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">'
        + '<div><div style="font-size:11px;color:#888;margin-bottom:3px;">NAMA</div>'
        +      '<div style="font-weight:500;">' + p.nama + '</div></div>'
        + '<div><div style="font-size:11px;color:#888;margin-bottom:3px;">TELEPON</div>'
        +      '<div><a href="https://wa.me/'+noWA+'" target="_blank" style="color:#0e7c7b;">'+p.telepon+'</a></div></div>'
        + '<div><div style="font-size:11px;color:#888;margin-bottom:3px;">ALAMAT</div>'
        +      '<div>' + p.alamat + '</div></div>'
        + '<div><div style="font-size:11px;color:#888;margin-bottom:3px;">TANGGAL</div>'
        +      '<div>' + p.created_at + '</div></div>'
        + (p.catatan ? '<div style="grid-column:1/-1"><div style="font-size:11px;color:#888;margin-bottom:3px;">CATATAN</div>'
        +      '<div>' + p.catatan + '</div></div>' : '')
        + '<div style="grid-column:1/-1"><div style="font-size:11px;color:#888;margin-bottom:3px;">STATUS</div>'
        +      '<span class="' + bc + '">' + p.status + '</span></div>'
        + '</div>'
        + '<div style="background:#f7faf9;border-radius:8px;padding:12px;margin-bottom:16px;">'
        +   '<div style="font-size:12px;font-weight:600;color:#444;margin-bottom:8px;">DETAIL PRODUK</div>'
        +   produkHTML
        +   '<div style="display:flex;justify-content:space-between;padding-top:10px;font-weight:700;">'
        +     '<span>Total Pembayaran</span>'
        +     '<span style="color:#0e7c7b;">Rp ' + Number(p.total).toLocaleString('id-ID') + '</span>'
        +   '</div>'
        + '</div>'
        + '<div>' + aksiHTML + '</div>';

    var panel = document.getElementById('po-detail-panel');
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function tutupDetail() {
    document.getElementById('po-detail-panel').style.display = 'none';
    document.querySelectorAll('.po-row').forEach(function(r){ r.style.background=''; });
}
</script>

<?php endif; ?>