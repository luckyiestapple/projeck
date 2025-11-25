<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "pasien") {
    header("Location: login.php");
    exit;
}

$id_pasien = $_SESSION["id_pasien"];

// SUBMIT ambil antrian
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ambil"])) {

    $poli_id   = (int) ($_POST["poli_id"] ?? 0);
    $jadwal_id = (int) ($_POST["jadwal_id"] ?? 0);
    $keluhan   = trim($_POST["keluhan"] ?? "");

    if ($poli_id <= 0 || $jadwal_id <= 0) {
        header("Location: pilih_poli.php");
        exit;
    }

    // ambil data jadwal + dokter
    $stmt = $konek->prepare("
        SELECT j.id, j.dokter_id, j.hari, j.jam_mulai, j.jam_selesai, d.nama AS nama_dokter
        FROM jadwal_dokter j
        JOIN dokter d ON d.id = j.dokter_id
        WHERE j.id = ? AND j.poli_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("ii", $jadwal_id, $poli_id);
    $stmt->execute();
    $jadwal = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$jadwal) {
        header("Location: pilih_poli.php");
        exit;
    }

    // ambil nomor terakhir untuk poli ini
    $last = $konek->query("SELECT MAX(nomor) AS nomor FROM antrian WHERE poli_id = $poli_id")->fetch_assoc();
    $next = ($last["nomor"] ?? 0) + 1;

    // nama pasien
    $p = $konek->query("SELECT nama FROM pasien WHERE id = $id_pasien")->fetch_assoc();
    $nama_pasien = $p["nama"];

    // insert antrian + keluhan
    $stmt = $konek->prepare("
        INSERT INTO antrian (nomor, pasien_id, nama_pasien, dokter_id, poli_id, keluhan)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $dokter_id = $jadwal["dokter_id"];
    $stmt->bind_param("iisiss", $next, $id_pasien, $nama_pasien, $dokter_id, $poli_id, $keluhan);
    $stmt->execute();
    $stmt->close();

    header("Location: antrian_saya.php");
    exit;
}

// MODE TAMPIL JADWAL
$poli_id = (int) ($_POST["poli_id"] ?? $_GET["poli_id"] ?? 0);
if ($poli_id <= 0) {
    header("Location: pilih_poli.php");
    exit;
}

// nama poli
$poli = $konek->query("SELECT nama_poli FROM poli WHERE id = $poli_id")->fetch_assoc();
$nama_poli = $poli["nama_poli"] ?? "-";

// jadwal dokter poli ini
$jadwal = $konek->query("
    SELECT j.*, d.nama AS nama_dokter
    FROM jadwal_dokter j
    JOIN dokter d ON d.id = j.dokter_id
    WHERE j.poli_id = $poli_id
    ORDER BY FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pilih Jadwal & Keluhan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            min-height: 100vh;
            background: radial-gradient(circle at top, #e0f2ff 0, #f4f6fb 45%, #eef1f6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-jadwal {
            width: 760px;
            max-width: 95vw;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(15,23,42,.12);
            padding: 22px 24px 26px;
        }
        .title {
            font-size: 22px;
            font-weight: 700;
        }
        .badge-poli {
            background: #ecfdf5;
            color: #166534;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 500;
        }
        .table-jadwal th, .table-jadwal td {
            font-size: 13px;
            vertical-align: middle;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border-color: #d1d5db;
            padding: 8px 10px;
            font-size: 14px;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(59,130,246,.25);
            border-color: #2563eb;
        }
        .btn-primary {
            border-radius: 999px;
            font-weight: 600;
            padding: 10px 18px;
        }
    </style>
</head>
<body>

<div class="card-jadwal">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h1 class="title mb-1">Pilih Jadwal Dokter</h1>
            <span class="badge-poli">Poli: <?= htmlspecialchars($nama_poli) ?></span>
        </div>
        <a href="pilih_poli.php" class="btn btn-sm btn-outline-secondary">Ganti Poli</a>
    </div>

    <?php if ($jadwal->num_rows == 0): ?>
        <div class="alert alert-warning mt-3 mb-2">
            Belum ada jadwal dokter untuk poli ini. Silakan pilih poli lain.
        </div>
    <?php else: ?>

    <form method="POST">
        <input type="hidden" name="poli_id" value="<?= $poli_id ?>">

        <div class="table-responsive mt-2 mb-3">
            <table class="table table-hover table-jadwal">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;"></th>
                        <th>Dokter</th>
                        <th>Hari</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($j = $jadwal->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <input type="radio" name="jadwal_id"
                                   value="<?= $j['id'] ?>" required>
                        </td>
                        <td><?= htmlspecialchars($j["nama_dokter"]) ?></td>
                        <td><?= htmlspecialchars($j["hari"]) ?></td>
                        <td><?= htmlspecialchars($j["jam_mulai"]) ?></td>
                        <td><?= htmlspecialchars($j["jam_selesai"]) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="mb-3">
            <label class="form-label">Keluhan (singkat)</label>
            <textarea name="keluhan" class="form-control" rows="2"
                      placeholder="Contoh: demam 2 hari, batuk, pusing"></textarea>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" name="ambil" value="1" class="btn btn-primary">
                Ambil Antrian
            </button>
        </div>
    </form>

    <?php endif; ?>
</div>

</body>
</html>
