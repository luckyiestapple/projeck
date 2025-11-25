<?php
session_start();
require "koneksi.php";

// hanya pasien
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "pasien") {
    header("Location: login.php");
    exit;
}

// ambil semua poli
$poli = $konek->query("SELECT * FROM poli ORDER BY nama_poli ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pilih Poli</title>
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
        .card-poli {
            width: 460px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(15,23,42,.12);
            padding: 24px 26px 28px;
        }
        .title {
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 6px;
        }
        .subtitle {
            font-size: 13px;
            text-align: center;
            color: #6b7280;
            margin-bottom: 16px;
        }
        .form-select, .form-control {
            border-radius: 10px;
            border-color: #d1d5db;
            padding: 9px 11px;
            font-size: 14px;
        }
        .form-select:focus, .form-control:focus {
            box-shadow: 0 0 0 3px rgba(34,197,94,.25);
            border-color: #16a34a;
        }
        .btn-success {
            border-radius: 999px;
            font-weight: 600;
            padding: 10px 16px;
        }
    </style>
</head>
<body>

<div class="card-poli">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="title mb-0">Pilih Poli</h1>
        <a href="dashboard_pasien.php" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>
    <p class="subtitle">Pilih poli tujuan terlebih dahulu untuk melihat jadwal dokter.</p>

    <form method="POST" action="pilih_jadwal.php">
        <div class="mb-3">
            <label class="form-label">Poli Tujuan</label>
            <select name="poli_id" class="form-select" required>
                <option value="">-- Pilih Poli --</option>
                <?php while ($row = $poli->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nama_poli']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success w-100 mt-2">Lihat Jadwal Dokter</button>
    </form>
</div>

</body>
</html>
