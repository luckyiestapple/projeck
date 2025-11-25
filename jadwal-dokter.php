<?php 
// ========== jadwal-dokter.php (VERSI BOOTSTRAP) ==========
include 'header.php';

// ambil poli sekali dari tabel dokter
$dok = $pdo->prepare("SELECT poli FROM dokter WHERE id=?");
$dok->execute([$dokterId]);
$poliDokter = $dok->fetch()['poli'] ?? '-';

// ambil jadwal
$st = $pdo->prepare("\r\n  SELECT hari, TIME_FORMAT(jam_mulai,'%H:%i') jm, TIME_FORMAT(jam_selesai,'%H:%i') js\r\n  FROM jadwal_dokter\r\n  WHERE dokter_id=?\r\n  ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), jam_mulai\r\n");
$st->execute([$dokterId]);
$rows = $st->fetchAll();
?>

<section class="card shadow-sm">
  <div class="card-header bg-white d-flex align-items-center">
    <span class="material-icons-sharp me-2 text-primary fs-4">calendar_today</span>
    <h2 class="h5 mb-0">Jadwal Praktik</h2>
  </div>
  
  <div class="list-group list-group-flush">
    <?php if (!$rows): ?>
        <div class="list-group-item text-center text-muted py-4">
            Tidak ada jadwal praktik yang terdaftar.
        </div>
    <?php endif; ?>

    <?php foreach($rows as $r): ?>
      <div class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <span class="d-block fw-bold"><?= htmlspecialchars($r['hari']) ?></span>
          <span class="text-muted small"><?= htmlspecialchars($r['jm'].' - '.$r['js']) ?> WIB</span>
        </div>
        <div>
          <span class="badge text-bg-info text-dark fw-normal"><?= htmlspecialchars($poliDokter) ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'footer.php'; ?>