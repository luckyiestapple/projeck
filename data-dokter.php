<?php
// ========== data-dokter.php (VERSI BOOTSTRAP) ==========
include 'header.php'; // header + db.php + $dokterId

// Ambil data dokter yang sedang login
$st = $pdo->prepare("SELECT nama, spesialisasi, poli, email, sip FROM dokter WHERE id=?");
$st->execute([$dokterId]);
$d = $st->fetch();
?>

<section class="card shadow-sm">
  <div class="card-header bg-white d-flex align-items-center">
    <span class="material-icons-sharp me-2 text-primary fs-4">person_outline</span>
    <h2 class="h5 mb-0">Data Dokter</h2>
  </div>

  <div class="card-body">
    <div class="row row-cols-1 row-cols-md-2 g-4">
      
      <div class="col">
        <div class="list-group list-group-flush border rounded-3 bg-light">
          <div class="list-group-item">
            <span class="label text-muted small d-block">Nama Lengkap</span>
            <span class="value fw-bold"><?= htmlspecialchars($d['nama'] ?? '-') ?></span>
          </div>
          <div class="list-group-item">
            <span class="label text-muted small d-block">Spesialisasi</span>
            <span class="value fw-bold"><?= htmlspecialchars($d['spesialisasi'] ?? '-') ?></span>
          </div>
          <div class="list-group-item">
            <span class="label text-muted small d-block">Poli</span>
            <span class="value fw-bold"><?= htmlspecialchars($d['poli'] ?? '-') ?></span>
          </div>
        </div>
      </div>

      <div class="col">
        <div class="list-group list-group-flush border rounded-3 bg-light">
          <div class="list-group-item">
            <span class="label text-muted small d-block">Email</span>
            <span class="value fw-bold"><?= htmlspecialchars($d['email'] ?? '-') ?></span>
          </div>
          <div class="list-group-item">
            <span class="label text-muted small d-block">Nomor SIP</span>
            <span class="value fw-bold"><?= htmlspecialchars($d['sip'] ?? '-') ?></span>
          </div>
        </div>
      </div>
      
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>