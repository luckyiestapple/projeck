<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

// HAPUS
if (isset($_GET["hapus"])) {
    $id_hapus = (int) $_GET["hapus"];
    $konek->query("DELETE FROM dokter WHERE id = $id_hapus");
    header("Location: kelola_dokter.php");
    exit;
}

// SIMPAN / UPDATE
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
        // UPDATE
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
        // INSERT
        $stmt = $konek->prepare("INSERT INTO dokter (nama, spesialisasi, poli, email, sip, password) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $nama, $spesialis, $poli, $email, $sip, $password);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: kelola_dokter.php");
    exit;
}

// MODE EDIT
if (isset($_GET["edit"])) {
    $id_edit  = (int) $_GET["edit"];
    $editData = $konek->query("SELECT * FROM dokter WHERE id = $id_edit")->fetch_assoc();
}

// AMBIL SEMUA DOKTER
$dokter = $konek->query("SELECT * FROM dokter ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Dokter - Beranda Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styleadmin.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="container-fluid">
    <nav class="navbar navbar-light bg-light py-3 border-bottom">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-user-doctor me-2"></i> Kelola Dokter
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
        <div class="row g-3">
            <!-- FORM -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <?= $editData ? "Edit Dokter" : "Tambah Dokter Baru" ?>
                        </h5>

                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $editData["id"] ?? 0; ?>">

                            <div class="mb-2">
                                <label class="form-label">Nama Dokter</label>
                                <input type="text" name="nama" class="form-control" required
                                       value="<?= htmlspecialchars($editData["nama"] ?? ""); ?>">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Spesialisasi</label>
                                <input type="text" name="spesialisasi" class="form-control"
                                       value="<?= htmlspecialchars($editData["spesialisasi"] ?? ""); ?>">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Poli</label>
                                <input type="text" name="poli" class="form-control"
                                       value="<?= htmlspecialchars($editData["poli"] ?? ""); ?>">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="<?= htmlspecialchars($editData["email"] ?? ""); ?>">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">SIP</label>
                                <input type="text" name="sip" class="form-control"
                                       value="<?= htmlspecialchars($editData["sip"] ?? ""); ?>">
                            </div>

                            <div class="mb-2">
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

            <!-- LIST -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Daftar Dokter</h5>

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
                                            <th style="width:110px;">Aksi</th>
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
