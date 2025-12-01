<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "dokter") {
    header("Location: login.php");
    exit;
}

$id_dokter = $_SESSION["id_dokter"];

// Ambil rekam medis
$rekam = $konek->query("
    SELECT rm.*, p.nama AS nama_pasien
    FROM rekam_medis rm
    JOIN pasien p ON p.id = rm.id_pasien
    WHERE rm.id_dokter = $id_dokter
    ORDER BY rm.tanggal_periksa DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekam Medis</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="dashboard_dokter.php">Panel Dokter</a>
    <div class="ms-auto">
        <a href="dashboard_dokter.php" class="btn btn-sm btn-outline-light">
            <i class="fas fa-sign-out-alt me-1"></i> Keluar
        </a>

   
</nav>

<div class="container py-4">
    <h3>Rekam Medis</h3>

   

    <table class="table table-bordered">
        <thead class="table-success">
            <tr>
                <th>Tanggal</th>
                <th>Nama Pasien</th>
                <th>Diagnosis</th>
                <th>Catatan</th>
            </tr>
        </thead>

        <tbody>
            <?php while ($row = $rekam->fetch_assoc()): ?>
            <tr>
                <td><?= $row["tanggal_periksa"] ?></td>
                <td><?= htmlspecialchars($row["nama_pasien"]) ?></td>
                <td><?= htmlspecialchars($row["diagnosis"]) ?></td>
                <td><?= nl2br(htmlspecialchars($row["catatan"])) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>

    </table>

</div>

</body>
</html>
