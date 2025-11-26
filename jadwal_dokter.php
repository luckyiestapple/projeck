<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "pasien") {
    header("Location: login.php");
    exit;
}

// pastikan poli dipilih
if (!isset($_GET["poli"])) {
    header("Location: pilih_poli.php");
    exit;
}

$poli_id = intval($_GET["poli"]);

// Ambil nama poli
$poliData = $konek->query("SELECT nama_poli FROM poli WHERE id = $poli_id")->fetch_assoc();
$nama_poli = $poliData ? $poliData["nama_poli"] : "Poli Tidak Ditemukan";

// Ambil jadwal sesuai poli
$jadwal = $konek->query("
    SELECT j.*, d.nama AS nama_dokter
    FROM jadwal_dokter j
    JOIN dokter d ON d.id = j.dokter_id
    WHERE j.poli_id = $poli_id
    ORDER BY FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Dokter - <?= htmlspecialchars($nama_poli) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: linear-gradient(to bottom, #e0f2ff, #ffffff);
        }

        .card-box {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 18px 40px rgba(0,0,0,0.08);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .btn-green {
            background: #0d8a3d;
            color: white;
            border-radius: 40px;
            font-weight: 600;
            padding: 10px 22px;
        }

        .btn-green:hover {
            background: #0b6f31;
            color: white;
        }

        .back-btn {
            border-radius: 10px;
            padding: 6px 16px;
        }
    </style>
</head>

<body>

<div class="card-box">

    <div class="d-flex justify-content-between mb-3">
        <h3 class="fw-bold">Jadwal Dokter • <?= htmlspecialchars($nama_poli) ?></h3>
        <a href="pilih_poli.php" class="btn btn-outline-secondary back-btn">Kembali</a>
    </div>

    <p class="text-muted mb-3">Berikut jadwal dokter yang bertugas di poli ini.</p>

    <table class="table table-bordered table-hover">
        <thead class="table-success">
            <tr>
                <th>Dokter</th>
                <th>Hari</th>
                <th>Mulai</th>
                <th>Selesai</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($jadwal->num_rows == 0): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted p-3">
                        Belum ada jadwal dokter untuk poli ini.
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($row = $jadwal->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row["nama_dokter"]) ?></td>
                    <td><?= $row["hari"] ?></td>
                    <td><?= $row["jam_mulai"] ?></td>
                    <td><?= $row["jam_selesai"] ?></td>
                    <td>
                        <a href="ambil_antrian.php?jadwal=<?= $row['id'] ?>&poli=<?= $poli_id ?>" 
                           class="btn btn-green btn-sm w-100">
                            Ambil Antrian
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>
