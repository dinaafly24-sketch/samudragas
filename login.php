<?php
session_start();
require 'Config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role     = $_POST['role'] ?? 'pelanggan';
    $input    = $_POST['telepon'] ?? '';
    $password = $_POST['password'] ?? '';
    $pass_md5 = md5($password);

    // VALIDASI INPUT KOSONG
    if (empty($input) || empty($password)) {
        $error = 'No telepon dan password wajib diisi!';
    } elseif ($role === 'admin') {
        // VALIDASI ADMIN HARUS PAKAI "adm" DI AKHIR
        if (substr($input, -3) !== 'adm') {
            $error = 'Login admin harus pakai "adm" di akhir nomor!';
        } else {
            $telepon = $conn->real_escape_string(substr($input, 0, -3));
        }
    } else {
        // Pelanggan biasa
        $telepon = $conn->real_escape_string($input);
    }

    // JALANKAN QUERY JIKA TIDAK ADA ERROR
    if (empty($error)) {
        if ($role === 'admin') {
            // Query ke tabel USERS (khusus admin)
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
            // Query ke tabel PELANGGAN
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
        }

        .login-box {
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

        .logo-icon {
            font-size: 40px;
        }

        .logo-name {
            font-family: 'Syne', sans-serif;
            font-size: 22px;
            font-weight: 800;
            color: #1a1a1a;
            margin-top: 8px;
        }

        .logo-sub {
            font-size: 12px;
            color: #0e7c7b;
            letter-spacing: 1px;
        }

        .tab-switch {
            display: flex;
            background: #f0f2f5;
            border-radius: 10px;
            padding: 4px;
            margin-bottom: 24px;
        }

        .tab-btn {
            flex: 1;
            padding: 10px;
            border: none;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            font-size: 13.5px;
            font-weight: 500;
            color: #999;
            transition: all 0.2s;
        }

        .tab-btn.active {
            background: #fff;
            color: #0e7c7b;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
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
            transition: border 0.2s;
        }

        .inp:focus {
            border-color: #0e7c7b;
            background: #fff;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: #0e7c7b;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            cursor: pointer;
            margin-top: 4px;
            transition: background 0.2s;
        }

        .btn-login:hover {
            background: #0a5f5e;
        }

        .error {
            background: #fdecea;
            color: #c62828;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 14px;
            border: 1px solid #ffcdd2;
        }

        .daftar {
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            color: #999;
        }

        .daftar a {
            color: #0e7c7b;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="login-box">
        <div class="logo">
            <div class="logo-name">Samudra Gas</div>
            <div class="logo-sub">SISTEM INFORMASI PELANGGAN</div>
        </div>

        <div class="tab-switch">
            <button class="tab-btn active" id="tabPelanggan" type="button"
                onclick="switchTab('pelanggan')">Pelanggan</button>
            <button class="tab-btn" id="tabAdmin" type="button"
                onclick="switchTab('admin')">Admin</button>
        </div>

        <?php if ($error): ?>
            <div class="error alert"><?= $error ?></div>
        <?php endif; ?>

        <!-- FORM PELANGGAN -->
        <form method="POST" id="formPelanggan">
            <input type="hidden" name="role" value="pelanggan">
            <input class="inp form-control" type="tel" name="telepon"
                placeholder="No. Telepon (cth: 08579)">
            <input class="inp form-control" type="password" name="password"
                placeholder="Password">
            <button class="btn-login btn w-100" type="submit">Masuk</button>
        </form>

        <!-- FORM ADMIN -->
        <form method="POST" id="formAdmin" style="display:none;">
            <input type="hidden" name="role" value="admin">
          <input class="inp form-control" type="text" name="telepon" inputmode="text" autocapitalize="none"
                placeholder="No. Telp(tambahkan 'adm')">
            <input class="inp form-control" type="password" name="password"
                placeholder="Password">
            <button class="btn-login btn w-100" type="submit">Masuk</button>
        </form>

        <div class="daftar text-center mt-3">
            Belum punya akun? <a href="register.php" class="text-decoration-none">Daftar di sini</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    </script>
</body>

</html>