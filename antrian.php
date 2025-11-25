<?php
session_start();
require "koneksi.php";

// CEK LOGIN DOKTER
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "dokter") {
    header("Location: login.php");
    exit;
}

$id_dokter = $_SESSION["id_dokter"];

// Ambil data antrian berdasarkan dokter yang login
$antrian = $konek->query("
    SELECT a.*, p.nama AS nama_pasien, pl.nama_poli
    FROM antrian a
    LEFT JOIN pasien p ON p.id = a.pasien_id
    LEFT JOIN poli pl ON pl.id = a.poli_id
    WHERE a.dokter_id = $id_dokter
    ORDER BY a.status = 'Menunggu' DESC, a.waktu_daftar ASC
");

// Proses update status (panggil / selesai)
if (isset($_GET["id"]) && isset($_GET["s"])) {
    $id = (int) $_GET["id"];
    $s  = $_GET["s"];

    $allowed = ["Menunggu","Dipanggil","Selesai"];

    if (in_array($s, $allowed)) {
        $konek->query("UPDATE antrian SET status='$s' WHERE id=$id");
        header("Location: antrian.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Antrian Pasien</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="dashboard_dokter.php">Panel Dokter</a>
    <div>
        <a class="nav-link d-inline text-white" href="dashboard_dokter.php">Dashboard</a>
        <a class="nav-link d-inline text-warning" href="logout.php">Logout</a>
    </div>
</nav>

<div class="container py-4">
    <h3>Antrian Pasien</h3>

    <table class="table table-bordered table-striped mt-3">
        <thead class="table-primary">
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
            <?php while ($row = $antrian->fetch_assoc()): ?>
            <tr>
                <td><?= $row["nomor"] ?></td>
                <td><?= htmlspecialchars($row["nama_pasien"]) ?></td>
                <td><?= htmlspecialchars($row["nama_poli"]) ?></td>
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
                        <a class="btn btn-sm btn-warning" href="antrian.php?id=<?= $row['id'] ?>&s=Dipanggil">
                            Panggil
                        </a>
                    <?php elseif ($row["status"] == "Dipanggil"): ?>
                        <a class="btn btn-sm btn-success" href="antrian.php?id=<?= $row['id'] ?>&s=Selesai">
                            Selesai
                        </a>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>

    </table>

</div>

</body>
</html>
