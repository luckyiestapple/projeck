<?php
session_start();
require "koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // ======================
    // 1. Coba sebagai ADMIN
    // ======================
    $stmt = $konek->prepare("SELECT id, nama, password FROM admin WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($admin && $password === $admin["password"]) {
        $_SESSION["role"] = "admin";
        $_SESSION["id_admin"] = $admin["id"];
        $_SESSION["nama"] = $admin["nama"];

        header("Location: admin/dashboard.php");
        exit;
    }

    // ======================
    // 2. Coba sebagai DOKTER
    // ======================
    $stmt = $konek->prepare("SELECT id, nama, password FROM dokter WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $dokter = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($dokter && $password === $dokter["password"]) {
        $_SESSION["role"] = "dokter";
        $_SESSION["id_dokter"] = $dokter["id"];
        $_SESSION["nama"] = $dokter["nama"];

        header("Location: dokter/dashboard.php");
        exit;
    }

    // ======================
    // 3. Coba sebagai PASIEN
    // ======================
    $stmt = $konek->prepare("SELECT id, nama, password FROM pasien WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $pasien = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($pasien && password_verify($password, $pasien["password"])) {
        $_SESSION["role"] = "pasien";
        $_SESSION["id_pasien"] = $pasien["id"];
        $_SESSION["nama"] = $pasien["nama"];

        header("Location: pasien/dashboard.php");
        exit;
    }

    // Kalau semua gagal
    $error = "Email atau password salah.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Sistem Rumah Sakit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

<div class="card shadow p-4" style="width: 380px;">
    <h3 class="text-center mb-3">Login</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button class="btn btn-primary w-100">Masuk</button>

        <p class="mt-3 text-center small">
            Belum punya akun pasien? <a href="register.php">Daftar di sini</a>
        </p>
    </form>
</div>

</body>
</html>
