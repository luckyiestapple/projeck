<?php
session_start();
require "koneksi.php";

// cek role admin
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$nama_admin = $_SESSION["nama"] ?? "Administrator";

// total pasien
$total_pasien = $konek->query("SELECT COUNT(*) AS jml FROM pasien")->fetch_assoc()["jml"] ?? 0;
// total dokter
$total_dokter = $konek->query("SELECT COUNT(*) AS jml FROM dokter")->fetch_assoc()["jml"] ?? 0;

// antrian hari ini
$today = date('Y-m-d');
$stat = $konek->query("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status='Menunggu' THEN 1 ELSE 0 END) AS menunggu,
        SUM(CASE WHEN status='Selesai' THEN 1 ELSE 0 END) AS selesai
    FROM antrian
    WHERE DATE(waktu_daftar) = '$today'
")->fetch_assoc();

$antrian_hari_ini = $stat["total"] ?? 0;
$antrian_menunggu = $stat["menunggu"] ?? 0;
$selesai_hari_ini  = $stat["selesai"] ?? 0;

// persentase progress
$total_antrian   = $antrian_menunggu + $selesai_hari_ini;
$persen_selesai  = ($total_antrian > 0) ? round($selesai_hari_ini / $total_antrian * 100) : 0;
$persen_menunggu = ($total_antrian > 0) ? round($antrian_menunggu / $total_antrian * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Admin - Klinik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="container-fluid">
    <!-- NAVBAR -->
    <nav class="navbar navbar-light bg-light py-3 border-bottom">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-house me-2"></i> Beranda Admin
            </span>
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-user-shield me-2"></i>
                <span class="me-3"><?= htmlspecialchars($nama_admin) ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="fa-solid fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <!-- RINGKASAN -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Ringkasan Sistem</h4>
            <div class="d-flex gap-2">
                <a href="indexadmin.php" class="btn btn-primary btn-sm active">Overview</a>
                <a href="kelola_dokter.php" class="btn btn-outline-secondary btn-sm">Kelola Dokter</a>
                <a href="kelola_pasien.php" class="btn btn-outline-secondary btn-sm">Kelola Pasien</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="summary-card shadow-sm">
                    <div>
                        <h6>Total Pasien</h6>
                        <h2 class="display-4"><?php echo $total_pasien; ?></h2>
                    </div>
                    <div class="icon-lg text-primary">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="summary-card shadow-sm">
                    <div>
                        <h6>Total Dokter</h6>
                        <h2 class="display-4"><?php echo $total_dokter; ?></h2>
                    </div>
                    <div class="icon-lg text-success">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="summary-card shadow-sm">
                    <div>
                        <h6>Antrian Hari Ini</h6>
                        <h2 class="display-4"><?php echo $antrian_hari_ini; ?></h2>
                    </div>
                    <div class="icon-lg text-warning">
                        <i class="fa-solid fa-list-ol"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- STATISTIK ANTRIAN -->
        <div class="row">
            <div class="col-lg-8 mb-3">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3"><i class="fa-solid fa-chart-pie me-2"></i>Statistik Antrian Hari Ini</h5>

                        <p class="mb-1">Menunggu: <strong><?= $antrian_menunggu; ?></strong> pasien (<?= $persen_menunggu; ?>%)</p>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-info" style="width: <?= $persen_menunggu; ?>%;"></div>
                        </div>

                        <p class="mb-1">Selesai Dilayani: <strong><?= $selesai_hari_ini; ?></strong> pasien (<?= $persen_selesai; ?>%)</p>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: <?= $persen_selesai; ?>%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- panel kecil -->
            <div class="col-lg-4 mb-3">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="card-title">Aksi Cepat</h6>
                        <a href="kelola_dokter.php" class="btn btn-outline-primary btn-sm w-100 mb-2">
                            <i class="fa-solid fa-user-doctor me-1"></i> Kelola Data Dokter
                        </a>
                        <a href="kelola_pasien.php" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fa-solid fa-hospital-user me-1"></i> Lihat Data Pasien
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Catatan</h6>
                        <p class="small text-muted mb-0">
                            Halaman ini menampilkan ringkasan data dari database
                            (pasien, dokter, dan antrian). Gunakan menu di atas untuk
                            mengelola data lebih lanjut.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
