<?php
// require "auth_pasien.php" sudah memiliki session_start() di dalamnya
require "auth_pasien.php"; 
require "koneksi.php"; 

$id_pasien = $_SESSION["id_pasien"] ?? 0;
$today = date('Y-m-d');
// Mengambil poli dari GET parameter, atau POST jika terjadi error
$id_poli_terpilih = $_GET['poli'] ?? ($_POST['poli_id_selected'] ?? null); 
$nama_poli_terpilih = '-- Pilih Poli --';
$error_msg = null;

// Query untuk mengambil daftar Poli
$query_poli = "SELECT id, nama_poli FROM poli ORDER BY nama_poli ASC";
$result_poli = $konek->query($query_poli);

// -----------------------------------------------------
// LOGIKA PENGAMBILAN ANTRIAN (POST)
// -----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_jadwal_pilihan']) && isset($_POST['keluhan'])) {
    $id_jadwal = intval($_POST['id_jadwal_pilihan']);
    $keluhan = $konek->real_escape_string($_POST['keluhan']);
    
    // Simpan poli yang dipilih saat POST untuk memastikan jadwal tetap ditampilkan jika gagal
    $id_poli_terpilih = intval($_POST['poli_id_selected']); 

    if (!$id_jadwal || empty($keluhan)) {
         $error_msg = "Mohon pilih jadwal dan isi keluhan.";
    } else {
        // 1. Ambil ID Dokter dan ID Poli dari Jadwal yang dipilih
        // 🚨 PERBAIKAN QUERY 1: Mengambil poli_id dari tabel jadwal_dokter (jd)
        $sql_data_dokter = $konek->query("
            SELECT 
                d.id AS dokter_id, 
                jd.poli_id /* ⬅️ DIUBAH dari d.poli_id menjadi jd.poli_id */
            FROM jadwal_dokter jd
            JOIN dokter d ON d.id = jd.dokter_id
            WHERE jd.id = $id_jadwal
        ")->fetch_assoc();

        if (!$sql_data_dokter) {
            $error_msg = "Jadwal dokter tidak valid.";
        } else {
            $id_dokter = $sql_data_dokter['dokter_id'];
            $poli_id = $sql_data_dokter['poli_id']; // Menggunakan alias 'poli_id' dari query di atas

            // 2. Ambil nomor antrian terakhir HARI INI untuk poli ini
            $last = $konek->query("
                SELECT MAX(nomor) AS nomor 
                FROM antrian 
                WHERE poli_id = $poli_id 
                AND DATE(waktu_daftar) = '$today'
            ")->fetch_assoc();
            $next_nomor = ($last["nomor"] ?? 0) + 1;

            // 3. Masukkan antrian baru
            $query_insert = "
                INSERT INTO antrian (pasien_id, dokter_id, poli_id, nomor, keluhan, status, waktu_daftar)
                VALUES ($id_pasien, $id_dokter, $poli_id, $next_nomor, '$keluhan', 'Menunggu', NOW())
            ";

            if ($konek->query($query_insert)) {
                echo "<script>alert('Antrian Anda berhasil diambil! Nomor antrian: {$next_nomor}'); window.location.href='antrian_saya.php';</script>";
                exit;
            } else {
                $error_msg = "Gagal mengambil antrian: " . $konek->error;
            }
        }
    }
}

// -----------------------------------------------------
// LOGIKA TAMPIL JADWAL (GET/Setelah POST Gagal)
// -----------------------------------------------------
$result_jadwal = null;
if ($id_poli_terpilih) {
    $id_poli_terpilih = intval($id_poli_terpilih);
    
    // Ambil nama poli
    $p = $konek->query("SELECT nama_poli FROM poli WHERE id = $id_poli_terpilih")->fetch_assoc();
    if ($p) {
        $nama_poli_terpilih = $p['nama_poli'];
    }

    // Query untuk mengambil jadwal dokter berdasarkan poli yang dipilih
    // 🚨 PERBAIKAN QUERY 2: Menggunakan jd.poli_id di klausa WHERE
    $query_jadwal = "
        SELECT 
            jd.id AS id_jadwal,
            d.id AS id_dokter,
            d.nama AS nama_dokter,
            jd.hari,
            jd.jam_mulai,
            jd.jam_selesai
        FROM jadwal_dokter jd
        JOIN dokter d ON d.id = jd.dokter_id
        WHERE jd.poli_id = $id_poli_terpilih /* ⬅️ DIUBAH dari d.poli_id menjadi jd.poli_id */
        ORDER BY FIELD(jd.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), jd.jam_mulai
    ";
    $result_jadwal = $konek->query($query_jadwal);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Poli & Ambil Antrian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container-box { background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); width: 95%; max-width: 700px; position: relative; }
        h2 { text-align: center; color: #333; margin-bottom: 25px; }
        .poli-select-group { display: flex; gap: 10px; margin-bottom: 20px; }
        .poli-select-group select { flex-grow: 1; }
        .selected-row { background-color: #ffc10740; } 
    </style>
</head>
<body>

    <div class="container-box">
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#konfirmasiModal">
                Kembali
            </button>
        </div>

        <h2>Sistem Pendaftaran Online Pasien</h2>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?= $error_msg ?></div>
        <?php endif; ?>

        <form method="GET" action="pilih_poli.php" class="mb-4">
            <div class="poli-select-group">
                <select name="poli" id="poli-select" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Pilih Poli --</option>
                    <?php 
                    if ($result_poli && $result_poli->num_rows > 0) {
                        while ($row = $result_poli->fetch_assoc()) {
                            $selected = ($row['id'] == $id_poli_terpilih) ? 'selected' : '';
                            echo "<option value='{$row['id']}' {$selected}>" . htmlspecialchars($row['nama_poli']) . "</option>";
                        }
                    }
                    ?>
                </select>
                <select disabled class="form-select">
                    <option>Poli Dipilih: <?= htmlspecialchars($nama_poli_terpilih) ?></option>
                </select>
            </div>
        </form>

        <?php if ($id_poli_terpilih && isset($result_jadwal)): ?>
            <h3 class="mb-3">Jadwal Dokter di <?= htmlspecialchars($nama_poli_terpilih) ?></h3>
            
            <form method="POST" action="pilih_poli.php">
                <input type="hidden" name="id_jadwal_pilihan" id="id-jadwal-terpilih" value="" required> 
                <input type="hidden" name="poli_id_selected" value="<?= $id_poli_terpilih ?>">

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Dokter</th>
                                <th>Hari</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result_jadwal && $result_jadwal->num_rows > 0) {
                                while ($row = $result_jadwal->fetch_assoc()) {
                                    $jam_mulai = substr($row['jam_mulai'], 0, 5);
                                    $jam_selesai = substr($row['jam_selesai'], 0, 5);
                                    
                                    echo "<tr data-id-jadwal='{$row['id_jadwal']}' id='row-{$row['id_jadwal']}'>";
                                    echo "<td>" . htmlspecialchars($row['nama_dokter']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['hari']) . "</td>";
                                    echo "<td>{$jam_mulai}</td>";
                                    echo "<td>{$jam_selesai}</td>";
                                    echo "<td><button type='button' class='btn btn-sm btn-success w-100' onclick='pilihJadwal({$row['id_jadwal']})'>Ambil Antrian</button></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>Tidak ada jadwal dokter tersedia di poli ini.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="mb-4">
                    <label for="keluhan" class="form-label">Keluhan Singkat</label>
                    <input type="text" id="keluhan" name="keluhan" class="form-control" placeholder="Cth: Demam tinggi sejak 2 hari" required>
                    <small class="form-text text-muted">Diperlukan untuk proses pendaftaran antrian.</small>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 konfirmasi-btn">Konfirmam & Ambil Antrian</button>
            </form>
        <?php else: ?>
            <div class="alert alert-info text-center">Silakan Pilih Poli yang Anda tuju pada dropdown di atas.</div>
        <?php endif; ?>

    </div>

    <div class="modal fade" id="konfirmasiModal" tabindex="-1" aria-labelledby="konfirmasiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="konfirmasiModalLabel">Konfirmasi Keluar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin keluar dari halaman ini? Data yang belum Anda konfirmasi akan hilang.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak, Batalkan</button>
                    <a href="dashboard_pasien.php" class="btn btn-danger">Ya, Keluar</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript untuk menandai baris yang dipilih dan mengisi ID Jadwal
        let idJadwalTerpilih = document.getElementById('id-jadwal-terpilih');
        let form = document.querySelector('form[method="POST"]');

        function pilihJadwal(id_jadwal) {
            // Hapus status terpilih dari semua baris
            document.querySelectorAll('tbody tr').forEach(tr => {
                tr.classList.remove('selected-row');
            });
            
            // Atur status terpilih pada baris yang diklik
            const rowTerpilih = document.getElementById(`row-${id_jadwal}`);
            rowTerpilih.classList.add('selected-row');
            
            // Update input hidden untuk form PHP
            idJadwalTerpilih.value = id_jadwal;

            console.log(`Jadwal ID ${id_jadwal} telah dipilih.`);
        }

        // Validasi tambahan sebelum submit form POST
        if (form) {
            form.addEventListener('submit', function(event) {
                if (idJadwalTerpilih.value === '') {
                    event.preventDefault();
                    alert('Mohon pilih salah satu jadwal dokter terlebih dahulu sebelum konfirmasi.');
                }
            });
        }
    </script>

</body>
</html>