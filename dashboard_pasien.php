<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "pasien") {
    header("Location: login.php");
    exit;
}

$id_pasien = $_SESSION["id_pasien"];

$pasien = $konek->query("SELECT * FROM pasien WHERE id = $id_pasien")->fetch_assoc();

// antrian aktif (menunggu / dipanggil)
$sqlAktif = $konek->query("
    SELECT a.*, p.nama_poli, d.nama AS nama_dokter
    FROM antrian a
    LEFT JOIN poli p ON p.id = a.poli_id
    LEFT JOIN dokter d ON d.id = a.dokter_id
    WHERE a.pasien_id = $id_pasien
      AND a.status IN ('Menunggu','Dipanggil')
    ORDER BY a.waktu_daftar DESC
    LIMIT 1
");
$antrianAktif = $sqlAktif->fetch_assoc();

$sqlRM = $konek->query("
    SELECT COUNT(*) AS jml
    FROM rekam_medis
    WHERE id_pasien = $id_pasien
");
$totalRM = $sqlRM->fetch_assoc()["jml"] ?? 0;

// rekam medis terakhir
$sqlRMlast = $konek->query("
    SELECT rm.*, d.nama AS nama_dokter
    FROM rekam_medis rm
    JOIN dokter d ON d.id = rm.id_dokter
    WHERE rm.id_pasien = $id_pasien
    ORDER BY rm.tanggal_periksa DESC
    LIMIT 1
");
$rmTerakhir = $sqlRMlast->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pasien</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            min-height: 100vh;
            margin: 0;
            background: radial-gradient(circle at top, #e0f2ff 0, #f4f6fb 45%, #eef1f6 100%);
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
            max-width: 1040px;
            margin: 0 auto;
            padding: 8px 16px 32px;
        }

        .card-main {
            background: #ffffff;
            border-radius: 22px;
            box-shadow: 0 18px 45px rgba(15,23,42,0.12);
            padding: 22px 24px 24px;
            margin-top: 6px;
        }

        .badge-rm {
            background: #eef2ff;
            color: #4f46e5;
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 11px;
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

        .info-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            background: #f3f4ff;
            padding: 4px 10px;
            font-size: 12px;
            color: #4b5563;
        }

        .info-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #22c55e;
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

        .card-soft {
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            padding: 12px 14px;
            font-size: 13px;
        }

        .status-badge {
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-menunggu {
            background: #e5e7eb;
            color: #374151;
        }

        .status-dipanggil {
            background: #fef3c7;
            color: #92400e;
        }

        .status-selesai {
            background: #dcfce7;
            color: #166534;
        }

        @media (max-width: 768px) {
            .card-main { padding: 18px 16px 20px; }
            .top-bar { padding: 12px 18px; }
        }
    </style>
</head>
<body>

<div class="top-bar">
    <div>
        <div class="top-title">Portal Pasien</div>
        <div class="top-sub">RS Citra Sehat &bull; Layanan antrian & rekam medis online</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span style="font-size:13px; color:#4b5563;">
            <?= htmlspecialchars($pasien["nama"]) ?>
        </span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm logout-btn">Logout</a>
    </div>
</div>

<div class="main-wrap">
    <div class="card-main">
      
        <div class="row g-3 align-items-start">
            <div class="col-md-7">
                <span class="badge-rm">No. RM: <?= htmlspecialchars($pasien["no_rm"]) ?></span>
                <div class="mt-2 welcome-name">Halo, <?= htmlspecialchars($pasien["nama"]) ?></div>
                <div class="welcome-sub">
                    Pantau status antrian, lihat riwayat kunjungan, dan jadwal dokter dalam satu halaman.
                </div>

                <?php if ($antrianAktif): ?>
                    <div class="mt-3 info-chip">
                        <span class="info-dot"></span>
                        <span>
                            Antrian aktif di 
                            <strong><?= htmlspecialchars($antrianAktif["nama_poli"] ?? '-') ?></strong>
                            (No: <?= $antrianAktif["nomor"] ?>)
                        </span>
                    </div>
                <?php else: ?>
                    <div class="mt-3 info-chip" style="background:#fefce8;">
                        <span class="info-dot" style="background:#fde047;"></span>
                        <span>Anda belum memiliki antrian aktif hari ini.</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-5">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="card-soft text-center">
                            <div style="font-size:11px; color:#6b7280;">Rekam Medis</div>
                            <div style="font-size:20px; font-weight:700; line-height:1;">
                                <?= $totalRM ?>
                            </div>
                            <div style="font-size:11px; color:#9ca3af;">Kunjungan terekam</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <a href="antrian_saya.php" class="text-decoration-none text-reset">
                            <div class="card-soft text-center">
                                <div style="font-size:11px; color:#6b7280;">Antrian</div>
                                <div style="font-size:20px; font-weight:700; line-height:1;">
                                    <?= $antrianAktif ? $antrianAktif["nomor"] : "-" ?>
                                </div>
                                <div style="font-size:11px; color:#9ca3af;">
                                    <?= $antrianAktif ? htmlspecialchars($antrianAktif["status"]) : "Belum ada" ?>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4" style="border-color:#e5e7eb,;">

       <div class="row g-3 justify-content-center">
            <div class="col-md-3 col-6">
                <div class="menu-card">
                    <div class="menu-title">Ambil Antrian</div>
                    <div class="menu-text">
                        Pilih poli dan jadwal dokter, lalu ambil nomor antrian secara online.
                    </div> 
                    <a href="pilih_poli.php" class="btn btn-primary menu-btn w-100">
                        Pilih Poli
                    </a>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="menu-card">
                    <div class="menu-title">Antrian Saya</div>
                    <div class="menu-text">
                        Lihat status antrian Anda secara realtime tanpa perlu refresh manual.
                    </div>
                    <a href="antrian_saya.php" class="btn btn-primary menu-btn w-100">
                        Lihat Antrian
                    </a>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <div class="menu-card">
                    <div class="menu-title">Rekam Medis</div>
                    <div class="menu-text">
                        Riwayat diagnose dan catatan dokter yang pernah menangani Anda.
                    </div>
                    <a href="rekam_medis_saya.php" class="btn btn-primary menu-btn w-100">
                        Lihat Rekam
                    </a>
                </div>
            </div>

        <div class="row g-3 mt-4">
            <div class="col-md-6">
                <div class="section-title">Antrian Aktif</div>
                <div class="card-soft">
                    <?php if ($antrianAktif): ?>
                        <div class="d-flex justify-content-between mb-1">
                            <div>
                                <div style="font-size:13px; font-weight:600;">
                                    Poli <?= htmlspecialchars($antrianAktif["nama_poli"] ?? '-') ?>
                                </div>
                                <div style="font-size:12px; color:#6b7280;">
                                    Dokter: <?= htmlspecialchars($antrianAktif["nama_dokter"] ?? '-') ?>
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
                            <a href="antrian_saya.php" class="btn btn-sm btn-outline-primary">
                                Detail Antrian
                            </a>
                        </div>
                    <?php else: ?>
                        <div style="font-size:13px; color:#6b7280;">
                            Belum ada antrian aktif. Silakan ambil antrian pada menu <strong>Ambil Antrian</strong>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="section-title">Kunjungan Terakhir</div>
                <div class="card-soft">
                    <?php if ($rmTerakhir): ?>
                        <div style="font-size:13px; font-weight:600;">
                            Tanggal: <?= htmlspecialchars($rmTerakhir["tanggal_periksa"]) ?>
                        </div>
                        <div style="font-size:12px; color:#6b7280;">
                            Dokter: <?= htmlspecialchars($rmTerakhir["nama_dokter"]) ?>
                        </div>
                        <div class="mt-2" style="font-size:12px;">
                            Diagnosis: <strong><?= htmlspecialchars($rmTerakhir["diagnosis"]) ?></strong>
                        </div>
                        <?php if (!empty($rmTerakhir["catatan"])): ?>
                            <div class="mt-1" style="font-size:12px;">
                                Catatan: <?= nl2br(htmlspecialchars($rmTerakhir["catatan"])) ?>
                            </div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <a href="rekam_medis_saya.php" class="btn btn-sm btn-outline-primary">
                                Lihat Semua Rekam Medis
                            </a>
                        </div>
                    <?php else: ?>
                        <div style="font-size:13px; color:#6b7280;">
                            Belum ada rekam medis yang tercatat. Rekam medis akan muncul setelah Anda diperiksa dokter.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
