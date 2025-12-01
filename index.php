<?php
require "koneksi.php";

// Ambil data poli
$poli = $konek->query("SELECT * FROM poli ORDER BY nama_poli ASC");

// Ambil dokter
$dokter = $konek->query("SELECT * FROM dokter ORDER BY nama ASC LIMIT 6");

// Ambil jadwal dokter
$jadwal = $konek->query("
    SELECT j.*, d.nama AS dokter, d.poli
    FROM jadwal_dokter j
    JOIN dokter d ON d.id = j.dokter_id
    ORDER BY FIELD(j.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rumah Sakit Citra Medika</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Poppins', sans-serif; }
        .hero-title { font-size: 42px; font-weight: 700; text-shadow: 0 3px 8px rgba(0,0,0,.35); }
        .section-title { font-size: 32px; font-weight: 700; }
        .footer { background:#0d6efd; padding:25px; color:#fff; margin-top:40px; }
    </style>
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg bg-white shadow-sm fixed-top">
    <div class="container">
        <a class="navbar-brand text-primary fw-bold" href="#">
            <i class="bi bi-hospital fs-3 me-2"></i>RS Citra Medika
        </a>

        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="nav" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#layanan">Poli</a></li>
                <li class="nav-item"><a class="nav-link" href="#dokter">Dokter</a></li>
                <li class="nav-item"><a class="nav-link" href="#jadwal">Jadwal</a></li>  
                <li class="nav-item"><a class="nav-link" href="login.php">Masuk</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- SLIDER -->
<div id="slider" class="carousel slide" data-bs-ride="carousel" style="margin-top:70px;">
    <div class="carousel-inner">

        <div class="carousel-item active">
            <img src="img/gedung.png" class="d-block w-100" style="height:500px; object-fit:cover;">
            <div class="carousel-caption d-none d-md-block text-start">
                <h1 class="hero-title">Pelayanan Kesehatan Terbaik</h1>
                <p>Didukung dokter profesional & peralatan modern.</p>
            </div>
        </div>

        <div class="carousel-item">
            <img src="img/rs2.png" class="d-block w-100" style="height: 500px; object-fit:cover;">
            <div class="carousel-caption d-none d-md-block text-start">
                <h1 class="hero-title">Daftar Antrian Lebih Mudah</h1>
                <p>Semuanya serba online, tanpa ribet.</p>
            </div>
        </div>

        <div class="carousel-item">
            <img src="img/dokter.png" class="d-block w-100" style="height:500px; object-fit:cover;">
            <div class="carousel-caption d-none d-md-block text-start">
                <h1 class="hero-title">Rekam Medis Digital</h1>
                <p>Akses riwayat kesehatan Anda kapan saja.</p>
            </div>
        </div>

    </div>
</div>
<div class="container my-4">
    <div style="
    max-width: 900px;
    background: #ffff;
    border-radius: 12px;
    padding: 22px 28px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.08);
    border-left: 6px solid #0d6efd;
    margin: 0 auto;">

    <p style="
    text-align: justify;
    font-size: 15px;
    color: #444;
    line-height: 1.6;
    margin:0;">
    Temukan dokter spesialis yang sesuai dengan kebutuhan Anda dengan jadwal praktek yang selalu diperbarui.
    Untuk memudahkan proses pendaftaran, antrean, dan akses rekam medis, kamu menyarankan Anda membuat Akun Pasien terlebih dahulu. 
    Dengan akun, Anda dapat mengambil antrean secara online, memantau status antrean secara real-time, serta melihat riwayat medis kapan saja.
    <br>
    Jika Anda sudah memiliki Akun, silakan Masuk untuk melanjutkan. Pelayanan kesehatan yang lebih cepat, mudah, dan nyaman dimulai dari sini. 
   
</p>
    </div>
</div>
<!-- JADWAL -->
<section id="jadwal" class="container py-5">
    <h2 class="section-title text-center mb-4">Jadwal Praktek Dokter</h2>

    <table class="table table-bordered">
        <thead class="table-primary">
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
</section>

<!-- POLI -->
<section id="layanan" class="container py-5 text-center">
    <h2 class="section-title mb-3">Layanan Poli</h2>
    <div class="row g-4">

        <?php while($p = $poli->fetch_assoc()): ?>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-heart-pulse text-primary fs-1"></i>
                    <h5 class="mt-3"><?= $p["nama_poli"] ?></h5>
                </div>
            </div>
        </div>
        <?php endwhile; ?>

    </div>
</section>

<!-- DOKTER -->
<section id="dokter" class="bg-white py-5">
    <div class="container">
        <h2 class="section-title text-center mb-4">Dokter Kami</h2>

        <div class="row g-4">
            <?php while($d = $dokter->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card shadow-sm p-3 text-center">
                    <i class="bi bi-person-circle fs-1 text-success"></i>
                    <h5 class="mt-3"><?= $d["nama"] ?></h5>
                    <p class="text-muted mb-0">Spesialis: <?= $d["spesialisasi"] ?></p>
                    <p class="text-muted mb-2">Poli: <?= $d["poli"] ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    </div>
</section>

<footer class="footer text-center">
    © <?= date('Y') ?> RS Citra Medika — Sistem Informasi Rumah Sakit
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
 