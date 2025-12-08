<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

// hapus pasien
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

$pasien = $konek->query("SELECT * FROM pasien $where ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pasien - Beranda Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            background: #eef4ff;
        }

        /* NAVBAR soft blue */
        .navbar {
            background: #dce7ff !important;
            border-bottom: 2px solid #b7ccff;
        }

        .navbar-brand {
            font-weight: 600;
            color: #3b4f99 !important;
        }

        /* Tombol Blue (Dashboard & Logout) */
        .btn-blue {
            background: #6a8dff;
            color: white !important;
            border: none;
            border-radius: 10px;
            padding: 7px 16px;
            font-weight: 500;
        }

        .btn-blue:hover {
            background: #5978e6;
        }

        /* Card soft blue */
        .card {
            border: none;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .card-title {
            color: #3b4f99;
            font-weight: 600;
        }

        /* Tabel soft */
        table thead {
            background: #e7efff;
            color: #2d3e80;
        }

        .table-hover tbody tr:hover {
            background: #f0f5ff;
        }

        /* Tombol hapus */
        .btn-outline-danger {
            border-radius: 8px;
            padding: 3px 10px;
        }

        /* Search input */
        input.form-control {
            border-radius: 10px;
            border: 1px solid #a9bffc;
        }
    </style>

</head>
<body>

<div class="container-fluid">
    <nav class="navbar navbar-light py-3">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-hospital-user me-2"></i> Kelola Pasien
            </span>
            <div class="d-flex align-items-center">
                <a href="dashboard_admin.php" class="btn btn-blue btn-sm me-2 rounded-pill">
                    <i class="fa-solid fa-house me-1"></i> Overview
                </a>
                <a href="logout.php" class="btn btn-blue btn-sm rounded-pill">
                    <i class="fa-solid fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form class="row g-2 align-items-center" method="GET">
                    <div class="col-sm-4">
                        <input type="text" name="cari" class="form-control"
                               placeholder="Cari nama / No RM / email"
                               value="<?= htmlspecialchars($cari); ?>">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm rounded-pill" type="submit">
                            <i class="fa-solid fa-search me-1"></i> Cari
                        </button>
                    </div>
                    <?php if ($cari !== ""): ?>
                        <div class="col-auto">
                            <a href="kelola_pasien.php" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
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
                            <thead>
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
                                    <tr>
                                        <td><?= htmlspecialchars($p["no_rm"]); ?></td>
                                        <td><?= htmlspecialchars($p["nama"]); ?></td>
                                        <td><?= htmlspecialchars($p["email"]); ?></td>
                                        <td><?= htmlspecialchars($p["jenis_kelamin"]); ?></td>
                                        <td><?= htmlspecialchars($p["no_hp"]); ?></td>
                                        <td><?= htmlspecialchars($p["alamat"]); ?></td>
                                        <td>
                                            <a href="kelola_pasien.php?hapus=<?= $p["id"]; ?>"
                                               class="btn btn-sm btn-outline-danger"
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
