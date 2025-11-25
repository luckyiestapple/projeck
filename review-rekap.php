<?php 
// ========== review-rekap.php (VERSI BOOTSTRAP FINAL) ==========
include 'header.php';

// Query untuk mengambil data rekap harian
$st = $pdo->prepare("SELECT DATE_FORMAT(tanggal,'%d %b %Y') tgl, pasien_ditangani, rata_waktu_menit, total_jam_menit 
                     FROM rekap_harian WHERE dokter_id=? ORDER BY tanggal DESC");
$st->execute([$dokterId]);
$rows = $st->fetchAll();
?>

<section class="card shadow-sm">
  <div class="card-header bg-white d-flex align-items-center">
    <span class="material-icons-sharp me-2 text-primary fs-4">description</span>
    <h2 class="h5 mb-0">Rekap Harian Dokter</h2>
  </div>
  
  <div class="list-group list-group-flush">
    <?php if (!$rows): ?>
        <div class="list-group-item text-center text-muted py-4">
            <span class="material-icons-sharp d-block mb-2 text-warning fs-1">info</span>
            Belum ada data rekap harian yang terdaftar.
            <p class="mt-2 small text-muted">Pastikan Anda sudah menekan tombol **"Selesai Praktik"** untuk memicu pembuatan rekap harian.</p>
        </div>
    <?php endif; ?>
    
    <?php foreach($rows as $r): ?>
      <div class="list-group-item">
        <h6 class="text-primary mb-3 fw-bold"><?= htmlspecialchars($r['tgl']) ?></h6>
        
        <div class="row g-3">
          
          <div class="col-md-4">
            <div class="card p-3 border-secondary-subtle bg-light">
              <span class="text-muted small mb-1">Pasien Ditangani</span>
              <span class="fw-bold fs-4"><?= (int)$r['pasien_ditangani'] ?></span>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card p-3 border-secondary-subtle bg-light">
              <span class="text-muted small mb-1">Rata-rata Waktu</span>
              <span class="fw-bold fs-4"><?= (int)$r['rata_waktu_menit'] ?> <small class="fw-normal text-muted">menit</small></span>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card p-3 border-secondary-subtle bg-light">
              <span class="text-muted small mb-1">Total Jam Praktik</span>
              <span class="fw-bold fs-4">
                <?php 
                  // Konversi menit ke jam (pembulatan 1 desimal)
                  $total_jam = round(($r['total_jam_menit'] / 60), 1);
                  echo number_format($total_jam, 1);
                ?> 
                <small class="fw-normal text-muted">jam</small>
              </span>
            </div>
          </div>
          
        </div> </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'footer.php'; ?>