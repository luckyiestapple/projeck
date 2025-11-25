<?php
require "koneksi.php";

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama          = trim($_POST["nama"] ?? "");
    $email         = trim($_POST["email"] ?? "");
    $password      = $_POST["password"] ?? "";
    $confirm       = $_POST["confirm_password"] ?? "";
    $jenis_kelamin = $_POST["jenis_kelamin"] ?? "";
    $tgl_lahir     = $_POST["tgl_lahir"] ?? "";
    $no_hp         = trim($_POST["no_hp"] ?? "");
    $alamat        = trim($_POST["alamat"] ?? "");

    if ($nama === "" || $email === "" || $password === "") {
        $errors[] = "Nama, Email, dan Password wajib diisi.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    if ($password !== $confirm) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    // cek email sudah ada atau belum
    if (empty($errors)) {
        $cek = $konek->prepare("SELECT id FROM pasien WHERE email = ? LIMIT 1");
        $cek->bind_param("s", $email);
        $cek->execute();
        $result = $cek->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email sudah terdaftar.";
        }
        $cek->close();
    }

    if (empty($errors)) {
        $no_rm = "RM" . time();
        $hash  = password_hash($password, PASSWORD_BCRYPT);

        // 👇 TANPA KELUHAN
        $stmt = $konek->prepare("
            INSERT INTO pasien (no_rm, nama, email, password, jenis_kelamin, tgl_lahir, alamat, no_hp)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssssssss",
            $no_rm,
            $nama,
            $email,
            $hash,
            $jenis_kelamin,
            $tgl_lahir,
            $alamat,
            $no_hp
        );

        if ($stmt->execute()) {
            $success = "Pendaftaran berhasil! Silakan login.";
            $nama = $email = $jenis_kelamin = $tgl_lahir = $no_hp = $alamat = "";
        } else {
            $errors[] = "Terjadi kesalahan saat menyimpan data.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Pasien</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            min-height: 100vh;
            background: radial-gradient(circle at top, #e0f2ff 0, #f4f6fb 45%, #eef1f6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-auth {
            width: 460px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
            padding: 26px 30px 30px;
        }

        .auth-title {
            font-size: 26px;
            font-weight: 700;
            text-align: center;
            color: #111827;
        }

        .auth-subtitle {
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        label {
            font-size: 13px;
            font-weight: 500;
            color: #374151;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border-color: #d1d5db;
            padding: 9px 11px;
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(59,130,246,.25);
            border-color: #2563eb;
        }

        .btn-primary {
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 600;
            background: #2563eb;
            border-color: #2563eb;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .small-text {
            font-size: 13px;
        }

        .error-alert, .success-alert {
            font-size: 13px;
            padding: 8px 10px;
            border-radius: 10px;
        }

        .row-tight { --bs-gutter-x: 0.75rem; }
    </style>
</head>
<body>

<div class="card-auth">

    <h1 class="auth-title">Daftar Pasien</h1>
    <p class="auth-subtitle">Buat akun untuk mengakses antrian dan rekam medis Anda.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger error-alert">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success success-alert">
            <?= htmlspecialchars($success) ?>  
            <a href="login.php" class="alert-link">Login</a>
        </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">

        <div class="mb-2">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" required
                   value="<?= htmlspecialchars($nama ?? "") ?>">
        </div>

        <div class="mb-2">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required
                   value="<?= htmlspecialchars($email ?? "") ?>">
        </div>

        <div class="row row-tight">
            <div class="col-6 mb-2">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-6 mb-2">
                <label>Konfirmasi</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
        </div>

        <div class="row row-tight">
            <div class="col-6 mb-2">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-select">
                    <option value="">- Pilih -</option>
                    <option value="L" <?= (isset($jenis_kelamin) && $jenis_kelamin=="L")?"selected":"" ?>>Laki-laki</option>
                    <option value="P" <?= (isset($jenis_kelamin) && $jenis_kelamin=="P")?"selected":"" ?>>Perempuan</option>
                </select>
            </div>
            <div class="col-6 mb-2">
                <label>Tanggal Lahir</label>
                <input type="date" name="tgl_lahir" class="form-control"
                       value="<?= htmlspecialchars($tgl_lahir ?? "") ?>">
            </div>
        </div>

        <div class="mb-2">
            <label>No HP</label>
            <input type="text" name="no_hp" class="form-control"
                   value="<?= htmlspecialchars($no_hp ?? "") ?>">
        </div>

        <div class="mb-2">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($alamat ?? "") ?></textarea>
        </div>

        <button class="btn btn-primary w-100 mt-2">Daftar</button>

        <p class="mt-3 text-center small-text">
            Sudah punya akun?
            <a href="login.php" class="text-decoration-none">Login di sini</a>
        </p>
    </form>
</div>

</body>
</html>
