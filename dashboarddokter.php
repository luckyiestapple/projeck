<?php
session_start();
require "koneksi.php"; // koneksi ke database

// CEK LOGIN & ROLE
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "dokter") {
    header("Location: login.php");
    exit;
}

// Ambil data dokter yang sedang login
$id_dokter = $_SESSION["id_dokter"];

$sqlDokter = $konek->query("SELECT * FROM dokter WHERE id = $id_dokter");
$dokter = $sqlDokter->fetch_assoc();

// Hitung antrian hari ini
$today = date('Y-m-d');

$sqlTotalAntrian = $konek->query("
    SELECT COUNT(*) AS jml 
    FROM antrian 
    WHERE dokter_id = $id_dokter 
      AND DATE(waktu_daftar) = '$today'
");
$totalAntrian = $sqlTotalAntrian->fetch_assoc()["jml"] ?? 0;

// Hitung yang sudah selesai hari ini
$sqlSelesai = $konek->query("
    SELECT COUNT(*) AS jml 
    FROM antrian 
    WHERE dokter_id = $id_dokter 
      AND DATE(waktu_daftar) = '$today'
      AND status = 'Selesai'
");
$totalSelesai = $sqlSelesai->fetch_assoc()["jml"] ?? 0;

// Ambil 5 antrian terbaru
$sqlListAntrian = $konek->query("
    SELECT a.*, p.nama AS nama_pasien, pl.nama_poli
    FROM antrian a
    LEFT JOIN pasien p ON p.id = a.pasien_id
    LEFT JOIN poli pl ON pl.id = a.poli_id
    WHERE a.dokter_id = $id_dokter
    ORDER BY a.status = 'Menunggu' DESC, a.waktu_daftar ASC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Dokter</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background-color: #f5f5f5; }
        .page-title { font-weight: 600; }
    </style>
</head>
<body>

<!-- NAVBAR DOKTER -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="dashboard_dokter.php">Panel Dokter</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navDokter">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div id="navDokter" class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link active" href="dashboard_dokter.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="antrian.php">Antrian</a></li>
            <li class="nav-item"><a class="nav-link" href="rekam.php">Rekam Medis</a></li>
            <li class="nav-item"><a class="nav-link" href="jadwal.php">Jadwal</a></li>
            <li class="nav-item"><a class="nav-link text-warning" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container py-4">

    <!-- Header -->
    <div class="mb-4">
        <h3 class="page-title">Dashboard Dokter</h3>
        <p class="text-muted mb-0">
            Selamat datang, <strong><?= htmlspecialchars($dokter["nama"]) ?></strong><br>
            Spesialis: <?= htmlspecialchars($dokter["spesialisasi"] ?? '-') ?> |
            Poli: <?= htmlspecialchars($dokter["poli"] ?? '-') ?>
        </p>
    </div>

    <!-- Kartu Ringkasan -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill text-primary fs-1"></i>
                    <h5 class="mt-2 mb-1">Antrian Hari Ini</h5>
                    <h2 class="mb-0"><?= $totalAntrian ?></h2>
                    <small class="text-muted">Total pasien dalam antrian</small><br>
                    <a href="antrian.php" class="btn btn-sm btn-primary mt-2">Lihat Antrian</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle-fill text-success fs-1"></i>
                    <h5 class="mt-2 mb-1">Pasien Selesai</h5>
                    <h2 class="mb-0"><?= $totalSelesai ?></h2>
                    <small class="text-muted">Pasien yang sudah ditangani hari ini</small>
                </div>
            </div>
        </div>

        <!-- Slot kartu lain, misalnya jadwal -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="bi bi-calendar2-week text-warning fs-1"></i>
                    <h5 class="mt-2 mb-1">Jadwal Praktek</h5>
                    <p class="mb-0 text-muted">Lihat jadwal praktek Anda.</p>
                    <a href="jadwal.php" class="btn btn-sm btn-warning mt-2">Lihat Jadwal</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Antrian Singkat -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            Antrian Terbaru
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No Antrian</th>
                            <th>Nama Pasien</th>
                            <th>Poli</th>
                            <th>Status</th>
                            <th>Waktu Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($sqlListAntrian->num_rows > 0): ?>
                        <?php while ($row = $sqlListAntrian->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row["nomor"] ?></td>
                                <td><?= htmlspecialchars($row["nama_pasien"] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row["nama_poli"] ?? '-') ?></td>
                                <td>
                                    <?php if ($row["status"] == "Menunggu"): ?>
                                        <span class="badge bg-secondary">Menunggu</span>
                                    <?php elseif ($row["status"] == "Dipanggil"): ?>
                                        <span class="badge bg-warning text-dark">Dipanggil</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Selesai</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $row["waktu_daftar"] ?></td>
                                <td>
                                    <?php if ($row["status"] == "Menunggu"): ?>
                                        <a href="update-status.php?id=<?= $row['id'] ?>&s=Dipanggil" class="btn btn-sm btn-warning">Panggil</a>
                                    <?php elseif ($row["status"] == "Dipanggil"): ?>
                                        <a href="update-status.php?id=<?= $row['id'] ?>&s=Selesai" class="btn btn-sm btn-success">Selesai</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">Belum ada antrian untuk Anda.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
