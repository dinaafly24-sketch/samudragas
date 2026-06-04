<div id="konten-produk" class="tab-konten">
    <?php
    // --- HAPUS PRODUK ---
    if (isset($_GET['hapus_produk'])) {
        $id = (int)$_GET['hapus_produk'];

        // Hapus foto lama jika ada
        $res = $conn->query("SELECT foto FROM produk WHERE id=$id");
        if ($res && $row = $res->fetch_assoc()) {
            if (!empty($row['foto']) && file_exists($row['foto'])) {
                unlink($row['foto']);
            }
        }

        $conn->query("DELETE FROM produk WHERE id=$id");
        echo "<script>window.location='?page=produk';</script>";
        exit;
    }

    // --- UPLOAD FOTO ---
    function uploadFotoProduk($file_input_name, $folder = '../img/produk/') {
        if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $file    = $_FILES[$file_input_name];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) return null;
        if ($file['size'] > 2 * 1024 * 1024) return null; // Maks 2MB

        if (!is_dir($folder)) mkdir($folder, 0755, true);
        $filename = $folder . uniqid('prod_') . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $filename);
        return $filename;
    }

    // --- TAMBAH PRODUK ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_produk'])) {
        $kode  = $conn->real_escape_string($_POST['kode']);
        $nama  = $conn->real_escape_string($_POST['nama']);
        $desc  = $conn->real_escape_string($_POST['deskripsi']);
        $harga = (float)$_POST['harga'];
        $stok  = (int)$_POST['stok'];
        $foto  = uploadFotoProduk('foto');
        $foto_sql = $foto ? "'" . $conn->real_escape_string($foto) . "'" : "NULL";

        $conn->query("INSERT INTO produk (kode, nama, deskripsi, harga, stok, satuan, aktif, foto)
            VALUES ('$kode','$nama','$desc',$harga,$stok,'tabung',1,$foto_sql)");
        echo "<script>window.location='?page=produk';</script>";
        exit;
    }

    // --- UPDATE PRODUK ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_produk'])) {
        $id    = (int)$_POST['id'];
        $kode  = $conn->real_escape_string($_POST['kode']);
        $nama  = $conn->real_escape_string($_POST['nama']);
        $desc  = $conn->real_escape_string($_POST['deskripsi']);
        $harga = (float)$_POST['harga'];
        $stok  = (int)$_POST['stok'];

        $foto_sql  = "";
        $foto_baru = uploadFotoProduk('foto');
        if ($foto_baru) {
            // Hapus foto lama dari server
            $res = $conn->query("SELECT foto FROM produk WHERE id=$id");
            if ($res && $row = $res->fetch_assoc()) {
                if (!empty($row['foto']) && file_exists($row['foto'])) {
                    unlink($row['foto']);
                }
            }
            $foto_safe = $conn->real_escape_string($foto_baru);
            $foto_sql  = ", foto='$foto_safe'";
        }

        $query = "UPDATE produk SET kode='$kode', nama='$nama', deskripsi='$desc', harga=$harga, stok=$stok $foto_sql WHERE id=$id";
        if ($conn->query($query)) {
            echo "<script>window.location='?page=produk';</script>";
        } else {
            echo "Error: " . $conn->error;
        }
        exit;
    }
    ?>

    <!-- FORM TAMBAH -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="pr-section-title">Tambah Produk Baru</div>
            <form method="POST" action="?page=produk" enctype="multipart/form-data">
                <input type="hidden" name="tambah_produk" value="1">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="pr-label">Kode</div>
                        <input class="pr-input" type="text" name="kode" placeholder="SG-01" required>
                    </div>
                    <div class="col-md-4">
                        <div class="pr-label">Nama Produk</div>
                        <input class="pr-input" type="text" name="nama" placeholder="LPG 3kg" required>
                    </div>
                    <div class="col-md-4">
                        <div class="pr-label">Stok</div>
                        <input class="pr-input" type="number" name="stok" placeholder="50" required>
                    </div>
                    <div class="col-md-8">
                        <div class="pr-label">Deskripsi</div>
                        <input class="pr-input" type="text" name="deskripsi" placeholder="Keterangan">
                    </div>
                    <div class="col-md-4">
                        <div class="pr-label">Harga</div>
                        <input class="pr-input" type="number" name="harga" placeholder="25000" required>
                    </div>

                    <!-- UPLOAD FOTO -->
                    <div class="col-12">
                        <div class="pr-label">Foto Produk <span style="color:#aaa;font-size:11px;">(opsional · maks 2MB · JPG/PNG/WEBP)</span></div>
                        <div class="pr-foto-upload-area" onclick="document.getElementById('fotoTambah').click()">
                            <img id="previewTambah" src="" alt="" style="display:none; max-height:120px; border-radius:8px; margin-bottom:8px;">
                            <div id="labelTambah" class="pr-foto-label">
                                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                <span>Klik untuk pilih foto</span>
                            </div>
                            <input type="file" id="fotoTambah" name="foto" accept="image/jpeg,image/png,image/webp"
                                style="display:none" onchange="previewFoto(this,'previewTambah','labelTambah')">
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="pr-btn-tambah">+ Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL PRODUK -->
    <div class="card">
        <div class="card-body">
            <div class="pr-section-title">Daftar Produk</div>
            <div class="table-responsive">
                <table class="pr-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Foto</th>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $no = 1;
                    $produk = $conn->query("SELECT * FROM produk ORDER BY id ASC");
                    while ($pr = $produk->fetch_assoc()):
                        // Konversi path '../img/produk/...' → 'img/produk/...' untuk src HTML
                        $foto_src = !empty($pr['foto']) ? ltrim($pr['foto'], './') : '';
                        $foto_src = str_replace('../', '', $foto_src);
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>
                            <?php if (!empty($pr['foto'])): ?>
                                <img src="<?= htmlspecialchars($foto_src) ?>" alt="foto"
                                    class="pr-foto-thumb"
                                    onclick="bukaFotoModal('<?= htmlspecialchars($foto_src) ?>','<?= addslashes($pr['nama']) ?>')">
                            <?php else: ?>
                                <div class="pr-foto-kosong">—</div>
                            <?php endif; ?>
                        </td>
                        <td><span class="pr-kode"><?= $pr['kode'] ?></span></td>
                        <td class="pr-nama"><?= $pr['nama'] ?></td>
                        <td class="pr-harga">Rp <?= number_format($pr['harga'], 0, ',', '.') ?></td>
                        <td><?= $pr['stok'] ?> <?= $pr['satuan'] ?></td>
                        <td>
                            <div class="pr-aksi">
                                <button class="pr-btn-edit"
                                    onclick="bukaEdit(
                                        <?= $pr['id'] ?>,
                                        '<?= $pr['kode'] ?>',
                                        '<?= addslashes($pr['nama']) ?>',
                                        '<?= addslashes($pr['deskripsi']) ?>',
                                        <?= $pr['harga'] ?>,
                                        <?= $pr['stok'] ?>,
                                        '<?= htmlspecialchars($foto_src) ?>'
                                    )">
                                    Edit
                                </button>
                                <a href="?page=produk&hapus_produk=<?= $pr['id'] ?>"
                                    onclick="return confirm('Yakin hapus produk ini?')"
                                    class="pr-btn-hapus">
                                    Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT -->
    <div id="modalEdit" class="pr-modal-overlay" style="display:none;">
        <div class="pr-modal-box">
            <div class="pr-section-title">Edit Produk</div>
            <form method="POST" action="?page=produk" enctype="multipart/form-data">
                <input type="hidden" name="update_produk" value="1">
                <input type="hidden" name="id" id="editId">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="pr-label">Kode Barang</div>
                        <input class="pr-input" type="text" name="kode" id="editKode" required>
                    </div>
                    <div class="col-12">
                        <div class="pr-label">Nama Produk</div>
                        <input class="pr-input" type="text" name="nama" id="editNama" required>
                    </div>
                    <div class="col-12">
                        <div class="pr-label">Deskripsi</div>
                        <input class="pr-input" type="text" name="deskripsi" id="editDesc">
                    </div>
                    <div class="col-6">
                        <div class="pr-label">Harga</div>
                        <input class="pr-input" type="number" name="harga" id="editHarga" required>
                    </div>
                    <div class="col-6">
                        <div class="pr-label">Stok</div>
                        <input class="pr-input" type="number" name="stok" id="editStok" required>
                    </div>

                    <!-- UPLOAD FOTO EDIT -->
                    <div class="col-12">
                        <div class="pr-label">Foto Produk <span style="color:#aaa;font-size:11px;">(kosongkan jika tidak diganti)</span></div>
                        <div class="pr-foto-upload-area" onclick="document.getElementById('fotoEdit').click()">
                            <img id="previewEdit" src="" alt="" style="display:none; max-height:120px; border-radius:8px; margin-bottom:8px;">
                            <div id="labelEdit" class="pr-foto-label">
                                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                <span>Klik untuk ganti foto</span>
                            </div>
                            <input type="file" id="fotoEdit" name="foto" accept="image/jpeg,image/png,image/webp"
                                style="display:none" onchange="previewFoto(this,'previewEdit','labelEdit')">
                        </div>
                    </div>

                    <div class="col-6">
                        <button type="submit" class="pr-btn-tambah w-100">Simpan Perubahan</button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="pr-btn-batal w-100" onclick="tutupEdit()">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL LIHAT FOTO BESAR -->
    <div id="modalFoto" class="pr-modal-overlay" style="display:none;" onclick="tutupFotoModal()">
        <div style="background:#fff;border-radius:12px;padding:16px;max-width:90vw;text-align:center;" onclick="event.stopPropagation()">
            <div id="modalFotoNama" style="font-weight:600;margin-bottom:10px;color:#333;"></div>
            <img id="modalFotoImg" src="" alt="" style="max-width:80vw;max-height:70vh;border-radius:8px;">
            <br>
            <button onclick="tutupFotoModal()" class="pr-btn-batal" style="margin-top:12px;">Tutup</button>
        </div>
    </div>
</div>

<!-- CSS FOTO -->
<style>
.pr-foto-upload-area {
    border: 2px dashed #b2dfdb;
    border-radius: 10px;
    padding: 16px;
    text-align: center;
    cursor: pointer;
    background: #f9fffe;
    transition: border-color .2s, background .2s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}
.pr-foto-upload-area:hover {
    border-color: #00897b;
    background: #e0f2f1;
}
.pr-foto-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    color: #80cbc4;
    font-size: 13px;
}
.pr-foto-thumb {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    cursor: pointer;
    transition: transform .15s;
}
.pr-foto-thumb:hover { transform: scale(1.1); }
.pr-foto-kosong { color: #ccc; font-size: 18px; text-align: center; }
</style>

<script>
function previewFoto(input, previewId, labelId) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = document.getElementById(previewId);
        img.src = e.target.result;
        img.style.display = 'block';
        document.getElementById(labelId).style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function bukaEdit(id, kode, nama, desc, harga, stok, foto) {
    document.getElementById('editId').value    = id;
    document.getElementById('editKode').value  = kode;
    document.getElementById('editNama').value  = nama;
    document.getElementById('editDesc').value  = desc;
    document.getElementById('editHarga').value = harga;
    document.getElementById('editStok').value  = stok;

    const prevEdit = document.getElementById('previewEdit');
    const lblEdit  = document.getElementById('labelEdit');
    document.getElementById('fotoEdit').value  = '';

    if (foto) {
        prevEdit.src = foto;
        prevEdit.style.display = 'block';
        lblEdit.style.display  = 'none';
    } else {
        prevEdit.src = '';
        prevEdit.style.display = 'none';
        lblEdit.style.display  = 'flex';
    }
    document.getElementById('modalEdit').style.display = 'flex';
}

function tutupEdit() {
    document.getElementById('modalEdit').style.display = 'none';
}

function bukaFotoModal(src, nama) {
    document.getElementById('modalFotoImg').src        = src;
    document.getElementById('modalFotoNama').textContent = nama;
    document.getElementById('modalFoto').style.display = 'flex';
}

function tutupFotoModal() {
    document.getElementById('modalFoto').style.display = 'none';
}
</script>