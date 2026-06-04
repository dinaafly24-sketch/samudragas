<?php
// Hapus admin - 
if (isset($_GET['hapus'])) {
    $hapus_id = (int)$_GET['hapus'];
    if ($hapus_id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id=$hapus_id AND role='admin'");
        header("Location: index.php?page=kelola_admin");
        exit;
    }
}
$error = '';
$sukses = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = $conn->real_escape_string($_POST['nama']);
    $telepon  = $conn->real_escape_string($_POST['telepon']);
    $password = $_POST['password'];
    if (empty($nama) || empty($telepon) || empty($password)) {
        $error = 'Semua field wajib diisi!';
    } else {
        $cek = $conn->query("SELECT id FROM users WHERE telepon='$telepon'");
        if ($cek->num_rows > 0) {
            $error = 'No. Telepon sudah terdaftar!';
        } else {
            $pass_md5 = md5($password);
            $conn->query("INSERT INTO users (nama, telepon, password, role) 
                VALUES ('$nama', '$telepon', '$pass_md5', 'admin')");
            $sukses = 'Admin baru berhasil ditambahkan!';
        }
    }
}
$admins = $conn->query("SELECT * FROM users WHERE role='admin' ORDER BY created_at DESC");
?>
<h2 class="page-title">Kelola Admin</h2>
<p class="page-sub">Tambah dan kelola akun admin</p>
<?php if ($error): ?>   
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>
<?php if ($sukses): ?>
    <div class="alert alert-success"><?= $sukses ?></div>
<?php endif; ?>

<!-- FORM TAMBAH ADMIN -->
<div class="card" style="padding:24px; margin-bottom:24px;">
    <h3 class="pr-section-title">Tambah Admin Baru</h3>
    <form method="POST">
        <div style="margin-bottom:12px;">
            <label class="pr-label">NAMA</label>
            <input type="text" name="nama" class="pr-input" placeholder="Nama admin" required>
        </div>
        <div style="margin-bottom:12px;">
            <label class="pr-label">NO. TELEPON</label>
            <input type="tel" name="telepon" class="pr-input" placeholder="08xxxxxxxxx" required>
        </div>
        <div style="margin-bottom:16px;">
            <label class="pr-label">PASSWORD</label>
            <input type="password" name="password" class="pr-input" placeholder="Password" required>
        </div>
        <button type="submit" class="pr-btn-tambah">Tambah Admin</button>
    </form>
</div>

<!-- DAFTAR ADMIN -->
<div class="card" style="padding:24px; overflow:visible !important;">
    <h3 class="pr-section-title">Daftar Admin</h3>

    <!-- WRAPPER SCROLL -->
    <div style="width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch;">
        <table class="pr-table" style="min-width:500px;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>No. Telepon</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($a = $admins->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($a['nama']) ?></td>
                    <td style="white-space:nowrap;"><?= $a['telepon'] ?></td>
                    <td style="white-space:nowrap;"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
                    <td>
                        <?php if ($a['id'] != $_SESSION['user_id']): ?>
                        <a href="?page=kelola_admin&hapus=<?= $a['id'] ?>"
                           onclick="return confirm('Hapus admin ini?')"
                           style="color:#e53935; font-size:13px; font-weight:600;">Hapus</a>
                        <?php else: ?>
                        <span style="color:#999; font-size:13px;">Akun Kamu</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>