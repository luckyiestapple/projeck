<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

if (isset($_GET["hapus"])) {
    $id_hapus = (int) $_GET["hapus"];
    $konek->query("DELETE FROM dokter WHERE id = $id_hapus");
    header("Location: kelola_dokter.php");
    exit;
}

$editData = null;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id        = (int)($_POST["id"] ?? 0);
    $nama      = trim($_POST["nama"] ?? "");
    $spesialis = trim($_POST["spesialisasi"] ?? "");
    $poli      = trim($_POST["poli"] ?? "");
    $email     = trim($_POST["email"] ?? "");
    $sip       = trim($_POST["sip"] ?? "");
    $password  = trim($_POST["password"] ?? "");

    if ($id > 0) {
       
        if ($password !== "") {
            $stmt = $konek->prepare("UPDATE dokter SET nama=?, spesialisasi=?, poli=?, email=?, sip=?, password=? WHERE id=?");
            $stmt->bind_param("ssssssi", $nama, $spesialis, $poli, $email, $sip, $password, $id);
        } else {
            $stmt = $konek->prepare("UPDATE dokter SET nama=?, spesialisasi=?, poli=?, email=?, sip=? WHERE id=?");
            $stmt->bind_param("sssssi", $nama, $spesialis, $poli, $email, $sip, $id);
        }
        $stmt->execute();
        $stmt->close();
    } else {
       
        $stmt = $konek->prepare("INSERT INTO dokter (nama, spesialisasi, poli, email, sip, password) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $nama, $spesialis, $poli, $email, $sip, $password);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: kelola_dokter.php");
    exit;
}

if (isset($_GET["edit"])) {
    $id_edit  = (int) $_GET["edit"];
    $editData = $konek->query("SELECT * FROM dokter WHERE id = $id_edit")->fetch_assoc();
}

$dokter = $konek->query("SELECT * FROM dokter ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Dokter - Beranda Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- ============================
         🎨 STYLE BARU & UNIK
    ============================== -->
   <style>
    body {
        background: #eaf2ff; /* soft blue background */
        font-family: "Inter", sans-serif;
    }

    /* NAVBAR */
    .navbar-custom {
        background: #d7e7ff; /* soft blue navbar */
        border-bottom: 1px solid #b8cff5;
    }

    .navbar-custom .navbar-brand,
    .navbar-custom span,
    .navbar-custom i {
        color: #253b66 !important;
        font-weight: 600;
    }

    .navbar-custom .btn-danger {
        background: #6bdaffff;
        border: none;
    }

    /* SUMMARY CARD */
    .summary-card {
        background: #f0f5ff; /* soft blue card */
        padding: 25px;
        border-radius: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #c8d9ff;
        box-shadow: 0px 4px 12px rgba(140, 170, 230, 0.25);
        transition: 0.2s;
    }

    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0px 8px 20px rgba(120, 150, 220, 0.35);
    }

    .summary-card h6 {
        color: #345087;
    }

    .summary-card .display-4,
    .summary-card .display-5 {
        color: #1f355e;
    }

    /* ICON CIRCLE */
    .icon-lg {
        font-size: 40px;
        padding: 16px;
        border-radius: 50%;
        background: #dce8ff;
        color: #3b6fdc;
    }

    /* MAIN CARD & SIDE CARD */
    .card {
        background: #f3f7ff !important;
        border-radius: 16px;
        border: 1px solid #c6d8ff;
        box-shadow: 0 6px 16px rgba(150, 175, 230, 0.25);
    }

    .card-title {
        color: #1f3a68;
        font-weight: 600;
    }

    /* PROGRESS BAR */
    .progress {
        height: 10px;
        border-radius: 20px;
        background: #d7e4ff;
    }

    .progress-bar.bg-info {
        background: #7da8ff !important;
    }

    .progress-bar.bg-success {
        background: #63d29f !important;
    }

    /* BUTTONS */
    .quick-btn {
        border-radius: 14px;
        padding: 12px;
        font-weight: 600;
        background: #e3edff;
        border: 1px solid #b8cdf9;
        color: #23457a;
        transition: 0.15s;
    }

    .quick-btn:hover {
        background: #d4e2ff;
    }

    .btn-outline-secondary {
        border-radius: 14px;
        background: #e9f1ff;
        border: 1px solid #c7d9ff;
        color: #394b78;
    }

    .btn-outline-secondary:hover {
        background: #d7e5ff;
    }

    /* CATATAN CARD */
    .card-note {
        background: #f2f6ff !important;
        border-color: #c6d8ff;
    }

    .card-note p {
        color: #47608f;
    }
</style>


</head>
<body>

<div class="container-fluid">
    <nav class="navbar navbar-custom py-3 mb-3">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-user-doctor me-2"></i> Kelola Dokter
            </span>
            <div class="d-flex align-items-center">
                <a href="dashboard_admin.php" class="btn btn-light btn-sm me-2">
                    <i class="fa-solid fa-house"></i> Overview
                </a>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <div class="row g-4">
          
            <!-- FORM SIDE -->
            <div class="col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h5 class="card-title mb-3 fw-bold">
                            <?= $editData ? "Edit Dokter" : "Tambah Dokter Baru" ?>
                        </h5>

                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $editData["id"] ?? 0; ?>">

                            <div class="mb-3">
                                <label class="form-label">Nama Dokter</label>
                                <input type="text" name="nama" class="form-control" required
                                       value="<?= htmlspecialchars($editData["nama"] ?? ""); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Spesialisasi</label>
                                <input type="text" name="spesialisasi" class="form-control"
                                       value="<?= htmlspecialchars($editData["spesialisasi"] ?? ""); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Poli</label>
                                <input type="text" name="poli" class="form-control"
                                       value="<?= htmlspecialchars($editData["poli"] ?? ""); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($editData["email"] ?? ""); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">SIP</label>
                                <input type="text" name="sip" class="form-control"
                                       value="<?= htmlspecialchars($editData["sip"] ?? ""); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Password 
                                    <?php if ($editData): ?>
                                        <small class="text-muted">(kosongkan jika tidak diubah)</small>
                                    <?php endif; ?>
                                </label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <button class="btn btn-primary w-100 mt-2" type="submit">
                                <?= $editData ? "Update Dokter" : "Simpan Dokter" ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TABLE SIDE -->
            <div class="col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-body">
                        <h5 class="card-title mb-3 fw-bold">Daftar Dokter</h5>

                        <?php if ($dokter->num_rows == 0): ?>
                            <p class="text-muted mb-0">Belum ada data dokter.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama</th>
                                            <th>Spesialisasi</th>
                                            <th>Poli</th>
                                            <th>Email</th>
                                            <th>SIP</th>
                                            <th style="width:120px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($d = $dokter->fetch_assoc()): ?>
                                            <tr class="doctor-item">
                                                <td><?= htmlspecialchars($d["nama"]); ?></td>
                                                <td><?= htmlspecialchars($d["spesialisasi"]); ?></td>
                                                <td><?= htmlspecialchars($d["poli"]); ?></td>
                                                <td><?= htmlspecialchars($d["email"]); ?></td>
                                                <td><?= htmlspecialchars($d["sip"]); ?></td>
                                                <td>
                                                    <a href="kelola_dokter.php?edit=<?= $d["id"]; ?>" class="btn btn-sm btn-outline-primary edit-btn">
                                                        Edit
                                                    </a>
                                                    <a href="kelola_dokter.php?hapus=<?= $d["id"]; ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Yakin hapus dokter ini?')">
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
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
