<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "pasien") {
    header("Location: login.php");
    exit;
}

// Ambil list POLI
$poli = $konek->query("SELECT * FROM poli ORDER BY nama_poli ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pilih Poli</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to bottom, #e0f2ff, #ffffff);
            font-family: 'Inter', sans-serif;
        }

        .box {
            max-width: 550px;
            margin: 60px auto;
            background: #fff;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 18px 40px rgba(0,0,0,0.08);
        }

        .btn-green {
            background: #0d8a3d;
            padding: 14px;
            border-radius: 40px;
            font-size: 17px;
            font-weight: 600;
        }

        .btn-green:hover {
            background: #0b6f31;
        }

        select {
            border-radius: 12px !important;
            padding: 10px !important;
        }
    </style>
</head>

<body>

<div class="box">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold">Pilih Poli</h3>
        <a href="dashboard_pasien.php" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>

    <p class="text-muted">Pilih poli tujuan terlebih dahulu untuk melihat jadwal dokter.</p>

    <form action="jadwal_dokter.php" method="GET">

        <label class="mb-2 fw-semibold">Poli Tujuan</label>

        <select name="poli" class="form-select mb-3" required>
            <option value="">-- Pilih Poli --</option>

            <?php while ($p = $poli->fetch_assoc()): ?>
                <option value="<?= $p['id']; ?>">
                    <?= htmlspecialchars($p['nama_poli']); ?>
                </option>
            <?php endwhile; ?>

        </select>

        <button class="btn btn-green text-white w-100 mt-2">Lihat Jadwal Dokter</button>
    </form>
</div>

</body>
</html>
