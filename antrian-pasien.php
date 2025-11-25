<?php
// ========== antrian-pasien.php (Versi Revisi - Menggunakan Tabel Pemeriksaan) ==========
include 'header.php'; // sudah memuat db.php, koneksi PDO, dan $dokterId

// 1) Ambil Poli Dokter yang sedang login
$stDokter = $pdo->prepare("SELECT poli FROM dokter WHERE id=?");
$stDokter->execute([$dokterId]);
$poliDokter = $stDokter->fetchColumn();

// 2) Proses aksi update status (Panggil / Selesai / Ulang)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['aksi'])) {
  // id di sini adalah ID dari tabel pemeriksaan
  if (!ctype_digit($_POST['id'])) {
    header("Location: antrian-pasien.php"); exit;
  }
  $pemeriksaanId  = (int) $_POST['id'];
  $aksi = $_POST['aksi'];

  if ($aksi === 'panggil') {
    // Update status di tabel pemeriksaan
    $pdo->prepare("UPDATE pemeriksaan SET status='Dipanggil' WHERE id=? AND jenis_pemeriksaan=? AND tanggal_pemeriksaan=CURDATE()")
        ->execute([$pemeriksaanId, $poliDokter]);
  } elseif ($aksi === 'selesai') {
    // Update status di tabel pemeriksaan
    $pdo->prepare("UPDATE pemeriksaan SET status='Selesai' WHERE id=? AND jenis_pemeriksaan=? AND tanggal_pemeriksaan=CURDATE()")
        ->execute([$pemeriksaanId, $poliDokter]);
  } elseif ($aksi === 'ulang') {
    // Update status di tabel pemeriksaan
    $pdo->prepare("UPDATE pemeriksaan SET status='Menunggu' WHERE id=? AND jenis_pemeriksaan=? AND tanggal_pemeriksaan=CURDATE()")
        ->execute([$pemeriksaanId, $poliDokter]);
  }
  header("Location: antrian-pasien.php"); exit;
}


// 3) Ambil data pendaftaran (antrian) HANYA untuk poli dokter ini hari ini
$sql = "
    SELECT 
        p.id AS pemeriksaan_id, 
        pa.nama, 
        pa.no_rm,
        p.status
    FROM pemeriksaan p
    JOIN pasien pa ON p.id_pasien = pa.id
    WHERE p.jenis_pemeriksaan = ? 
      AND p.tanggal_pemeriksaan = CURDATE()
    ORDER BY p.id ASC
";
$st = $pdo->prepare($sql);
$st->execute([$poliDokter]);
$rows = $st->fetchAll();
?>

<section class="content-card">
  <div class="card-header">
    <span class="material-icons-sharp">group</span>
    <h2>Antrian Pendaftaran Hari Ini (Poli: <?= htmlspecialchars($poliDokter) ?>)</h2>
  </div>
  
  <div class="queue-list">
    <?php if (empty($rows)): ?>
      <div class="empty-message">
        Tidak ada pendaftaran pemeriksaan untuk Poli <?= htmlspecialchars($poliDokter) ?> hari ini.
      </div>
    <?php endif; ?>

    <?php $nomor_antrian = 0; ?>
    <?php foreach ($rows as $r): ?>
      <?php $nomor_antrian++; // Nomor antrian dihitung berdasarkan urutan pendaftaran ?>
      <div class="queue-item">
        <div class="queue-info">
          <span class="queue-number">No. <?= $nomor_antrian ?></span>
          <span class="patient-name"><?= htmlspecialchars($r['nama']) ?> (RM: <?= htmlspecialchars($r['no_rm'] ?? '-') ?>)</span>
        </div>

        <div class="d-flex align-items-center" style="gap:.5rem">
          <div class="queue-status me-3">
             <span class="status-text 
                <?php if ($r['status'] === 'Dipanggil') echo 'text-primary';
                      elseif ($r['status'] === 'Selesai') echo 'text-success';
                      else echo 'text-warning';
                ?>"
             >
                <?= htmlspecialchars($r['status']) ?>
             </span>
          </div>
          
          <?php if ($r['status'] === 'Menunggu'): ?>
            <form method="post">
              <input type="hidden" name="id" value="<?= (int)$r['pemeriksaan_id'] ?>">
              <button class="queue-btn btn-panggil" name="aksi" value="panggil">Panggil</button>
            </form>
          <?php elseif ($r['status'] === 'Dipanggil'): ?>
            <form method="post">
              <input type="hidden" name="id" value="<?= (int)$r['pemeriksaan_id'] ?>">
              <button class="queue-btn btn-selesai" name="aksi" value="selesai">Selesai</button>
            </form>
          <?php else: // Status Selesai/Lainnya ?>
            <form method="post">
              <input type="hidden" name="id" value="<?= (int)$r['pemeriksaan_id'] ?>">
              <button class="status-link" name="aksi" value="ulang">Ulangi Pindai</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'footer.php'; ?>