<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "pasien") {
    header("Location: login.php");
    exit;
}

$id_pasien = $_SESSION["id_pasien"];

$data = $konek->query("
    SELECT rm.*, d.nama AS nama_dokter
    FROM rekam_medis rm
    JOIN dokter d ON d.id = rm.id_dokter
    WHERE rm.id_pasien = $id_pasien
    ORDER BY rm.tanggal_periksa DESC
");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Rekam Medis Saya</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success px-4">
    <a class="navbar-brand" href="dashboard_pasien.php">Pasien Panel</a>
</nav>

<div class="container py-4">
    <h3>Rekam Medis Saya</h3>

    <table class="table table-bordered mt-3">
        <thead class="table-success">
            <tr>
                <th>Tanggal</th>
                <th>Dokter</th>
                <th>Diagnosis</th>
                <th>Catatan</th>
            </tr>
        </thead>

        <tbody>
            <?php while ($row = $data->fetch_assoc()): ?>
            <tr>
                <td><?= $row["tanggal_periksa"] ?></td>
                <td><?= $row["nama_dokter"] ?></td>
                <td><?= htmlspecialchars($row["diagnosis"]) ?></td>
                <td><?= nl2br(htmlspecialchars($row["catatan"])) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>

    </table>
</div>

</body>
</html>
