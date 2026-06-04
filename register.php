<?php
session_start();
require 'Config/database.php';

$error = '';
$sukses = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = $conn->real_escape_string($_POST['nama']);
    $telepon  = $conn->real_escape_string($_POST['telepon']);
    $password = $_POST['password'];
    $konfirm  = $_POST['konfirm'];

    // Validasi
    if (empty($nama) || empty($telepon) || empty($password)) {
        $error = 'Nama, No. Telepon, dan Password wajib diisi!';
    } elseif ($password !== $konfirm) {
        $error = 'Password dan konfirmasi password tidak sama!';
    } else {

        // Cek telepon sudah terdaftar
       $cek = $conn->query("SELECT id FROM pelanggan WHERE telepon='$telepon'");
        if ($cek->num_rows > 0) {
            $error = 'No. Telepon sudah terdaftar!';
        } else {
            $pass_md5 = MD5($password);
           $query = "INSERT INTO pelanggan (nama, telepon, password) 
             VALUES ('$nama','$telepon','$pass_md5')";

            if ($conn->query($query)) {
                $sukses = 'Akun berhasil dibuat! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan sistem, coba lagi.';
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
    <title>Daftar — Samudra Gas</title>
    <!-- Bootstrap CSS -->
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .box {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
        }

        .logo {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo-name {
            font-family: 'Syne', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #1a1a1a;
        }

        .logo-sub {
            font-size: 11px;
            color: #0e7c7b;
            letter-spacing: 1.5px;
            font-weight: 600;
            margin-top: 4px;
        }

        .inp {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            background: #f9f9f9;
            color: #1a1a1a;
            outline: none;
            margin-bottom: 12px;
            transition: all 0.2s;
        }

        .inp:focus {
            border-color: #0e7c7b;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(14, 124, 123, 0.1);
        }

        .label {
            font-size: 12px;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
            display: block;
        }

        .btn-daftar {
            width: 100%;
            padding: 14px;
            background: #0e7c7b;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.2s;
        }

        .btn-daftar:hover {
            background: #0a5f5e;
            transform: translateY(-1px);
        }

        .error {
            background: #fdecea;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            border: 1px solid #ffcdd2;
        }

        .sukses {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            border: 1px solid #c8e6c9;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #0e7c7b;
            text-decoration: none;
            font-weight: 600;
        }

        .note {
            font-size: 11px;
            color: #888;
            margin: -8px 0 12px 0;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 py-4">

    <div class="box">
        <div class="logo">
            <div class="logo-name">Samudra Gas</div>
            <div class="logo-sub">DAFTAR AKUN PELANGGAN</div>
        </div>

        <?php if ($error): ?>
            <div class="error alert">⚠️ <?= $error ?></div>
        <?php endif; ?>

        <?php if ($sukses): ?>
            <div class="sukses alert">
                ✅ <?= $sukses ?><br>
                <a href="login.php" style="color:#2e7d32; text-decoration:underline;">Klik di sini untuk login →</a>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label class="label form-label">Nama Lengkap *</label>
            <input class="inp form-control" type="text" name="nama"
                placeholder="Masukkan nama lengkap"
                value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>" required>

            <label class="label form-label">No. Telepon *</label>
            <input class="inp form-control" type="tel" name="telepon"
                placeholder="Cth: 08123456789"
                value="<?= isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : '' ?>" required>
            <p class="note">Gunakan nomor ini untuk login nantinya.</p>

            <label class="label form-label">Password *</label>
            <input class="inp form-control" type="password" name="password"
                placeholder="Masukkan password" required>

            <label class="label form-label">Konfirmasi Password *</label>
            <input class="inp form-control" type="password" name="konfirm"
                placeholder="Ulangi password" required>

            <button class="btn-daftar btn w-100" type="submit">Buat Akun</button>
        </form>

        <div class="login-link text-center mt-3">
            Sudah punya akun? <a href="login.php" class="text-decoration-none">Login di sini</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>