<?php
// ========== header.php (DENGAN PROTEKSI LOGIN) ==========

// 1) Koneksi database (menggunakan PDO)
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
  // Hanya tampilkan error DB jika bukan di halaman login
  if (basename($_SERVER['PHP_SELF']) !== 'signin.php') {
      die("DB Error: " . $e->getMessage());
  }
}

session_start();

// !!! KRITIS: CEK PROTEKSI LOGIN !!!
if (!isset($_SESSION['dokter_id'])) {
    // Jika TIDAK ADA sesi dokter, arahkan ke halaman signin
    header("Location: signin.php"); 
    exit;
}
$dokterId = (int) $_SESSION['dokter_id'];

// 2) Ambil data dokter (untuk header)
$cekDok = $pdo->prepare("SELECT id,nama FROM dokter WHERE id=?");
$cekDok->execute([$dokterId]);
$dokter = $cekDok->fetch();

// JIKA ID di sesi TIDAK VALID, paksa logout
if (!$dokter) {
  session_destroy();
  header("Location: signin.php");
  exit;
}

// 3) Cek status Check-in (Lanjutan kode header)
$today = date('Y-m-d');
$st_checkin = $pdo->prepare("SELECT id FROM doctor_attendance WHERE doctor_id=? AND check_in_date=?");
$st_checkin->execute([$dokterId, $today]);
$sudahCheckin = (bool) $st_checkin->fetch();

$active = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Dokter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
  </head>
<body>

<header class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm fixed-top">
  <div class="container-fluid container-xl">
    <a class="navbar-brand text-primary fw-bold d-flex align-items-center" href="data-dokter.php">
      <span class="material-icons-sharp me-2">local_hospital</span>
      Sistem RS
    </a>

    <div class="d-flex align-items-center">
      <span class="user-name me-3 text-dark fw-bold">
        <?= htmlspecialchars($dokter['nama']) ?>
      </span>
      <a href="logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
    </div>
  </div>
</header>

<main class="container-xl pt-5 mt-4">

  <section class="card shadow-sm p-3 mb-4
    <?= $sudahCheckin ? 'bg-success-subtle border-success' : 'bg-warning-subtle border-warning' ?>">
    <div class="d-flex justify-content-between align-items-center">
      <div class="banner-text">
        <strong class="fs-5">
            <?= $sudahCheckin ? 'Sedang Praktik' : 'Belum Check-in' ?>
        </strong>
        <p class="mb-0 text-muted small">
            <?= $sudahCheckin ? 'Tekan "Selesai Praktik" untuk membuat rekap harian.' : 'Silakan check-in untuk memulai praktik hari ini' ?>
        </p>
      </div>

      <?php if (!$sudahCheckin): ?>
        <form method="post" action="checkin.php">
          <button class="btn btn-primary">Check-in</button>
        </form>
      <?php else: ?>
        <a href="checkout.php" class="btn btn-danger">Selesai Praktik</a> 
      <?php endif; ?>
    </div>
  </section>

  <ul class="nav nav-tabs mb-4">
    <li class="nav-item">
      <a href="data-dokter.php" class="nav-link <?= $active==='data-dokter.php' ? 'active' : '' ?>">Data Dokter</a>
    </li>
    <li class="nav-item">
      <a href="jadwal-dokter.php" class="nav-link <?= $active==='jadwal-dokter.php' ? 'active' : '' ?>">Jadwal</a>
    </li>
    <li class="nav-item">
      <a href="antrian-pasien.php" class="nav-link <?= $active==='antrian-pasien.php' ? 'active' : '' ?>">Antrian Pasien</a>
    </li>
    <li class="nav-item">
      <a href="review-rekap.php" class="nav-link <?= $active==='review-rekap.php' ? 'active' : '' ?>">Review/Rekap</a>
    </li>
  </ul>