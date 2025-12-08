<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$nama_admin = $_SESSION["nama"] ?? "Administrator";

$total_pasien = $konek->query("SELECT COUNT(*) AS jml FROM pasien")->fetch_assoc()["jml"] ?? 0;
$total_dokter = $konek->query("SELECT COUNT(*) AS jml FROM dokter")->fetch_assoc()["jml"] ?? 0;

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

    <style>
    body {
        background: linear-gradient(to bottom, #eef5ff, #f7faff);
        font-family: "Inter", sans-serif;
    }

    /* NAVBAR */
    .navbar-custom {
        background: linear-gradient(90deg, #dce9ff, #c8dbff);
        border-bottom: 1px solid #cfdcff;
    }

    .navbar-custom .navbar-brand,
    .navbar-custom span,
    .navbar-custom i {
        color: #2c3e50 !important;
        font-weight: 600;
    }

    /* SUMMARY CARD */
    .summary-card {
        background: #f3f7ff;
        padding: 25px;
        border-radius: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #d9e6ff;
        transition: 0.2s;
    }

    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0px 10px 20px rgba(170, 190, 255, 0.35);
    }

    .summary-card h6 {
        color: #38507a;
        font-weight: 600;
    }

    .summary-card .display-5 {
        color: #20325c;
        font-weight: 700;
    }

    /* ICON */
    .icon-lg {
        font-size: 40px;
        padding: 16px;
        border-radius: 50%;
        background: #e3ecff;
        color: #3b6fdc;
    }

    /* MAIN CARD */
    .card {
        border-radius: 18px;
        border: 1px solid #d7e4ff;
        background: #f5f8ff !important;
        box-shadow: 0 6px 16px rgba(180, 200, 255, 0.25);
    }

    .card-title {
        color: #72d4feff;
        font-weight: 600;
    }

    /* PROGRESS BAR */
    .progress {
        height: 11px;
        border-radius: 20px;
        background: #dce6ff;
    }

    .progress-bar.bg-info {
        background: #7da8ff !important;
    }

    .progress-bar.bg-success {
        background: #6fd7a6 !important;
    }

    /* Quick Buttons */
    .quick-btn {
        border-radius: 15px;
        padding: 12px;
        font-weight: 600;
        border: 1px solid #bcd0ff;
        background: #e8efff;
        color: #2e4b88;
    }

    .quick-btn:hover {
        background: #d9e6ff;
    }

    .btn-outline-secondary {
        background: #eef3ff !important;
        border-color: #c9d7ff !important;
        color: #394b78 !important;
    }

    .btn-outline-secondary:hover {
        background: #dce8ff !important;
    }

    /* CATATAN CARD */
    .card-note {
        background: #f1f5ff !important;
        border-color: #d6e2ff;
    }

    .card-note p {
        color: #51648e;
    }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-custom py-3 shadow-sm">
    <div class="container-fluid d-flex justify-content-between">
        <span class="navbar-brand mb-0 h1">
            <i class="fa-solid fa-house me-2"></i> Beranda Admin
        </span>

        <div class="d-flex align-items-center">
            <i class="fa-solid fa-user-shield me-2"></i>
            <span class="me-3"><?= htmlspecialchars($nama_admin) ?></span>
            <a href="logout.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container my-4">

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="summary-card shadow-sm">
                <div>
                    <h6>Total Pasien</h6>
                    <h2 class="display-5"><?= $total_pasien ?></h2>
                </div>
                <div class="icon-lg text-primary bg-light">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="summary-card shadow-sm">
                <div>
                    <h6>Total Dokter</h6>
                    <h2 class="display-5"><?= $total_dokter ?></h2>
                </div>
                <div class="icon-lg text-success bg-light">
                    <i class="fa-solid fa-user-doctor"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="summary-card shadow-sm">
                <div>
                    <h6>Antrian Hari Ini</h6>
                    <h2 class="display-5"><?= $antrian_hari_ini ?></h2>
                </div>
                <div class="icon-lg text-warning bg-light">
                    <i class="fa-solid fa-list-ol"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fa-solid fa-chart-pie me-2"></i> Statistik Antrian Hari Ini</h5>

                    <p>Menunggu: <strong><?= $antrian_menunggu ?></strong> pasien (<?= $persen_menunggu ?>%)</p>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-info" style="width: <?= $persen_menunggu ?>%"></div>
                    </div>

                    <p>Selesai: <strong><?= $selesai_hari_ini ?></strong> pasien (<?= $persen_selesai ?>%)</p>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: <?= $persen_selesai ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SIDE PANEL -->
        <div class="col-lg-4 mb-3">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="card-title">Aksi Cepat</h6>

                    <a href="kelola_dokter.php" class="btn btn-outline-primary quick-btn w-100 mb-2">
                        <i class="fa-solid fa-user-doctor"></i> Kelola Data Dokter
                    </a>

                    <a href="kelola_pasien.php" class="btn btn-outline-secondary quick-btn w-100">
                        <i class="fa-solid fa-hospital-user"></i> Lihat Data Pasien
                    </a>
                </div>
            </div>

            <div class="card shadow-sm card-note">
                <div class="card-body">
                    <h6 class="card-title">Catatan</h6>
                    <p class="small text-muted">
                        Halaman ini menampilkan ringkasan data dari database (pasien, 
                        dokter, dan antrian). Gunakan menu untuk mengelola data lebih lanjut.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 