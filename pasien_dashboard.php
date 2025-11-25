<?php
// ========== pasien_dashboard.php (VERSI PERBAIKAN AKHIR) ==========

session_start();
$error = "";

// 1. Konfigurasi Database (Pastikan SAMA dengan db.php/register.php/signin.php)
$DB_HOST = 'localhost';
$DB_NAME = 'rumahsakit';
$DB_USER = 'root';
$DB_PASS = '';

try {
  $pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (PDOException $e) {
  // Jika koneksi DB gagal, hentikan eksekusi
  die("DB Error: Koneksi database gagal: " . $e->getMessage());
}

// 2. Cek Sesi Pasien
if (!isset($_SESSION['pasien_id'])) {
    // Jika belum login, alihkan ke halaman login
    header("Location: signin.php"); 
    exit();
}

// ID pasien yang sedang login (Ini yang menyebabkan error di baris 12 sebelumnya)
$pasienId = (int) $_SESSION['pasien_id'];

// 3. Ambil Data Pasien
// Pastikan semua nama kolom di sini SAMA dengan nama kolom di tabel 'pasien' Anda.
// Kolom 'id' harus ada di tabel pasien sebagai Primary Key.
$st = $pdo->prepare("
    SELECT 
        id, nama, no_rm, email, jenis_kelamin, tgl_lahir, alamat, no_hp 
    FROM 
        pasien 
    WHERE 
        id = ?
");
// Baris 12 mungkin berada pada $st->execute([$pasienId]); atau baris setelahnya.
if (!$st->execute([$pasienId])) {
    die("Error mengambil data pasien.");
}
$dataPasien = $st->fetch();

if (!$dataPasien) {
    // Jika data pasien tidak ditemukan (mungkin data sudah terhapus)
    session_destroy();
    header("Location: signin.php?error=Akun tidak ditemukan. Silakan login ulang.");
    exit();
}

// Definisikan variabel untuk memudahkan pemanggilan di HTML
$nama = $dataPasien['nama'];
$noRm = $dataPasien['no_rm'];
// ... dan seterusnya

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pasien</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Gaya dasar untuk dashboard pasien */
        body { font-family: 'Poppins', sans-serif; background: #f4f9ff; padding: 20px; }
        .card-pasien { 
            background: white; padding: 30px; border-radius: 12px; 
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1); max-width: 600px; 
            margin: 0 auto;
        }
        h1 { color: #0052cc; margin-bottom: 20px; font-size: 1.8rem; }
        .info-item { 
            margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; 
            display: flex; justify-content: space-between;
        }
        .info-item strong { color: #333; font-weight: 600; }
        .info-item span { color: #555; }
        .logout-btn { 
            display: inline-block; background: #dc3545; color: white; 
            padding: 10px 15px; border: none; border-radius: 8px; 
            cursor: pointer; margin-top: 20px; text-decoration: none;
            font-weight: 500;
        }
        .logout-btn:hover { background: #c82333; }
    </style>
</head>
<body>

    <div class="card-pasien">
        <h1>Dashboard Pasien</h1>
        <p style="margin-bottom: 30px;">Selamat datang, **<?= htmlspecialchars($nama) ?>**! Ini adalah data profil Anda.</p>

        <div class="info-item">
            <strong>ID Pasien:</strong> <span><?= htmlspecialchars($dataPasien['id']) ?></span>
        </div>
        <div class="info-item">
            <strong>No. Rekam Medis (RM):</strong> <span><?= htmlspecialchars($noRm) ?></span>
        </div>
        <div class="info-item">
            <strong>Nama Lengkap:</strong> <span><?= htmlspecialchars($nama) ?></span>
        </div>
        <div class="info-item">
            <strong>Email:</strong> <span><?= htmlspecialchars($dataPasien['email']) ?></span>
        </div>
        <div class="info-item">
            <strong>Tanggal Lahir:</strong> <span><?= htmlspecialchars($dataPasien['tgl_lahir']) ?></span>
        </div>
        <div class="info-item">
            <strong>Jenis Kelamin:</strong> <span><?= htmlspecialchars($dataPasien['jenis_kelamin']) ?></span>
        </div>
        <div class="info-item">
            <strong>No. HP:</strong> <span><?= htmlspecialchars($dataPasien['no_hp']) ?></span>
        </div>
        <div class="info-item" style="border-bottom: none; display: block;">
            <strong>Alamat:</strong> <span><?= nl2br(htmlspecialchars($dataPasien['alamat'])) ?></span>
        </div>

        <a href="logout.php" class="logout-btn">Logout <i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
    
</body>
</html>