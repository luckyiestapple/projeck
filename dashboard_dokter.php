<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "dokter") {
    header("Location: login.php");
    exit;
}

$id_dokter = $_SESSION["id_dokter"];

// data dokter
$dokter = $konek->query("SELECT * FROM dokter WHERE id = $id_dokter")->fetch_assoc();

// hari ini
$today = date('Y-m-d');

// total antrian hari ini
$sqlAntrianToday = $konek->query("
    SELECT COUNT(*) AS jml
    FROM antrian
    WHERE dokter_id = $id_dokter
      AND DATE(waktu_daftar) = '$today'
");
$totalAntrianToday = $sqlAntrianToday->fetch_assoc()["jml"] ?? 0;

// total selesai hari ini
$sqlSelesaiToday = $konek->query("
    SELECT COUNT(*) AS jml
    FROM antrian
    WHERE dokter_id = $id_dokter
      AND DATE(waktu_daftar) = '$today'
      AND status = 'Selesai'
");
$totalSelesaiToday = $sqlSelesaiToday->fetch_assoc()["jml"] ?? 0;

// antrian berjalan (Dipanggil prioritas, lalu Menunggu paling awal)
$sqlCurrent = $konek->query("
    SELECT a.*, p.nama AS nama_pasien, pl.nama_poli
    FROM antrian a
    LEFT JOIN pasien p ON p.id = a.pasien_id
    LEFT JOIN poli pl ON pl.id = a.poli_id
    WHERE a.dokter_id = $id_dokter
      AND a.status IN ('Menunggu','Dipanggil')
    ORDER BY FIELD(a.status,'Dipanggil','Menunggu'), a.waktu_daftar ASC
    LIMIT 1
");
$antrianAktif = $sqlCurrent->fetch_assoc();

// total rekam medis (pasien pernah ditangani)
$sqlRMtotal = $konek->query("
    SELECT COUNT(*) AS jml
    FROM rekam_medis
    WHERE id_dokter = $id_dokter
");
$totalRM = $sqlRMtotal->fetch_assoc()["jml"] ?? 0;

// rekam medis terakhir (5 pasien terakhir)
$sqlLastRM = $konek->query("
    SELECT rm.*, p.nama AS nama_pasien
    FROM rekam_medis rm
    JOIN pasien p ON p.id = rm.id_pasien
    WHERE rm.id_dokter = $id_dokter
    ORDER BY rm.tanggal_periksa DESC
    LIMIT 5
");

// jadwal dokter
$sqlJadwal = $konek->query("
    SELECT *
    FROM jadwal_dokter
    WHERE dokter_id = $id_dokter
    ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), jam_mulai
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Dokter</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            min-height: 100vh;
            margin: 0;
            background: radial-gradient(circle at top, #dbeafe 0, #eff6ff 45%, #eef1f6 100%);
        }

        .top-bar {
            padding: 14px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-title {
            font-weight: 700;
            font-size: 20px;
            color: #0f172a;
        }

        .top-sub {
            font-size: 12px;
            color: #6b7280;
        }

        .logout-btn {
            border-radius: 999px;
            padding: 7px 14px;
            font-size: 13px;
        }

        .main-wrap {
            max-width: 1120px;
            margin: 0 auto;
            padding: 8px 16px 32px;
        }

        .card-main {
            background: #ffffff;
            border-radius: 22px;
            box-shadow: 0 18px 45px rgba(15,23,42,0.14);
            padding: 22px 24px 24px;
            margin-top: 6px;
        }

        .badge-spesialis {
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .welcome-name {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
        }

        .welcome-sub {
            font-size: 13px;
            color: #6b7280;
        }

        .chip-online {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            background: #ecfdf5;
            padding: 4px 10px;
            font-size: 11px;
            color: #166534;
            margin-top: 6px;
        }

        .dot-online {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #22c55e;
        }

        .small-label {
            font-size: 11px;
            color: #6b7280;
        }

        .big-number {
            font-size: 22px;
            font-weight: 700;
            line-height: 1;
            color: #111827;
        }

        .card-soft {
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            padding: 12px 14px;
            font-size: 13px;
        }

        .menu-card {
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            padding: 14px 14px 14px;
            background: #f9fafb;
            transition: all .15s;
            height: 100%;
        }

        .menu-card:hover {
            background: #eff6ff;
            border-color: #bfdbfe;
            transform: translateY(-1px);
        }

        .menu-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .menu-text {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .menu-btn {
            border-radius: 999px;
            font-size: 13px;
            padding: 7px 14px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .status-badge {
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-menunggu { background:#e5e7eb; color:#374151; }
        .status-dipanggil{ background:#fef3c7; color:#92400e; }
        .status-selesai  { background:#dcfce7; color:#166534; }

        .table-sm td, .table-sm th { font-size: 12px; vertical-align: middle; }

        @media (max-width: 768px) {
            .card-main { padding: 18px 16px 20px; }
            .top-bar { padding: 12px 18px; }
        }
    </style>
</head>
<body>

<div class="top-bar">
    <div>
        <div class="top-title">Panel Dokter</div>
        <div class="top-sub">RS Citra Medika &bull; Manajemen antrian & rekam medis pasien</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span style="font-size:13px; color:#4b5563;">
            <?= htmlspecialchars($dokter["nama"]) ?>
        </span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm logout-btn">Logout</a>
    </div>
</div>

<div class="main-wrap">
    <div class="card-main">
        <!-- HEADER -->
        <div class="row g-3 align-items-start">
            <div class="col-md-7">
                <span class="badge-spesialis">
                    <?= htmlspecialchars($dokter["spesialisasi"] ?: 'Dokter Umum') ?>
                    <?php if (!empty($dokter["poli"])): ?>
                        • Poli <?= htmlspecialchars($dokter["poli"]) ?>
                    <?php endif; ?>
                </span>

                <div class="mt-2 welcome-name">
                    Selamat datang, <?= htmlspecialchars($dokter["nama"]) ?>
                </div>
                <div class="welcome-sub">
                    Pantau antrian pasien, riwayat pemeriksaan, dan jadwal praktek Anda dalam satu tampilan.
                </div>

                <div class="chip-online">
                    <span class="dot-online"></span>
                    <span>Anda sedang login sebagai Dokter</span>
                </div>
            </div>

            <div class="col-md-5">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="card-soft text-center">
                            <div class="small-label">Antrian hari ini</div>
                            <div class="big-number"><?= $totalAntrianToday ?></div>
                            <div class="small-label">Total pasien terdaftar</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card-soft text-center">
                            <div class="small-label">Selesai hari ini</div>
                            <div class="big-number"><?= $totalSelesaiToday ?></div>
                            <div class="small-label">Sudah ditangani</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color:#e5e7eb;">

        <!-- MENU -->
        <div class="row g-3"center>
            <div class="col-md-3 col-6">
                <div class="menu-card">
                    <div class="menu-title">Kelola Antrian</div>
                    <div class="menu-text">
                        Lihat daftar antrian pasien, panggil dan selesaikan kunjungan.
                    </div>
                    <a href="antrian.php" class="btn btn-primary menu-btn w-100">
                        Buka Antrian
                    </a>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="menu-card">
                    <div class="menu-title">Rekam Medis</div>
                    <div class="menu-text">
                        Akses rekam medis pasien yang pernah Anda tangani.
                    </div>
                    <a href="cari_rekam_medis.php" class="btn btn-primary menu-btn w-100">
                    <i class="fas fa-notes-medical me-1"></i> Riwayat Rekam Medis
                </a>
                </a>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="menu-card">
                    <div class="menu-title">Tambah Rekam</div>
                    <div class="menu-text">
                        Catat hasil pemeriksaan dan diagnosis untuk pasien.
                    </div>
                    <a href="tambah_rekam.php" class="btn btn-success menu-btn w-100">
                        Input Rekam
                    </a>
                </div>
            </div>

            
       

        <!-- BAWAH: ANTRIAN AKTIF + REKAM TERAKHIR + JADWAL -->
        <div class="row g-3 mt-4">
            <div class="col-md-6">
                <div class="section-title">Antrian Berjalan</div>
                <div class="card-soft">
                    <?php if ($antrianAktif): ?>
                        <div class="d-flex justify-content-between mb-1">
                            <div>
                                <div style="font-size:13px; font-weight:600;">
                                    Pasien: <?= htmlspecialchars($antrianAktif["nama_pasien"] ?? '-') ?>
                                </div>
                                <div style="font-size:12px; color:#6b7280;">
                                    Poli: <?= htmlspecialchars($antrianAktif["nama_poli"] ?? '-') ?>
                                </div>
                            </div>
                            <div>
                                <?php if ($antrianAktif["status"] == "Menunggu"): ?>
                                    <span class="status-badge status-menunggu">Menunggu</span>
                                <?php elseif ($antrianAktif["status"] == "Dipanggil"): ?>
                                    <span class="status-badge status-dipanggil">Dipanggil</span>
                                <?php else: ?>
                                    <span class="status-badge status-selesai">Selesai</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="font-size:12px; margin-top:4px;">
                            No Antrian: <strong><?= $antrianAktif["nomor"] ?></strong><br>
                            Waktu daftar: <?= $antrianAktif["waktu_daftar"] ?>
                        </div>
                        <?php if (!empty($antrianAktif["keluhan"])): ?>
                            <div class="mt-2" style="font-size:12px;">
                                Keluhan: <em><?= htmlspecialchars($antrianAktif["keluhan"]) ?></em>
                            </div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <a href="antrian.php" class="btn btn-sm btn-outline-primary">
                                Kelola Antrian
                            </a>
                        </div>
                    <?php else: ?>
                        <div style="font-size:13px; color:#6b7280;">
                            Tidak ada antrian aktif saat ini. Silakan cek menu <strong>Kelola Antrian</strong>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="section-title">Ringkasan Rekam Medis</div>
                <div class="card-soft">
                    <?php if ($totalRM == 0): ?>
                        <div style="font-size:13px; color:#6b7280;">
                            Belum ada rekam medis yang tercatat. Rekam medis akan muncul setelah Anda menginput pada menu <strong>Tambah Rekam</strong>.
                        </div>
                    <?php else: ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small-label">Total rekam medis yang Anda buat</div>
                            <div class="big-number" style="font-size:18px;"><?= $totalRM ?></div>
                        </div>
                        <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">
                            5 rekam medis terakhir:
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <?php while ($rm = $sqlLastRM->fetch_assoc()): ?>
                                        <tr>
                                            <td style="width: 40%;">
                                                <strong><?= htmlspecialchars($rm["nama_pasien"]) ?></strong><br>
                                                <span style="font-size:11px; color:#6b7280;">
                                                    <?= htmlspecialchars($rm["tanggal_periksa"]) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span style="font-size:12px;">
                                                    <?= htmlspecialchars($rm["diagnosis"]) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <a href="rekam.php" class="btn btn-sm btn-outline-primary">
                                Lihat Semua Rekam Medis
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-4">
            <div class="col-12">
                <div class="section-title">Jadwal Praktek Anda</div>
                <div class="card-soft">
                    <?php if ($sqlJadwal->num_rows == 0): ?>
                        <div style="font-size:13px; color:#6b7280;">
                            Belum ada jadwal praktek yang terdaftar untuk akun ini.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Hari</th>
                                        <th>Poli</th>
                                        <th>Jam Mulai</th>
                                        <th>Jam Selesai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($j = $sqlJadwal->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($j["hari"]) ?></td>
                                            <td><?= htmlspecialchars($j["poli"] ?: '-') ?></td>
                                            <td><?= htmlspecialchars($j["jam_mulai"]) ?></td>
                                            <td><?= htmlspecialchars($j["jam_selesai"]) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
