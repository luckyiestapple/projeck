<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "pasien") {
    header("Location: login.php");
    exit;
}

$jadwal = $konek->query("
    SELECT j.*, d.nama AS dokter, p.nama_poli
    FROM jadwal_dokter j
    JOIN dokter d ON d.id = j.dokter_id
    JOIN poli p ON p.id = j.poli_id
    ORDER BY FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')
");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Jadwal Dokter</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-success px-4">
    <a class="navbar-brand" href="dashboard_pasien.php">Pasien Panel</a>
</nav>

<div class="container py-4">
    <h3>Jadwal Dokter</h3>

    <table class="table table-bordered mt-3">
        <thead class="table-warning">
            <tr>
                <th>Dokter</th>
                <th>Poli</th>
                <th>Hari</th>
                <th>Mulai</th>
                <th>Selesai</th>
            </tr>
        </thead>

        <tbody>
            <?php while ($j = $jadwal->fetch_assoc()): ?>
            <tr>
                <td><?= $j["dokter"] ?></td>
                <td><?= $j["nama_poli"] ?></td>
                <td><?= $j["hari"] ?></td>
                <td><?= $j["jam_mulai"] ?></td>
                <td><?= $j["jam_selesai"] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>

    </table>
</div>

</body>
</html>
