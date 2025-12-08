<?php
session_start();
require "koneksi.php";

// Pastikan hanya dokter yang bisa mengakses
require "auth_dokter.php";

$id_dokter = $_SESSION["id_dokter"];
$hasil_cari = null;
$error = "";
$mode = 'search'; 
$pasien_detail = null; // Data Pasien yang dipilih
$sql_rekam_medis = null; // Riwayat RM pasien

// --- 1. Ambil Data Pasien Detail jika pasien_id ada di URL ---
$pasien_id = (int) ($_GET['pasien_id'] ?? 0);
if ($pasien_id > 0) {
    $mode = 'detail';
    
    // Ambil data pasien yang dipilih
    $pasien_detail = $konek->query("SELECT * FROM pasien WHERE id = $pasien_id")->fetch_assoc();
    
    // Ambil semua riwayat Rekam Medis pasien tersebut.
    // DENGAN PERBAIKAN: Hapus JOIN ke tabel poli (untuk mengatasi error 'd.poli_id')
    $sql_rekam_medis = $konek->query("
        SELECT 
            rm.*, 
            d.nama AS nama_dokter
        FROM rekam_medis rm
        LEFT JOIN dokter d ON d.id = rm.id_dokter
        -- LEFT JOIN poli pl ON pl.id = d.poli_id <--- BARIS INI DIHAPUS
        WHERE rm.id_pasien = $pasien_id
        ORDER BY rm.tanggal_periksa DESC
    ");
    
    if (!$pasien_detail) {
        $error = "Pasien tidak ditemukan. Kembali ke mode Pencarian.";
        $mode = 'search'; 
    }

// --- 2. Proses Pencarian Pasien jika ada POST request ---
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $keyword = trim($_POST['keyword'] ?? '');

    if (empty($keyword)) {
        $error = "Mohon masukkan Nomor RM atau Nama Pasien.";
    } else {
        // Cari pasien berdasarkan No. RM atau Nama
        $sql = $konek->prepare("
            SELECT id, no_rm, nama, alamat, no_hp 
            FROM pasien 
            WHERE no_rm = ? OR nama LIKE ?
            ORDER BY nama ASC
            LIMIT 10
        ");
        
        $keyword_like = "%" . $keyword . "%";
        $sql->bind_param("ss", $keyword, $keyword_like);
        $sql->execute();
        $hasil_cari = $sql->get_result();
        
        if ($hasil_cari->num_rows == 0) {
            $error = "Pasien dengan Nomor RM/Nama: '" . htmlspecialchars($keyword) . "' tidak ditemukan.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekam Medis Pasien (Dokter)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background:#f8f9fa; }
        .card { border-radius: 10px; box-shadow: 0 4px 12px #0d6efd(0, 0, 0, 0.08); }
        .table-custom-header { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4 shadow-sm">
    <a class="navbar-brand" href="dashboard_dokter.php">
        <i class="fas fa-notes-medical me-2"></i> Rekam Medis Dokter
    </a>
    <div class="ms-auto">
        <a href="dashboard_dokter.php" class="btn btn-sm btn-outline-light">
            <i class="fas fa-sign-out-alt me-1"></i> Keluar
        </a>
    </div>
</nav>

<div class="container py-5">
    <h3 class="mb-4">
        <i class="fas fa-search me-2 text-primary"></i> Pencarian Rekam Medis
    </h3>

    <div class="card p-4 mb-4">
        <h5 class="card-title mb-3">Cari Pasien</h5>
        <form method="POST" class="row g-3 align-items-center">
            <div class="col-md-9 col-lg-10">
                <input type="text" name="keyword" class="form-control" 
                       placeholder="Masukkan Nomor RM atau Nama Pasien" 
                       value="<?= htmlspecialchars($_POST['keyword'] ?? '') ?>" required>
            </div>
            <div class="col-md-3 col-lg-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Cari
                </button>
            </div>
            <small class="text-muted mt-2">Masukkan Nomor RM atau Nama pasien untuk melihat riwayat rekam medis.</small>
        </form>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($hasil_cari && $hasil_cari->num_rows > 0): ?>
        <h4 class="mt-4">Hasil Pencarian Pasien (<?= $hasil_cari->num_rows ?> ditemukan)</h4>
        <div class="card p-4 mt-3">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>No. RM</th>
                            <th>Nama Pasien</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $hasil_cari->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['no_rm']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars(substr($row['alamat'], 0, 40)) . (strlen($row['alamat']) > 40 ? '...' : '') ?></td>
                            <td>
                                <a href="cari_rekam_medis.php?pasien_id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-file-medical me-1"></i> Lihat RM
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($mode === 'detail' && $pasien_detail): ?>
        
        <hr class="my-5">
        
        <h4 class="mb-4 text-dark">
            <i class="fas fa-history me-2 text-info"></i> Riwayat Rekam Medis: 
            <?= htmlspecialchars($pasien_detail['nama']) ?> (No. RM: <?= htmlspecialchars($pasien_detail['no_rm']) ?>)
        </h4>

        <div class="card p-4">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th></th>
                            <th>Tanggal</th>
                            <th>Dokter</th>
                            <th>Diagnosis (Ringkasan)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($sql_rekam_medis->num_rows == 0): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted p-4">
                                    Tidak ada riwayat rekam medis untuk pasien ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; while ($rm = $sql_rekam_medis->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars(date('d F Y', strtotime($rm["tanggal_periksa"]))) ?></td>
                                <td>
                                    <?= htmlspecialchars($rm["nama_dokter"]) ?> 
                                    </td>
                                <td><?= htmlspecialchars(substr($rm["diagnosis"], 0, 50)) . (strlen($rm["diagnosis"]) > 50 ? '...' : '') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailModal" 
                                            data-tanggal="<?= htmlspecialchars(date('d F Y', strtotime($rm["tanggal_periksa"]))) ?>"
                                            data-dokter="<?= htmlspecialchars($rm["nama_dokter"]) ?>"
                                            data-poli="-" data-diagnosis="<?= htmlspecialchars($rm["diagnosis"]) ?>"
                                            data-catatan="<?= htmlspecialchars($rm["catatan"]) ?>">
                                        <i class="fas fa-magnifying-glass"></i> Lihat Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="detailModalLabel">Detail Rekam Medis Pasien</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <p class="mb-1"><strong>Tanggal Periksa:</strong> <span id="modal-tanggal" class="fw-bold"></span></p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Dokter:</strong> <span id="modal-dokter"></span></p>
            </div>
        </div>
        <hr>
        <h6><i class="fas fa-file-waveform me-1"></i> Diagnosis:</h6>
        <p><span id="modal-diagnosis" class="fw-bold text-danger"></span></p>
        <h6><i class="fas fa-book-medical me-1"></i> Catatan Dokter:</h6>
        <p style="white-space: pre-wrap; background:#f4f4f4; padding:10px; border-radius:5px;"><span id="modal-catatan"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script untuk mengisi data modal pada mode Detail
    var detailModal = document.getElementById('detailModal')
    if (detailModal) {
        detailModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var tanggal = button.getAttribute('data-tanggal')
            var dokter = button.getAttribute('data-dokter')
            // var poli = button.getAttribute('data-poli') // Poli dihapus
            var diagnosis = button.getAttribute('data-diagnosis')
            var catatan = button.getAttribute('data-catatan')

            detailModal.querySelector('#modal-tanggal').textContent = tanggal
            detailModal.querySelector('#modal-dokter').textContent = dokter
            // Jika Anda ingin mempertahankan elemen Poli:
            // detailModal.querySelector('#modal-poli').textContent = poli 
            detailModal.querySelector('#modal-diagnosis').textContent = diagnosis
            detailModal.querySelector('#modal-catatan').textContent = catatan
        })
    }
</script>
</body>
</html>