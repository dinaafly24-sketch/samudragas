<?php
session_start();
require 'Config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'pelanggan') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama         = $conn->real_escape_string($_POST['nama']);
    $alamat       = $conn->real_escape_string($_POST['alamat']);
    $catatan      = $conn->real_escape_string($_POST['catatan'] ?? '');
    $tabung_isi    = (int)($_POST['tabung_isi'] ?? 0);
    $tabung_kosong = (int)($_POST['tabung_kosong'] ?? 0);
    $total        = (float)$_POST['total'];
    $items        = json_decode($_POST['items'], true);
    $telepon      = '';

    $kode = 'SGS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

    // Hitung berapa kali pelanggan ini sudah pesan
    $hitung = $conn->query("SELECT COUNT(*) as total FROM pesanan WHERE pelanggan_id = '{$_SESSION['user_id']}'")->fetch_assoc();
    $no_sj = $hitung['total'] + 1;

    // Simpan ke tabel pesanan
    $conn->query("INSERT INTO pesanan (kode_pesanan, pelanggan_id, alamat, tabung_isi, total, catatan, no_sj, status)
VALUES (
    '$kode',
    '{$_SESSION['user_id']}',
    '$alamat',
    '$tabung_isi',
    '$total',
    '$catatan',
    '$no_sj',
    'Pending'
)");
    $pesanan_id = $conn->insert_id;

    // Simpan detail pesanan
    foreach ($items as $item) {
        $nama_produk = $conn->real_escape_string($item['nama']);
        $harga       = (float)$item['harga'];
        $jumlah      = (int)$item['jumlah'];
        $subtotal    = $harga * $jumlah;

        $conn->query("INSERT INTO detail_pesanan (pesanan_id, produk_id, nama_produk, harga, jumlah, subtotal)
    VALUES (
        '$pesanan_id',
        '{$item['id']}',
        '$nama_produk',
        '$harga',
        '$jumlah',
        '$subtotal'
    )");
        // Kurangi stok produk
        $conn->query("UPDATE produk 
              SET stok = stok - $jumlah 
              WHERE id = {$item['id']}");
    }

    // Buat pesan WhatsApp
    $baris = [];
    $baris[] = "Halo Admin Samudra Gas! Saya ingin memesan:";
    $baris[] = "";
    $baris[] = "Kode Pesanan: *$kode*";
    $baris[] = "Nama: $nama";
    $baris[] = "Alamat: $alamat";
    $baris[] = "Tabung Isi: $tabung_isi tabung";
    $baris[] = "";
    $baris[] = "Detail Pesanan:";

    foreach ($items as $item) {
        $subtotal = number_format($item['harga'] * $item['jumlah'], 0, ',', '.');
        $baris[] = "- {$item['nama']} x {$item['jumlah']} tabung = Rp $subtotal";
    }

    $total_fmt = number_format($total, 0, ',', '.');
    $baris[] = "";
    $baris[] = "Total: *Rp $total_fmt*";

    if ($catatan) {
        $baris[] = "Catatan: $catatan";
    }

    $baris[] = "";
    $baris[] = "Terima kasih!";

    $pesan_text = implode("\n", $baris);
    $wa_url = "https://wa.me/6281345222415?text=" . rawurlencode($pesan_text);

    echo "<script>
        sessionStorage.removeItem('keranjang');
        window.location.href = '$wa_url';
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — Samudra Gas</title>
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
        }

        .navbar {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .nav-logo {
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            font-weight: 800;
        }

        .nav-logo span {
            color: #0e7c7b;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .judul {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 24px;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .card h3 {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 16px;
            color: #1a1a1a;
        }

        .label {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            margin-bottom: 4px;
            display: block;
        }

        .inp {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            background: #f9f9f9;
            color: #1a1a1a;
            outline: none;
            margin-bottom: 12px;
            transition: border 0.2s;
        }

        .inp:focus {
            border-color: #0e7c7b;
            background: #fff;
        }

        .item-list {
            margin-bottom: 8px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f2f5;
            font-size: 14px;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-row-nama {
            font-weight: 600;
        }

        .item-row-harga {
            color: #0e7c7b;
            font-weight: 700;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 2px solid #e0e0e0;
            margin-top: 8px;
        }

        .total-label {
            font-size: 15px;
            font-weight: 700;
        }

        .total-angka {
            font-family: 'Syne', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: #0e7c7b;
        }

        .btn-wa {
            width: 100%;
            padding: 15px;
            background: #25D366;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 8px;
        }

        .btn-wa:hover {
            background: #1da851;
        }

        .btn-kembali {
            display: block;
            text-align: center;
            margin-top: 12px;
            font-size: 13px;
            color: #666;
            text-decoration: none;
        }

        .bubble-wrap {
            position: relative;
            margin-bottom: 12px;
        }

        .bubble-trigger {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #f9f9f9;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            color: #999;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: border 0.2s, color 0.2s;
            text-align: left;
        }

        .bubble-trigger.dipilih {
            border-color: #0e7c7b;
            color: #1a1a1a;
            background: #fff;
            font-weight: 600;
        }

        .bubble-arrow {
            font-size: 11px;
            color: #aaa;
            transition: transform 0.25s;
        }

        .bubble-arrow.open {
            transform: rotate(180deg);
        }

        .bubble-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            width: 100%;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            z-index: 99;
            overflow: hidden;
            animation: bubbleFade 0.2s ease;
        }

        .bubble-dropdown.open {
            display: block;
        }

        @keyframes bubbleFade {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .bubble-option {
            padding: 13px 16px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            color: #1a1a1a;
            transition: background 0.15s;
            border-bottom: 1px solid #f0f2f5;
        }

        .bubble-option:last-child {
            border-bottom: none;
        }

        .bubble-option:hover {
            background: #f0fafa;
            color: #0e7c7b;
            font-weight: 600;
        }

        .bubble-detail {
            margin-top: 8px;
            animation: bubbleFade 0.2s ease;
        }

        .kosong {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <div class="nav-logo">Samudra <span>Gas</span></div>
        <div style="font-size:13px;color:#666;">Halo, <?= $_SESSION['user_nama'] ?>!</div>
    </div>

    <div class="container">
        <div class="judul">Konfirmasi Pesanan</div>
        <div id="kontenCheckout">
            <div class="kosong">Memuat data keranjang...</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const keranjang = JSON.parse(sessionStorage.getItem('keranjang') || '[]');

        function formatRupiah(n) {
            return 'Rp ' + Number(n).toLocaleString('id-ID');
        }

        function render() {
            const container = document.getElementById('kontenCheckout');

            if (keranjang.length === 0) {
                container.innerHTML = `
                    <div class="kosong">
                        <p>Keranjang kosong!</p>
                        <a href="katalog.php" style="color:#0e7c7b;font-weight:600;">Kembali ke Katalog</a>
                    </div>`;
                return;
            }

            let total = 0;
            let itemsHTML = '';

            keranjang.forEach(k => {
                const subtotal = k.harga * k.jumlah;
                total += subtotal;
                itemsHTML += `
                    <div class="item-row">
                        <div>
                            <div class="item-row-nama">${k.nama}</div>
                            <div style="font-size:12px;color:#999;">${k.jumlah} ${k.satuan} x ${formatRupiah(k.harga)}</div>
                        </div>
                        <div class="item-row-harga">${formatRupiah(subtotal)}</div>
                    </div>`;
            });

            container.innerHTML = `
                <div class="card">
                    <h3>Ringkasan Pesanan</h3>
                    <div class="item-list">${itemsHTML}</div>
                    <div class="total-row">
                        <span class="total-label">Total</span>
                        <span class="total-angka">${formatRupiah(total)}</span>
                    </div>
                </div>

                <div class="card">
                    <h3>Data Pemesan</h3>
                    <form method="POST" action="checkout.php" onsubmit="submitForm(event)">
                        <input type="hidden" name="total" id="inputTotal" value="${total}">
                        <input type="hidden" name="items" id="inputItems">

                        <label class="label">Nama Pembeli*</label>
                        <input class="inp" type="text" name="nama" value="<?= $_SESSION['user_nama'] ?>" required>

                        <label class="label">Alamat Pengiriman*</label>
                        <textarea class="inp" name="alamat" rows="3" placeholder="Masukkan alamat lengkap" required></textarea>

                        <label class="label">Tabung Isi*</label>
                        <div class="bubble-wrap">
                            <button type="button" class="bubble-trigger" onclick="toggleBubble('isi')">
                                <span id="labelIsi">Pilih opsi...</span>
                                <span class="bubble-arrow" id="arrowIsi">▼</span>
                            </button>
                            <div class="bubble-dropdown" id="dropIsi">
                                <div class="bubble-option" onclick="pilihOpsi('isi', 'sendiri', 'Tabung Sendiri')">
                                    Tabung Sendiri
                                </div>
                                <div class="bubble-option" onclick="pilihOpsi('isi', 'pinjam', 'Meminjam')">
                                    Meminjam
                                </div>
                            </div>
                            <div class="bubble-detail" id="detailIsi" style="display:none;">
                                <input class="inp" type="number" id="inputJumlahIsi" name="tabung_isi" min="1" placeholder="Masukkan jumlah tabung" oninput="updateHidden('isi')">
                            </div>
                        </div>

                        <label class="label">Catatan (opsional)</label>
                        <input class="inp" type="text" name="catatan" placeholder="Contoh: tolong antar pagi">

                        <button class="btn-wa" type="submit">Pesan & Kirim via WhatsApp</button>
                        <a href="katalog.php" class="btn-kembali">Kembali ke Katalog</a>
                    </form>
                </div>`;
        }

        let pilihanIsi = null;

        function toggleBubble(tipe) {
            const drop = document.getElementById('drop' + (tipe === 'isi' ? 'Isi' : 'Kosong'));
            const arrow = document.getElementById('arrow' + (tipe === 'isi' ? 'Isi' : 'Kosong'));
            const isOpen = drop.classList.contains('open');

            document.querySelectorAll('.bubble-dropdown').forEach(d => d.classList.remove('open'));
            document.querySelectorAll('.bubble-arrow').forEach(a => a.classList.remove('open'));

            if (!isOpen) {
                drop.classList.add('open');
                arrow.classList.add('open');
            }
        }

        function pilihOpsi(tipe, nilai, label) {
            if (tipe === 'isi') {
                pilihanIsi = nilai;
                document.getElementById('labelIsi').textContent = label;
                document.getElementById('dropIsi').classList.remove('open');
                document.getElementById('arrowIsi').classList.remove('open');
                document.querySelector('#dropIsi').closest('.bubble-wrap').querySelector('.bubble-trigger').classList.add('dipilih');

                const detail = document.getElementById('detailIsi');
                if (nilai === 'sendiri' || nilai === 'pinjam') {
                    detail.style.display = 'block';
                    document.getElementById('inputJumlahIsi').focus();
                } else {
                    detail.style.display = 'none';
                }
            }
        }

        function updateHidden(tipe) {
            // sudah pakai name="tabung_isi" langsung, tidak perlu hidden field
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.bubble-wrap')) {
                document.querySelectorAll('.bubble-dropdown').forEach(d => d.classList.remove('open'));
                document.querySelectorAll('.bubble-arrow').forEach(a => a.classList.remove('open'));
            }
        });

        function submitForm(e) {
            if (!pilihanIsi) {
                e.preventDefault();
                alert('Pilih opsi tabung isi terlebih dahulu!');
                return;
            }
            if ((pilihanIsi === 'sendiri' || pilihanIsi === 'pinjam') && !document.getElementById('inputJumlahIsi').value) {
                e.preventDefault();
                alert('Masukkan jumlah tabung isi!');
                return;
            }

            document.getElementById('inputItems').value = JSON.stringify(keranjang);
        }

        render();
    </script>
</body>

</html>