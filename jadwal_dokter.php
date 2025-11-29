<?php
/**
 * File: jadwal_dokter.php (MODIFIKASI: ALL-IN-ONE)
 * Halaman ini menampilkan semua Poli, Jadwal Dokter, dan memproses Ambil Antrian.
 */
session_start();
require "koneksi.php"; // Pastikan koneksi.php sudah dibuat

// 1. Otorisasi Pasien
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "pasien") {
    header("Location: login.php");
    exit;
}

$id_pasien = $_SESSION["id_pasien"];
$msg = "";
$error = "";

// Ambil semua data Poli untuk menu pilihan
$allPoli = $konek->query("SELECT id, nama_poli FROM poli ORDER BY nama_poli ASC");

// Tentukan Poli yang dipilih dari GET
$poli_id = isset($_GET["poli"]) ? intval($_GET["poli"]) : null;

// --- 2. PROSES AMBIL ANTRIAN (Hanya saat form POST/link GET dipicu) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ambil_antrian"]) || isset($_GET["ambil"])) {
    
    // Gunakan GET untuk data antrian (lebih mudah dari link)
    $jadwal_id = $_GET["jadwal"] ?? $_POST["jadwal_id"];
    $poli_id   = $_GET["poli"] ?? $_POST["poli_id"];
    
    if ($jadwal_id && $poli_id) {
        
        // Dapatkan dokter_id dari jadwal_id
        $sqlJadwal = $konek->query("SELECT dokter_id FROM jadwal_dokter WHERE id = " . intval($jadwal_id));
        $dataJadwal = $sqlJadwal->fetch_assoc();
        $id_dokter = $dataJadwal["dokter_id"] ?? NULL;

        if ($id_dokter) {
            $today = date('Y-m-d');
            
            // Cek apakah pasien sudah punya antrian aktif hari ini untuk poli ini
            $cekAntrian = $konek->query("
                SELECT * FROM antrian 
                WHERE pasien_id = $id_pasien 
                AND poli_id = $poli_id 
                AND DATE(waktu_daftar) = '$today'
                AND status IN ('Menunggu', 'Dipanggil')
            ");

            if ($cekAntrian->num_rows > 0) {
                $error = "Anda sudah memiliki antrian aktif di Poli ini hari ini.";
            } else {
                // Hitung nomor antrian terakhir HARI INI
                $last = $konek->query("
                    SELECT MAX(nomor) AS nomor 
                    FROM antrian 
                    WHERE poli_id = $poli_id AND DATE(waktu_daftar) = '$today'
                ")->fetch_assoc();
                $next = ($last["nomor"] ?? 0) + 1;

                // Ambil nama pasien
                $p = $konek->query("SELECT nama FROM pasien WHERE id = $id_pasien")->fetch_assoc();
                $nama_pasien = $konek->real_escape_string($p["nama"]);

                // Insert antrian baru
                $konek->query("
                    INSERT INTO antrian (nomor, pasien_id, nama_pasien, dokter_id, poli_id, status, waktu_daftar)
                    VALUES ($next, $id_pasien, '$nama_pasien', $id_dokter, $poli_id, 'Menunggu', NOW())
                ");
                
                // Redirect ke halaman antrian saya setelah berhasil
                header("Location: antrian_saya.php?msg=antri_success");
                exit;
            }
        } else {
            $error = "Jadwal atau Dokter tidak valid.";
        }
    } else {
        $error = "Pilih jadwal yang valid untuk mengambil antrian.";
    }
}
// --- AKHIR PROSES ANTRIAN ---

// --- 3. AMBIL JADWAL UNTUK TAMPILAN ---
$jadwal = null;
$nama_poli = "Pilih Poli di Bawah";
if ($poli_id) {
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
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal & Ambil Antrian</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(to bottom, #e0f2ff, #ffffff); }
        .card-box { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 18px 40px rgba(0,0,0,0.08); }
        .table { border-radius: 10px; overflow: hidden; }
        .btn-green { background: #0d8a3d; color: white; border-radius: 40px; font-weight: 600; padding: 10px 22px; }
        .btn-green:hover { background: #0b6f31; color: white; }
    </style>
</head>

<body>

<div class="card-box">

    <div class="d-flex justify-content-between mb-4">
        <h3 class="fw-bold">Pilih Jadwal & Ambil Antrian</h3>
        <a href="dashboard_pasien.php" class="btn btn-outline-secondary back-btn"><i class="fas fa-home me-1"></i> Dashboard</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="mb-4">
        <label for="poli-select" class="form-label fw-bold">Pilih Poli:</label>
        <select id="poli-select" class="form-select" onchange="window.location.href = 'jadwal_dokter.php?poli=' + this.value;">
            <option value="" disabled selected>--- Pilih Poli Tujuan ---</option>
            <?php while ($p = $allPoli->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>" <?= ($p['id'] == $poli_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama_poli']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <?php if (!$poli_id): ?>
        <div class="alert alert-info text-center">
            Silakan pilih Poli dari daftar di atas untuk melihat jadwal dokter yang tersedia.
        </div>
    <?php else: ?>
        <h4 class="mt-4 mb-3">Jadwal Dokter di **<?= htmlspecialchars($nama_poli) ?>**</h4>

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
                <?php if ($jadwal && $jadwal->num_rows > 0): ?>
                    <?php while ($row = $jadwal->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row["nama_dokter"]) ?></td>
                        <td><?= $row["hari"] ?></td>
                        <td><?= $row["jam_mulai"] ?></td>
                        <td><?= $row["jam_selesai"] ?></td>
                        <td>
                            <a href="jadwal_dokter.php?ambil=1&jadwal=<?= $row['id'] ?>&poli=<?= $poli_id ?>" 
                               class="btn btn-green btn-sm w-100"
                               onclick="return confirm('Apakah Anda yakin ingin mengambil antrian untuk Dokter <?= htmlspecialchars($row["nama_dokter"]) ?> hari ini?');">
                                Ambil Antrian
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted p-3">
                            Belum ada jadwal dokter yang terdaftar untuk Poli <?= htmlspecialchars($nama_poli) ?>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>