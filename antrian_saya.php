<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "pasien") {
    header("Location: login.php");
    exit;
}

$id_pasien = $_SESSION["id_pasien"];

if (isset($_GET["ajax"]) && $_GET["ajax"] == "1") {
    $sql = $konek->query("
        SELECT a.*, p.nama_poli, d.nama AS nama_dokter
        FROM antrian a
        LEFT JOIN poli p ON p.id = a.poli_id
        LEFT JOIN dokter d ON d.id = a.dokter_id
        WHERE a.pasien_id = $id_pasien
        ORDER BY a.waktu_daftar DESC
    ");

    $rows = [];
    while ($r = $sql->fetch_assoc()) $rows[] = $r;

    header("Content-Type: application/json");
    echo json_encode($rows);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Antrian Saya</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background:#f9fafb; }
</style>
</head>
<body>

<nav class="navbar navbar-dark bg-success px-4">
    <a class="navbar-brand" href="dashboard_pasien.php">Pasien Panel</a>
</nav>

<div class="container py-4">
    <h3>Status Antrian Saya</h3>
    <p class="text-muted">Realtime update.</p>

    <table class="table table-bordered mt-3">
        <thead class="table-primary">
            <tr>
                <th>No</th>
                <th>Poli</th>
                <th>Dokter</th>
                <th>Keluhan</th>
                <th>Status</th>
                <th>Waktu</th>
            </tr>
        </thead>

        <tbody id="tbody-antriannya"></tbody>
    </table>
</div>

<script>
function badge(s) {
    if (s === "Menunggu") return '<span class="badge bg-secondary">Menunggu</span>';
    if (s === "Dipanggil") return '<span class="badge bg-warning text-dark">Dipanggil</span>';
    return '<span class="badge bg-success">Selesai</span>';
}

function load() {
    fetch("antrian_saya.php?ajax=1")
    .then(r => r.json())
    .then(data => {
        let html = "";
        data.forEach(row => {
            html += `
            <tr>
                <td>${row.nomor}</td>
                <td>${row.nama_poli}</td>
                <td>${row.nama_dokter}</td>
                <td>${row.keluhan ?? "-"}</td>
                <td>${badge(row.status)}</td>
                <td>${row.waktu_daftar}</td>
            </tr>`;
        });
        document.getElementById("tbody-antriannya").innerHTML = html;
    });
}

load();
setInterval(load, 3000);
</script>

</body>
</html>
