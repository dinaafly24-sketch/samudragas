<?php
session_start();
require 'Config/database.php';

$error  = '';
$sukses = '';

if (isset($_GET['reset']) && $_GET['reset'] === 'berhasil') {
    $sukses = 'Password berhasil diubah! Silakan login.';
}

// Handle reset password admin tersembunyi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_admin') {
    $telepon      = $conn->real_escape_string($_POST['telepon'] ?? '');
    $kode_rahasia = $conn->real_escape_string($_POST['kode_rahasia'] ?? '');
    $pass_baru    = $_POST['password_baru'] ?? '';
    $pass_konfirm = $_POST['password_konfirm'] ?? '';

    if (empty($telepon) || empty($kode_rahasia) || empty($pass_baru)) {
        $error_reset = 'Semua kolom wajib diisi!';
    } elseif ($pass_baru !== $pass_konfirm) {
        $error_reset = 'Password baru tidak cocok!';
    } else {
        $sql    = "SELECT * FROM users WHERE telepon='$telepon' AND kode_rahasia='$kode_rahasia'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows === 1) {
            $pass_md5 = md5($pass_baru);
            $conn->query("UPDATE users SET password='$pass_md5' WHERE telepon='$telepon'");
            $sukses_reset = 'Password admin berhasil direset! Silakan login.';
        } else {
            $error_reset = 'No telepon atau kode rahasia salah!';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $role     = $_POST['role'] ?? 'pelanggan';
    $input    = $_POST['telepon'] ?? '';
    $password = $_POST['password'] ?? '';
    $pass_md5 = md5($password);

    if (empty($input) || empty($password)) {
        $error = 'No telepon dan password wajib diisi!';
    } elseif ($role === 'admin') {
        if (substr($input, -3) !== 'adm') {
            $error = 'Login admin harus pakai "adm" di akhir nomor!';
        } else {
            $telepon = $conn->real_escape_string(substr($input, 0, -3));
        }
    } else {
        $telepon = $conn->real_escape_string($input);
    }

    if (empty($error)) {
        if ($role === 'admin') {
            $sql    = "SELECT * FROM users WHERE telepon='$telepon' AND password='$pass_md5'";
            $result = $conn->query($sql);
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_nama'] = $user['nama'];
                $_SESSION['user_role'] = 'admin';
                header("Location: index.php?page=dashboard");
                exit;
            } else {
                $error = 'No telepon atau password admin salah!';
            }
        } else {
            $sql    = "SELECT * FROM pelanggan WHERE telepon='$telepon' AND password='$pass_md5'";
            $result = $conn->query($sql);
            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_nama'] = $user['nama'];
                $_SESSION['user_role'] = 'pelanggan';
                header("Location: katalog.php");
                exit;
            } else {
                $error = 'No telepon atau password salah!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Samudra Gas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e0e0e0;
        }
        .logo { text-align: center; margin-bottom: 28px; cursor: pointer; user-select: none; }
        .logo-name { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 800; color: #1a1a1a; margin-top: 8px; }
        .logo-sub { font-size: 12px; color: #0e7c7b; letter-spacing: 1px; }
        .tab-switch {
            display: flex; background: #f0f2f5; border-radius: 10px;
            padding: 4px; margin-bottom: 24px;
        }
        .tab-btn {
            flex: 1; padding: 10px; border: none; background: transparent;
            border-radius: 8px; cursor: pointer; font-family: 'DM Sans', sans-serif;
            font-size: 13.5px; font-weight: 500; color: #999; transition: all 0.2s;
        }
        .tab-btn.active { background: #fff; color: #0e7c7b; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
        .inp {
            width: 100%; padding: 12px 14px; border: 1px solid #e0e0e0;
            border-radius: 8px; font-size: 14px; font-family: 'DM Sans', sans-serif;
            background: #f9f9f9; color: #1a1a1a; outline: none;
            margin-bottom: 12px; transition: border 0.2s;
        }
        .inp:focus { border-color: #0e7c7b; background: #fff; }
        .btn-login {
            width: 100%; padding: 13px; background: #0e7c7b; color: #fff;
            border: none; border-radius: 8px; font-size: 15px;
            font-family: 'DM Sans', sans-serif; font-weight: 600;
            cursor: pointer; margin-top: 4px; transition: background 0.2s;
        }
        .btn-login:hover { background: #0a5f5e; }
        .btn-back {
            width: 100%; padding: 10px; background: transparent; color: #999;
            border: 1px solid #e0e0e0; border-radius: 8px; font-size: 13px;
            font-family: 'DM Sans', sans-serif; cursor: pointer; margin-top: 8px;
            transition: all 0.2s;
        }
        .btn-back:hover { background: #f0f2f5; color: #555; }
        .error {
            background: #fdecea; color: #c62828; padding: 10px 14px;
            border-radius: 8px; font-size: 13px; margin-bottom: 14px;
            border: 1px solid #ffcdd2;
        }
        .sukses {
            background: #e8f5e9; color: #2e7d32; padding: 10px 14px;
            border-radius: 8px; font-size: 13px; margin-bottom: 14px;
            border: 1px solid #c8e6c9;
        }
        .daftar { text-align: center; margin-top: 16px; font-size: 13px; color: #999; }
        .daftar a { color: #0e7c7b; text-decoration: none; font-weight: 500; }
        .lupa-link { text-align: right; margin-top: -6px; margin-bottom: 12px; font-size: 12px; }
        .lupa-link a { color: #0e7c7b; text-decoration: none; font-weight: 500; }
        .reset-title {
            font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 800;
            color: #1a1a1a; margin-bottom: 6px;
        }
        .reset-sub { font-size: 12px; color: #999; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="login-box">

        <!-- LOGO — klik 3x untuk reset admin -->
        <div class="logo" id="logo-click">
            <div class="logo-name">Samudra Gas</div>
            <div class="logo-sub">SISTEM INFORMASI PELANGGAN</div>
        </div>

        <!-- ===== FORM LOGIN (default) ===== -->
        <div id="mainLogin">
            <div class="tab-switch">
                <button class="tab-btn active" id="tabPelanggan" type="button"
                    onclick="switchTab('pelanggan')">Pelanggan</button>
                <button class="tab-btn" id="tabAdmin" type="button"
                    onclick="switchTab('admin')">Admin</button>
            </div>

            <?php if ($sukses): ?>
                <div class="sukses">✅ <?= $sukses ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <!-- Form Pelanggan -->
            <form method="POST" id="formPelanggan">
                <input type="hidden" name="role" value="pelanggan">
                <input class="inp" type="tel" name="telepon" placeholder="No. Telepon (cth: 08579)">
                <input class="inp" type="password" name="password" placeholder="Password">
                <div class="lupa-link"><a href="lupa_password.php">Lupa password?</a></div>
                <button class="btn-login" type="submit">Masuk</button>
                <div class="daftar text-center mt-3">
                    Belum punya akun? <a href="register.php">Daftar di sini</a>
                </div>
            </form>

            <!-- Form Admin -->
            <form method="POST" id="formAdmin" style="display:none;">
                <input type="hidden" name="role" value="admin">
                <input class="inp" type="text" name="telepon" inputmode="text" autocapitalize="none"
                    placeholder="No. Telp + 'adm' (cth: 08123adm)">
                <input class="inp" type="password" name="password" placeholder="Password">
                <button class="btn-login" type="submit">Masuk sebagai Admin</button>
            </form>
        </div>

        <!-- ===== FORM RESET PASSWORD ADMIN (hidden, muncul klik logo 3x) ===== -->
        <div id="resetAdmin" style="display:none;">
            <div class="reset-title">🔐 Reset Password Admin</div>
            <div class="reset-sub">Masukkan no. telepon dan kode rahasia untuk mereset password.</div>

            <?php if (!empty($sukses_reset)): ?>
                <div class="sukses">✅ <?= $sukses_reset ?></div>
            <?php endif; ?>
            <?php if (!empty($error_reset)): ?>
                <div class="error"><?= $error_reset ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
    <input type="hidden" name="action" value="reset_admin">
    
    <input class="inp" type="tel" name="telepon" 
        placeholder="No. Telepon admin"
        autocomplete="off">
    
    <input class="inp" type="text" name="kode_rahasia" 
        placeholder="Kode Rahasia"
        autocomplete="off"
        readonly onfocus="this.removeAttribute('readonly')">
    
    <input class="inp" type="password" name="password_baru" 
        placeholder="Password Baru"
        autocomplete="new-password"
        readonly onfocus="this.removeAttribute('readonly')">
    
    <input class="inp" type="password" name="password_konfirm" 
        placeholder="Konfirmasi Password Baru"
        autocomplete="new-password"
        readonly onfocus="this.removeAttribute('readonly')">
    
    <button class="btn-login" type="submit">Reset Password</button>
    <button type="button" class="btn-back" onclick="sembunyikanReset()">← Kembali ke Login</button>
</form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Klik logo 3x → tampilkan form reset admin
        let clickCount = 0;
        document.getElementById('logo-click').addEventListener('click', function () {
            clickCount++;
            if (clickCount === 3) {
                document.getElementById('mainLogin').style.display = 'none';
                document.getElementById('resetAdmin').style.display = 'block';
                clickCount = 0;
            }
        });

        function sembunyikanReset() {
            document.getElementById('resetAdmin').style.display = 'none';
            document.getElementById('mainLogin').style.display = 'block';
        }

        function switchTab(tab) {
            if (tab === 'pelanggan') {
                document.getElementById('formPelanggan').style.display = 'block';
                document.getElementById('formAdmin').style.display = 'none';
                document.getElementById('tabPelanggan').classList.add('active');
                document.getElementById('tabAdmin').classList.remove('active');
            } else {
                document.getElementById('formPelanggan').style.display = 'none';
                document.getElementById('formAdmin').style.display = 'block';
                document.getElementById('tabAdmin').classList.add('active');
                document.getElementById('tabPelanggan').classList.remove('active');
            }
        }

        // Jika ada error reset, otomatis tampilkan form reset
        <?php if (!empty($error_reset) || !empty($sukses_reset)): ?>
        document.getElementById('mainLogin').style.display = 'none';
        document.getElementById('resetAdmin').style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>