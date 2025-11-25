<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "dokter") {
    header("Location: login.php");
    exit;
}

$id_dokter = $_SESSION["id_dokter"];

// MODE AJAX → realtime data
if (isset($_GET["ajax"]) && $_GET["ajax"] == "1") {
    $antrian = $konek->query("
        SELECT a.*, p.nama AS nama_pasien, pl.nama_poli
        FROM antrian a
        LEFT JOIN pasien p ON p.id = a.pasien_id
        LEFT JOIN poli pl ON pl.id = a.poli_id
        WHERE a.dokter_id = $id_dokter
        ORDER BY a.status = 'Menunggu' DESC, a.waktu_daftar ASC
    ");

    $rows = [];
    while ($row = $antrian->fetch_assoc()) {
        $rows[] = $row;
    }

    header("Content-Type: application/json");
    echo json_encode($rows);
    exit;
}

// Update status
if (isset($_GET["id"]) && isset($_GET["s"])) {
    $id = (int) $_GET["id"];
    $s  = $_GET["s"];

    if (in_array($s, ["Menunggu","Dipanggil","Selesai"])) {
        $konek->query("UPDATE antrian SET status='$s' WHERE id=$id AND dokter_id=$id_dokter");
        header("Location: antrian.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Antrian Pasien (Dokter)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: #f8fafc; }
        table td { vertical-align: middle; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="dashboard_dokter.php">Panel Dokter</a>
</nav>

<div class="container py-4">
    <h3>Antrian Pasien</h3>

    <table class="table table-bordered table-hover mt-3">
        <thead class="table-primary">
            <tr>
                <th>No</th>
                <th>Nama Pasien</th>
                <th>Poli</th>
                <th>Keluhan</th>
                <th>Status</th>
                <th>Waktu Daftar</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody id="tbody-antrian">
            <!-- realtime JS -->
        </tbody>
    </table>
</div>

<script>
function badgeStatus(s) {
    if (s === "Menunggu") return '<span class="badge bg-secondary">Menunggu</span>';
    if (s === "Dipanggil") return '<span class="badge bg-warning text-dark">Dipanggil</span>';
    return '<span class="badge bg-success">Selesai</span>';
}

function tombol(row) {
    if (row.status === "Menunggu")
        return `<a href="antrian.php?id=${row.id}&s=Dipanggil" class="btn btn-sm btn-warning">Panggil</a>`;
    if (row.status === "Dipanggil")
        return `<a href="antrian.php?id=${row.id}&s=Selesai" class="btn btn-sm btn-success">Selesai</a>`;
    return "-";
}

function load() {
    fetch("antrian.php?ajax=1")
    .then(r => r.json())
    .then(data => {
        let html = "";
        data.forEach(row => {
            html += `
            <tr>
                <td>${row.nomor}</td>
                <td>${row.nama_pasien}</td>
                <td>${row.nama_poli}</td>
                <td>${row.keluhan ?? "-"}</td>
                <td>${badgeStatus(row.status)}</td>
                <td>${row.waktu_daftar}</td>
                <td>${tombol(row)}</td>
            </tr>`;
        });
        document.getElementById("tbody-antrian").innerHTML = html;
    });
}

load();
setInterval(load, 3000);
</script>

</body>
</html>
