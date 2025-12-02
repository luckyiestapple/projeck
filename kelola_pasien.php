<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

// hapus pasien (opsional)
if (isset($_GET["hapus"])) {
    $id_hapus = (int) $_GET["hapus"];
    $konek->query("DELETE FROM pasien WHERE id = $id_hapus");
    header("Location: kelola_pasien.php");
    exit;
}

$cari = trim($_GET["cari"] ?? "");
$where = "";
if ($cari !== "") {
    $c = $konek->real_escape_string($cari);
    $where = "WHERE nama LIKE '%$c%' OR no_rm LIKE '%$c%' OR email LIKE '%$c%'";
}

// ambil pasien
$pasien = $konek->query("SELECT * FROM pasien $where ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pasien - Beranda Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styleadmin.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="container-fluid">
    <nav class="navbar navbar-light bg-light py-3 border-bottom">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-hospital-user me-2"></i> Kelola Pasien
            </span>
            <div class="d-flex align-items-center">
                <a href="dashboard_admin.php" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="fa-solid fa-house"></i> Overview
                </a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="fa-solid fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form class="row g-2 align-items-center" method="GET">
                    <div class="col-sm-4">
                        <input type="text" name="cari" class="form-control" placeholder="Cari nama / No RM / email"
                               value="<?= htmlspecialchars($cari); ?>">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm" type="submit">
                            <i class="fa-solid fa-search me-1"></i> Cari
                        </button>
                    </div>
                    <?php if ($cari !== ""): ?>
                        <div class="col-auto">
                            <a href="kelola_pasien.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Daftar Pasien</h5>

                <?php if ($pasien->num_rows == 0): ?>
                    <p class="text-muted mb-0">Tidak ada data pasien.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No RM</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>JK</th>
                                    <th>No HP</th>
                                    <th>Alamat</th>
                                    <th style="width:90px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($p = $pasien->fetch_assoc()): ?>
                                    <tr class="patient-item">
                                        <td><?= htmlspecialchars($p["no_rm"]); ?></td>
                                        <td><?= htmlspecialchars($p["nama"]); ?></td>
                                        <td><?= htmlspecialchars($p["email"]); ?></td>
                                        <td><?= htmlspecialchars($p["jenis_kelamin"]); ?></td>
                                        <td><?= htmlspecialchars($p["no_hp"]); ?></td>
                                        <td><?= htmlspecialchars($p["alamat"]); ?></td>
                                        <td>
                                            <a href="kelola_pasien.php?hapus=<?= $p["id"]; ?>"
                                               class="btn btn-sm btn-outline-danger detail-btn"
                                               onclick="return confirm('Yakin hapus pasien ini?')">
                                                Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
