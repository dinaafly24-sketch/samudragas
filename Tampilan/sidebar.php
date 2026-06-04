<?php $page = $_GET['page'] ?? 'dashboard'; ?>
<div id="overlay" onclick="toggleSidebar()"></div>
<aside class="sidebar" id="sidebar">
    <div class="logo-area">
        <div class="logo-name">Samudra Gas</div>
        <div class="logo-sub">Sistem Informasi Pelanggan</div>
    </div>

    <div class="menu">
        <div class="nav-section-label">Menu Utama</div>
        <a href="?page=dashboard" class="menu-item <?= $page === 'dashboard' ? 'active' : '' ?>">
            Dashboard
        </a>
        <a href="?page=pesanan_admin" class="menu-item <?= $page === 'pesanan_admin' ? 'active' : '' ?>">
            Pesanan Masuk
        </a>
        <a href="?page=produk" class="menu-item <?= $page === 'produk' ? 'active' : '' ?>">
            Kelola Produk
        </a>
        <a href="?page=distribusi" class="menu-item <?= $page === 'distribusi' ? 'active' : '' ?>">
            Distribusi Harian
        </a>
        <a href="?page=kelola_admin" class="menu-item <?= $page === 'kelola_admin' ? 'active' : '' ?>">
            Kelola Admin
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="avatar">AD</div>
            <div class="user-info">
                <div class="user-name">Admin</div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        <button class="btn btn-outline-danger btn-sm w-100 mt-2" onclick="konfirmasiLogout()">
            🚪 Keluar
        </button>
    </div>
</aside>

<!-- Modal Konfirmasi Logout -->
<div class="modal fade" id="modalLogout" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-body text-center py-4">
                <div style="font-size:48px; margin-bottom:12px;">🚪</div>
                <h5 class="fw-bold mb-1">Yakin ingin keluar?</h5>
                <p class="text-muted small mb-4">Sesi kamu akan diakhiri dan kamu akan diarahkan ke halaman login.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <a href="logout.php" class="btn btn-danger px-4">Ya, Keluar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
}
function konfirmasiLogout() {
    var modal = new bootstrap.Modal(document.getElementById('modalLogout'));
    modal.show();
}
</script>