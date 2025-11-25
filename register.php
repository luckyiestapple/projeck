<?php
require "koneksi.php";   // ← koneksi ke DB

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
    $keluhan       = trim($_POST["keluhan"] ?? "");

    // Validasi dasar
    if ($nama === "" || $email === "" || $password === "") {
        $errors[] = "Nama, Email, dan Password wajib diisi.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    if ($password !== $confirm) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    // Cek email sudah ada atau belum
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

    // Jika lolos semua
    if (empty($errors)) {

        $no_rm = "RM" . time();              // generate nomor rekam medis unik
        $hash  = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $konek->prepare("
            INSERT INTO pasien (no_rm, nama, email, password, jenis_kelamin, tgl_lahir, alamat, no_hp, keluhan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssssssss",
            $no_rm,
            $nama,
            $email,
            $hash,
            $jenis_kelamin,
            $tgl_lahir,
            $alamat,
            $no_hp,
            $keluhan
        );

        if ($stmt->execute()) {
            $success = "Pendaftaran berhasil! Silakan login.";
            $nama = $email = $jenis_kelamin = $tgl_lahir = $no_hp = $alamat = $keluhan = "";
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

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

<div class="card shadow p-4" style="width: 420px;">
    <h3 class="text-center mb-3">Registrasi Pasien</h3>

    <!-- ERROR MESSAGE -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- SUCCESS -->
    <?php if ($success): ?>
        <div class="alert alert-success py-2">
            <?= $success ?>  
            <a href="login.php" class="alert-link">Login</a>
        </div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-2">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" required
                   value="<?= htmlspecialchars($nama ?? "") ?>">
        </div>

        <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required
                   value="<?= htmlspecialchars($email ?? "") ?>">
        </div>

        <div class="row">
            <div class="col-6 mb-2">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="col-6 mb-2">
                <label class="form-label">Konfirmasi</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-6 mb-2">
                <label class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-select">
                    <option value="">- Pilih -</option>
                    <option value="L" <?= (isset($jenis_kelamin) && $jenis_kelamin=="L")?"selected":"" ?>>Laki-laki</option>
                    <option value="P" <?= (isset($jenis_kelamin) && $jenis_kelamin=="P")?"selected":"" ?>>Perempuan</option>
                </select>
            </div>

            <div class="col-6 mb-2">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" name="tgl_lahir" class="form-control"
                       value="<?= htmlspecialchars($tgl_lahir ?? "") ?>">
            </div>
        </div>

        <div class="mb-2">
            <label class="form-label">No HP</label>
            <input type="text" name="no_hp" class="form-control"
                   value="<?= htmlspecialchars($no_hp ?? "") ?>">
        </div>

        <div class="mb-2">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($alamat ?? "") ?></textarea>
        </div>

        <div class="mb-2">
            <label class="form-label">Keluhan (opsional)</label>
            <textarea name="keluhan" class="form-control" rows="2"><?= htmlspecialchars($keluhan ?? "") ?></textarea>
        </div>

        <button class="btn btn-primary w-100 mt-2">Daftar</button>

        <p class="mt-3 text-center small">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </p>
    </form>
</div>

</body>
</html>
