<?php
// ── STAT COUNTS ──────────────────────────────────────────────
$total_pesanan    = $conn->query("SELECT COUNT(*) AS n FROM pesanan")->fetch_assoc()['n'];
$total_pendapatan = $conn->query("SELECT SUM(total) AS s FROM pesanan WHERE status='Selesai'")->fetch_assoc()['s'] ?? 0;
$pesanan_pending  = $conn->query("SELECT COUNT(*) AS n FROM pesanan WHERE status='Pending'")->fetch_assoc()['n'];
$total_pelanggan  = $conn->query("SELECT COUNT(*) AS n FROM pelanggan")->fetch_assoc()['n'];

// ── GRAFIK ───────────────────────────────────────────────────
$minggu = [];
for ($i = 6; $i >= 0; $i--) {
    $tgl   = date('Y-m-d', strtotime("-$i days"));
    $label = date('D', strtotime("-$i days"));
    $total = $conn->query("SELECT SUM(total) AS s FROM pesanan WHERE DATE(created_at)='$tgl'")->fetch_assoc()['s'] ?? 0;
    $minggu[] = ['label' => $label, 'total' => (float)$total];
}
$bulan = [];
for ($i = 5; $i >= 0; $i--) {
    $bln   = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    $total = $conn->query("SELECT SUM(total) AS s FROM pesanan WHERE DATE_FORMAT(created_at,'%Y-%m')='$bln'")->fetch_assoc()['s'] ?? 0;
    $bulan[] = ['label' => $label, 'total' => (float)$total];
}
$minggu_labels = json_encode(array_column($minggu, 'label'));
$minggu_data   = json_encode(array_column($minggu, 'total'));
$bulan_labels  = json_encode(array_column($bulan,  'label'));
$bulan_data    = json_encode(array_column($bulan,  'total'));
?>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="stat-val"><?= $total_pesanan ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="stat-val"><?= $pesanan_pending ?></div>
                <div class="stat-label">Pesanan Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="stat-val"><?= $total_pelanggan ?></div>
                <div class="stat-label">Total Pelanggan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="stat-val sm">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>
    </div>
</div>

<!-- GRAFIK -->
<div class="row g-3">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="fw-bold mb-1" style="font-size:15px;">Penjualan Mingguan</div>
                <div class="text-muted mb-3" style="font-size:12px;">7 hari terakhir</div>
                <div style="position:relative;height:200px;">
                    <canvas id="grafikMinggu"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="fw-bold mb-1" style="font-size:15px;">Penjualan Bulanan</div>
                <div class="text-muted mb-3" style="font-size:12px;">6 bulan terakhir</div>
                <div style="position:relative;height:200px;">
                    <canvas id="grafikBulan"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const chartOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: ctx => 'Rp ' + Number(ctx.raw).toLocaleString('id-ID') } }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: val => {
                    if (val >= 1000000) return 'Rp ' + (val/1000000).toFixed(1) + ' jt';
                    if (val >= 1000)    return 'Rp ' + (val/1000) + ' rb';
                    return 'Rp ' + val;
                },
                font: { size: 10 }, maxTicksLimit: 5
            },
            grid: { color: '#f0f2f5' }
        },
        x: { ticks: { font: { size: 11 } }, grid: { display: false } }
    }
};

new Chart(document.getElementById('grafikMinggu'), {
    type: 'line',
    data: {
        labels: <?= $minggu_labels ?>,
        datasets: [{ data: <?= $minggu_data ?>, borderColor: '#0e7c7b', backgroundColor: 'rgba(14,124,123,0.08)', borderWidth: 2.5, pointBackgroundColor: '#0e7c7b', pointRadius: 4, pointHoverRadius: 6, fill: true, tension: 0.4 }]
    },
    options: chartOpts
});

new Chart(document.getElementById('grafikBulan'), {
    type: 'line',
    data: {
        labels: <?= $bulan_labels ?>,
        datasets: [{ data: <?= $bulan_data ?>, borderColor: '#17c3b2', backgroundColor: 'rgba(23,195,178,0.08)', borderWidth: 2.5, pointBackgroundColor: '#17c3b2', pointRadius: 4, pointHoverRadius: 6, fill: true, tension: 0.4 }]
    },
    options: chartOpts
});
</script>