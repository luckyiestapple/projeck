<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "dokter") {
    header("Location: login.php");
    exit;
}

$id_dokter = $_SESSION["id_dokter"];

// Ambil daftar pasien
$pasien = $konek->query("SELECT id, nama, no_rm FROM pasien ORDER BY nama ASC");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_pasien = $_POST["id_pasien"];
    $diagnosis = $_POST["diagnosis"];
    $catatan   = $_POST["catatan"];

    if ($id_pasien !== "" && $diagnosis !== "") {

        $stmt = $konek->prepare("
            INSERT INTO rekam_medis (id_pasien, id_dokter, tanggal_periksa, diagnosis, catatan)
            VALUES (?, ?, CURDATE(), ?, ?)
        ");
        $stmt->bind_param("iiss", $id_pasien, $id_dokter, $diagnosis, $catatan);
        $stmt->execute();

        header("Location: rekam.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Rekam Medis</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
    <a class="navbar-brand" href="dashboard_dokter.php">Panel Dokter</a>
</nav>

<div class="container py-4">
    <h3>Tambah Rekam Medis</h3>

    <form method="POST" class="mt-3">

        <div class="mb-3">
            <label>Pasien</label>
            <select name="id_pasien" class="form-control" required>
                <option value="">-- Pilih Pasien --</option>
                <?php while ($p = $pasien->fetch_assoc()): ?>
                    <option value="<?= $p["id"] ?>">
                        <?= htmlspecialchars($p["nama"]) ?> (<?= $p["no_rm"] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Diagnosis</label>
            <textarea name="diagnosis" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label>Catatan</label>
            <textarea name="catatan" class="form-control"></textarea>
        </div>

        <button class="btn btn-success">Simpan</button>
        <a href="rekam.php" class="btn btn-secondary">Batal</a>

    </form>
</div>

</body>
</html>
