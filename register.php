<?php
session_start();
require "koneksi.php";

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama          = trim($_POST['nama']);
    $email         = trim($_POST['email']);
    $password      = $_POST['password'];
    $alamat        = trim($_POST['alamat']);
    $no_hp         = trim($_POST['no_hp']);
    $tanggal_lahir = trim($_POST['tanggal_lahir']);
    $jenis_kelamin = trim($_POST['jenis_kelamin']);  // pastikan DB menerima string ini

    if (empty($nama) || empty($email) || empty($password) || empty($alamat) || empty($no_hp)) {
        $error = "Semua kolom wajib diisi!";
    }

    if (!$error) {
        $stmt_email = $konek->prepare("SELECT id FROM pasien WHERE email = ?");
        $stmt_email->bind_param("s", $email);
        $stmt_email->execute();
        $stmt_email->store_result();

        if ($stmt_email->num_rows > 0) {
            $error = "Email sudah terdaftar.";
        }

        $stmt_email->close();
    }

    if (!$error) {

        // Generate RM berurutan berdasarkan ID terakhir
        $getLast = $konek->query("SELECT id FROM pasien ORDER BY id DESC LIMIT 1");
        $lastID  = $getLast->fetch_assoc();
        $nextID  = $lastID ? $lastID["id"] + 1 : 1;

        $no_rm = "RM-" . str_pad($nextID, 3, "0", STR_PAD_LEFT);

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt_insert = $konek->prepare("
            INSERT INTO pasien (no_rm, nama, email, password, alamat, no_hp, tgl_lahir, jenis_kelamin)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt_insert->bind_param(
            "ssssssss",
            $no_rm,
            $nama,
            $email,
            $hashed_password,
            $alamat,
            $no_hp,
            $tanggal_lahir,
            $jenis_kelamin
        );

        if ($stmt_insert->execute()) {

            $_SESSION["id_pasien"] = $konek->insert_id;
            $_SESSION["nama"]      = $nama;
            $_SESSION["role"]      = "pasien";

            header("Location: dashboard_pasien.php");
            exit;

        } else {
            $error = "Pendaftaran gagal: " . $stmt_insert->error;
        }

        $stmt_insert->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun Pasien</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
       * {
    font-family: 'Inter', sans-serif;
}

body {
    min-height: 100vh;
    background: linear-gradient(135deg, #c7ddff, #eef2ff);
    display: flex;
    align-items: center;
    justify-content: center;
}

.register-box {
    width: 100%;
    max-width: 500px;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(14px);
    padding: 35px 32px;
    border-radius: 20px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.15);
    animation: fadeIn .6s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.register-title {
    font-size: 26px;
    font-weight: 700;
    text-align: center;
    color: #1e293b;
    margin-bottom: 6px;
}

.title-line {
    height: 4px;
    width: 60px;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    border-radius: 99px;
    margin: 0 auto 22px;
}

label {
    font-size: 14px;
    font-weight: 600;
    color: #334155;
}

.form-control,
.form-select {
    border-radius: 12px;
    border: 1px solid #cbd5f5;
    padding: 11px 14px;
    transition: .3s ease;
}

.form-control:focus,
.form-select:focus {
    box-shadow: 0 0 0 4px rgba(59,130,246,.25);
    border-color: #2563eb;
}

.btn-primary {
    border-radius: 999px;
    padding: 12px 16px;
    font-weight: 600;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    border: none;
    font-size: 16px;
    box-shadow: 0 8px 20px rgba(37,99,235,.35);
    transition: .3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(37,99,235,.45);
}

.alert {
    border-radius: 12px;
    font-size: 13px;
    text-align: center;
}

.login-link {
    font-size: 14px;
    color: #475569;
}

.login-link a {
    font-weight: 600;
    color: #2563eb;
    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}
 </style>
</head>
<body>

<div class="register-box">
   <h3 class="register-title">
    <i class="fas fa-user-plus me-2 text-primary"></i> Daftar Akun Pasien
</h3>
<div class="title-line"></div>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" id="nama" name="nama" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="no_hp" class="form-label">No. HP</label>
                <input type="text" class="form-control" id="no_hp" name="no_hp" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control" id="tgl_lahir" name="tanggal_lahir" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                <option value="">Pilih...</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 mt-2">Daftar</button>
    </form>
    
   <div class="text-center mt-4 login-link">
    Sudah punya akun? <a href="login.php">Login di sini</a>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>