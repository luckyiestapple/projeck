<?php
session_start();
require "koneksi.php"; // Pastikan file koneksi.php sudah tersedia

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil dan Sanitasi Input
    $nama            = htmlspecialchars($_POST['nama']);
    $email           = htmlspecialchars($_POST['email']);
    $password        = $_POST['password'];
    $alamat          = htmlspecialchars($_POST['alamat']);
    $no_hp           = htmlspecialchars($_POST['no_hp']);
    $tanggal_lahir   = htmlspecialchars($_POST['tgl_lahir']);
    $jenis_kelamin   = htmlspecialchars($_POST['jenis_kelamin']);

    // 2. Validasi Dasar
    if (empty($nama) || empty($email) || empty($password) || empty($alamat) || empty($no_hp)) {
        $error = "Semua kolom wajib diisi!";
    }

    // 3. Cek apakah email sudah terdaftar
    $stmt_email = $konek->prepare("SELECT id FROM pasien WHERE email = ?");
    $stmt_email->bind_param("s", $email);
    $stmt_email->execute();
    $stmt_email->store_result();
    if ($stmt_email->num_rows > 0) {
        $error = "Email sudah terdaftar. Silakan gunakan email lain atau login.";
    }
    $stmt_email->close();

    // 4. GENERASI NOMOR REKAM MEDIS OTOMATIS (FIX UNTUK DUPLICATE ENTRY)
    if (empty($error)) {
        $no_rm = '';
        
        // Ambil nomor RM terbesar saat ini
        $q = $konek->query("SELECT no_rm FROM pasien ORDER BY id DESC LIMIT 1");
        $lastRM = $q->fetch_assoc();

        if ($lastRM) {
            // Asumsi format: RM[angka]
            $lastNumber = (int) substr($lastRM['no_rm'], 2); // Ambil angka setelah 'RM'
            $newNumber = $lastNumber + 1;
        } else {
            // Jika data kosong, mulai dari 1
            $newNumber = 1;
        }

        // Format angka menjadi 4 digit (misal: 1 -> 0001, 1765 -> 1765)
        $formattedNumber = str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        $no_rm = 'RM' . $formattedNumber;

        // 5. Hash Password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 6. Masukkan Data ke Database
        $stmt_insert = $konek->prepare("
            INSERT INTO pasien (no_rm, nama, email, password, alamat, no_hp, tgl_lahir, jenis_kelamin) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_insert->bind_param("ssssssss", 
            $no_rm, $nama, $email, $hashed_password, $alamat, $no_hp, $tanggal_lahir, $jenis_kelamin
        );

        if ($stmt_insert->execute()) {
            // 7. Registrasi Sukses, Otomatis Login
            $_SESSION["id_pasien"] = $konek->insert_id;
            $_SESSION["nama"]      = $nama;
            $_SESSION["role"]      = "pasien";
            header("Location: dashboard_pasien.php");
            exit;
        } else {
            $error = "Pendaftaran gagal. Silakan coba lagi. (" . $konek->error . ")";
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
        body { background: linear-gradient(to right, #e0f7fa, #ffffff); }
        .register-box { max-width: 500px; margin: 50px auto; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); background: white; }
    </style>
</head>
<body>

<div class="register-box">
    <h3 class="text-center text-primary mb-4"><i class="fas fa-user-plus me-2"></i> Daftar Akun Pasien</h3>

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
    
    <div class="text-center mt-3">
        Sudah punya akun? <a href="login.php">Login di sini</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>