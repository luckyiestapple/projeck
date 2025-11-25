<?php
// ========== pasien_header.php (Bootstrap Version) ==========

// 1) Konfigurasi Database (Gunakan db.php jika sudah ada, atau hardcode jika belum)
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
  die("DB Error: " . $e->getMessage());
}

session_start();

// !!! PROTEKSI LOGIN PASIEN !!!
if (!isset($_SESSION['pasien_id'])) {
    // Arahkan ke halaman login jika sesi tidak ada
    header("Location: signin.php"); 
    exit;
}
$pasienId = (int) $_SESSION['pasien_id'];

// Ambil data pasien yang sedang login
$cekPasien = $pdo->prepare("SELECT id, nama, no_rm, email FROM pasien WHERE id=?");
$cekPasien->execute([$pasienId]);
$pasien = $cekPasien->fetch();

if (!$pasien) {
  session_destroy();
  header("Location: signin.php?msg=AkunPasienTidakValid");
  exit;
}

$active = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Pasien</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <link rel="stylesheet" href="style.css"> 

  <style>
    /* Tambahkan sedikit style kustom agar mirip dengan layout Dokter */
    body { background-color: #f4f9ff; color: #333; }
    .top-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 2.5rem; background-color: #ffffff; border-bottom: 1px solid #e0e0e0; }
    .logo { display: flex; align-items: center; color: #0052cc; }
    .logo h1 { font-size: 1.1rem; font-weight: 600; margin-left: 0.5rem; }
    .user-menu .user-name { font-weight: 600; margin-right: 15px; color: #333; }
    
    .container-custom { max-width: 1200px; margin: 20px auto; padding: 0 15px; }
    
    /* Style untuk Card Bootstrap agar terlihat seperti di halaman Dokter */
    .card { border: none; border-radius: 10px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); }
    .card-header-custom { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 1rem 1.25rem; border-radius: 10px 10px 0 0; display: flex; align-items: center; color: #0052cc; }
    .card-header-custom h2 { font-size: 1.25rem; font-weight: 600; margin-left: 10px; margin-bottom: 0; }
  </style>
</head>
<body>

<header class="top-header">
  <div class="logo">
    <i class="fa-solid fa-hospital fa-lg"></i>
    <h1>Sistem Pasien RS</h1>
  </div>

  <div class="user-menu d-flex align-items-center">
    <span class="user-name">Pasien: <?= htmlspecialchars($pasien['nama'] ?? 'Pasien') ?></span>
    <a href="logout.php" class="btn btn-sm btn-outline-primary">Logout</a>
  </div>
</header>

<main class="container-custom">
  <?php if (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
    <div class="alert alert-danger" role="alert">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($_GET['msg'] ?? 'Terjadi kesalahan.') ?>
    </div>
  <?php endif; ?>