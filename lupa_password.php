<?php
session_start();
require 'Config/database.php';

$step  = 1;
$error = '';
$pelanggan_id = null;

// STEP 1 — Verifikasi no. telepon + tanggal lahir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] == '1') {
    $telepon       = $conn->real_escape_string($_POST['telepon']);
    $tanggal_lahir = $conn->real_escape_string($_POST['tanggal_lahir']);

    $result = $conn->query("SELECT id FROM pelanggan WHERE telepon='$telepon' AND tanggal_lahir='$tanggal_lahir'");

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $pelanggan_id = $row['id'];
        $step = 2;
    } else {
        $error = 'No. telepon atau tanggal lahir tidak cocok!';
        $step  = 1;
    }
}

// STEP 2 — Simpan password baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] == '2') {
    $pelanggan_id = (int)$_POST['pelanggan_id'];
    $new_password = $_POST['new_password'];
    $konfirm      = $_POST['konfirm_password'];

    if (empty($new_password) || empty($konfirm)) {
        $error = 'Password baru wajib diisi!';
        $step  = 2;
    } elseif ($new_password !== $konfirm) {
        $error = 'Password dan konfirmasi tidak sama!';
        $step  = 2;
    } else {
        $pass_md5 = md5($new_password);
        $conn->query("UPDATE pelanggan SET password='$pass_md5' WHERE id=$pelanggan_id");
        // Redirect ke login dengan notif sukses
        header("Location: login.php?reset=berhasil");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — Samudra Gas</title>
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
            padding: 20px;
        }

        .box {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e0e0e0;
        }

        .logo { text-align: center; margin-bottom: 28px; }
        .logo-name { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 800; color: #1a1a1a; }
        .logo-sub { font-size: 11px; color: #0e7c7b; letter-spacing: 1.5px; font-weight: 600; margin-top: 4px; }

        .step-title { font-size: 16px; font-weight: 700; color: #1a1a1a; margin-bottom: 6px; }
        .step-desc  { font-size: 13px; color: #777; margin-bottom: 20px; }

        .label { font-size: 12px; font-weight: 600; color: #555; margin-bottom: 6px; display: block; }

        .inp {
            width: 100%; padding: 12px 14px; border: 1px solid #e0e0e0;
            border-radius: 8px; font-size: 14px; font-family: 'DM Sans', sans-serif;
            background: #f9f9f9; color: #1a1a1a; outline: none;
            margin-bottom: 12px; transition: all 0.2s;
        }

        .inp:focus { border-color: #0e7c7b; background: #fff; box-shadow: 0 0 0 3px rgba(14,124,123,0.1); }

        .btn-submit {
            width: 100%; padding: 13px; background: #0e7c7b; color: #fff;
            border: none; border-radius: 8px; font-size: 15px; font-weight: 600;
            cursor: pointer; margin-top: 6px; transition: 0.2s;
        }

        .btn-submit:hover { background: #0a5f5e; }

        .error { background: #fdecea; color: #c62828; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; border: 1px solid #ffcdd2; }

        .back-link { text-align: center; margin-top: 18px; font-size: 13px; color: #999; }
        .back-link a { color: #0e7c7b; text-decoration: none; font-weight: 500; }

        .step-indicator { display: flex; justify-content: center; gap: 8px; margin-bottom: 24px; }
        .step-dot { width: 8px; height: 8px; border-radius: 50%; background: #e0e0e0; transition: 0.2s; }
        .step-dot.active { background: #0e7c7b; }
    </style>
</head>
<body>
    <div class="box">
        <div class="logo">
            <div class="logo-name">Samudra Gas</div>
            <div class="logo-sub">LUPA PASSWORD</div>
        </div>

        <div class="step-indicator">
            <div class="step-dot <?= $step >= 1 ? 'active' : '' ?>"></div>
            <div class="step-dot <?= $step >= 2 ? 'active' : '' ?>"></div>
        </div>

        <?php if ($error): ?>
            <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($step === 2): ?>
            <!-- STEP 2 — Input password baru -->
            <div class="step-title">Buat Password Baru</div>
            <div class="step-desc">Identitas terverifikasi. Masukkan password baru kamu.</div>

            <form method="POST">
                <input type="hidden" name="step" value="2">
                <input type="hidden" name="pelanggan_id" value="<?= $pelanggan_id ?>">

                <label class="label">PASSWORD BARU</label>
                <input class="inp" type="password" name="new_password" placeholder="Masukkan password baru" required>

                <label class="label">KONFIRMASI PASSWORD</label>
                <input class="inp" type="password" name="konfirm_password" placeholder="Ulangi password baru" required>

                <button class="btn-submit" type="submit">Simpan Password</button>
            </form>

        <?php else: ?>
            <!-- STEP 1 — Verifikasi identitas -->
            <div class="step-title">Verifikasi Identitas</div>
            <div class="step-desc">Masukkan no. telepon dan tanggal lahir yang terdaftar di akun kamu.</div>

            <form method="POST">
                <input type="hidden" name="step" value="1">

                <label class="label">NO. TELEPON</label>
                <input class="inp" type="tel" name="telepon"
                    placeholder="Cth: 08123456789"
                    value="<?= isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : '' ?>" required>

                <label class="label">TANGGAL LAHIR</label>
                <input class="inp" type="date" name="tanggal_lahir"
                    value="<?= isset($_POST['tanggal_lahir']) ? htmlspecialchars($_POST['tanggal_lahir']) : '' ?>" required>

                <button class="btn-submit" type="submit">Verifikasi</button>
            </form>

        <?php endif; ?>

        <div class="back-link">
            <a href="login.php">← Kembali ke login</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>