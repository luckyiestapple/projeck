<?php
require "koneksi.php";

// Ambil data poli
$poli = $konek->query("SELECT * FROM poli ORDER BY nama_poli ASC");

// Ambil dokter
$dokter = $konek->query("SELECT * FROM dokter ORDER BY nama ASC LIMIT 6");

// Ambil jadwal dokter
$jadwal = $konek->query("
SELECT 
    d.nama AS dokter,
    d.poli,
    GROUP_CONCAT(
        CONCAT(
            '• ', j.hari, ' (',
            TIME_FORMAT(j.jam_mulai, '%H:%i'),
            ' - ',
            TIME_FORMAT(j.jam_selesai, '%H:%i'),
            ')'
        )
        ORDER BY FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')
        SEPARATOR '<br>'
    ) AS jadwal_praktek
FROM jadwal_dokter j
JOIN dokter d ON d.id = j.dokter_id
GROUP BY d.id
ORDER BY d.nama ASC
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
        .footer { background:#242BAB; padding:25px; color:#fff; margin-top:40px; }
      .nav-bg {
  background: transparent;
  transition: all 0.4s ease;
}

.nav-hide {
  transform: translateY(-100%);
}

.nav-scroll {
  background: rgba(255,255,255,0.95) !important;
  box-shadow: 0 2px 0   px rgba(0,0,0,0.15);
}

.nav-bg .navbar-brand {
  color: #0d47a1 !important;
}

.nav-bg .nav-link {
  color: #333 !important;
}
.mt-3{
    font-size: medium;
}
.mb-0{
    font-size: small;
}
    </style>
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg shadow-sm fixed-top nav-bg">
    <div class="container">
     <a class="navbar-brand d-flex align-items-center fw-bold" href="index.php">
    <img src="img/logocitra.png" class="me-2" style="height:40px; width:auto; object-fit:contain;">
    RS Citra Medika
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
<div id="carouselRS" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000" style="margin-top:70px;">
  <div class="carousel-inner">

    <div class="carousel-item active">
      <img src="img/gedung.png" class="d-block w-100" style="height:500px; object-fit:cover;">
      <div class="carousel-caption text-start">
        <h1 class="hero-title">Pelayanan Kesehatan Terbaik</h1>
        <p>Didukung dokter profesional & peralatan modern.</p>
      </div>
    </div>

    <div class="carousel-item">
      <img src="img/rs2.png" class="d-block w-100" style="height:500px; object-fit:cover;">
      <div class="carousel-caption text-start">
        <h1 class="hero-title">Daftar Antrian Lebih Mudah</h1>
        <p>Semuanya serba online, tanpa ribet.</p>
      </div>
    </div>

    <div class="carousel-item">
      <img src="img/dokter2.1.png" class="d-block w-100" style="height:500px; object-fit:cover;">
      <div class="carousel-caption text-start">
        <h1 class="hero-title">Rekam Medis Digital</h1>
        <p>Akses riwayat kesehatan Anda kapan saja.</p>
      </div>
    </div>

  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#carouselRS" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carouselRS" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
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
    <br>
    Jika Anda sudah memiliki Akun, silakan Masuk untuk melanjutkan. Pelayanan kesehatan yang lebih cepat, mudah, dan nyaman dimulai dari sini. 
   
</p>
    </div>
</div>
<!-- JADWAL -->
<section id="jadwal" class="container py-5">
  <h2 class="section-title text-center mb-4">Jadwal Praktek Dokter</h2>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-primary text-center">
        <tr>
          <th>Dokter</th>
          <th>Poli</th>
          <th>Jadwal Praktik</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($j = $jadwal->fetch_assoc()): ?>
        <tr>
          <td class="fw-semibold"><?= $j["dokter"] ?></td>
          <td class="text-center"><?= $j["poli"] ?></td>
          <td>
            <ul class="list-group list-group-flush">
              <?php 
                $list = explode('|', $j["jadwal_praktek"]);
                foreach ($list as $item):
              ?>
                <li class="list-group-item bg-transparent px-0 py-1">
                  <?= $item ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</section>



<!-- POLI -->
<section id="layanan" class="container py-5 text-center">
    <h2 class="section-title mb-3">Layanan Poli</h2>
    <div class="row g-4">

        <?php while($p = $poli->fetch_assoc()): ?>
        <div class="col-md-4 col-sm-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <i class="bi bi-heart-pulse text-primary fs-1"></i>
                    <h5 class="mt-2"><?= $p["nama_poli"] ?></h5>
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
                    <p class="text-muted mb-0">Poli: <?= $d["poli"] ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    </div>
</section>

<footer class="footer text-center">
    © <?= date('Y') ?> RS Citra Medika — All rights reserved. group 2
</footer>
<script>    
let lastScroll = 0;
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', function() {
  const currentScroll = window.pageYOffset;

  // HILANG / MUNCUL
  if (currentScroll > lastScroll && currentScroll > 80) {
    navbar.classList.add('nav-hide');   // scroll ke bawah → hilang
  } else {
    navbar.classList.remove('nav-hide'); // scroll ke atas → muncul
  }

  // BACKGROUND GAMBAR HILANG SAAT SCROLL
  if (currentScroll > 50) {
    navbar.classList.add('nav-scroll');   // jadi putih
  } else {
    navbar.classList.remove('nav-scroll'); // balik ke background gambar
  }

  lastScroll = currentScroll;
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
 