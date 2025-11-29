<?php
/**
 * File: jadwal_praktek_dokter.php
 * Digunakan oleh Dokter untuk melihat Jadwal Prakteknya sendiri.
 */
session_start();
require "koneksi.php"; // Pastikan koneksi.php sudah benar

// Otorisasi: Pastikan hanya Dokter yang bisa mengakses
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "dokter") {
    header("Location: login.php");
    exit;
}

$id_dokter = $_SESSION["id_dokter"]; // ID Dokter yang sedang login
$nama_dokter = $_SESSION["nama"] ?? "Dokter"; 

// Query untuk mengambil jadwal praktek dokter yang sedang login
// Menggabungkan (JOIN) tabel jadwal_dokter dengan tabel poli untuk mendapatkan nama poli
$sqlJadwal = $konek->query("
    SELECT 
        j.hari, 
        j.jam_mulai, 
        j.jam_selesai, 
        p.nama_poli AS poli_name
    FROM jadwal_dokter j
    JOIN poli p ON p.id = j.poli_id
    WHERE j.dokter_id = $id_dokter
    ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Praktek Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background: #f4f7f6; }
        .card-box { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 15px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .table thead th { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="dashboard_dokter.php">
        <i class="fas fa-stethoscope me-2"></i> Dokter Panel
    </a>
    <div class="ms-auto text-light">
        Halo, **<?= htmlspecialchars($nama_dokter) ?>**
        <a href="logout.php" class="btn btn-sm btn-outline-light ms-2">Logout</a>
    </div>
</nav>

<div class="card-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="far fa-calendar-alt me-2"></i> Jadwal Praktek Saya</h3>
        <a href="dashboard_dokter.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <p class="text-muted">Berikut adalah daftar jadwal praktek Anda di klinik.</p>

    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>Hari</th>
                <th>Poli</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($sqlJadwal->num_rows > 0): ?>
                <?php while ($row = $sqlJadwal->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["hari"]) ?></td>
                    <td><?= htmlspecialchars($row["poli_name"]) ?></td>
                    <td><?= htmlspecialchars($row["jam_mulai"]) ?></td>
                    <td><?= htmlspecialchars($row["jam_selesai"]) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-danger p-3">
                        <i class="fas fa-exclamation-triangle me-1"></i> Belum ada jadwal praktek yang terdaftar untuk Anda.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>