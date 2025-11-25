<?php
session_start();
require "koneksi.php";

$error = "";

// Proses login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Email dan password wajib diisi.";
    } else {

        // 1. Coba sebagai ADMIN
        $stmt = $konek->prepare("SELECT id, nama, password FROM admin WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin && $password === $admin["password"]) {
            $_SESSION["role"]     = "admin";
            $_SESSION["id_admin"] = $admin["id"];
            $_SESSION["nama"]     = $admin["nama"];
            header("Location: dashboard_admin.php"); // sesuaikan kalau beda
            exit;
        }

        // 2. Coba sebagai DOKTER
        $stmt = $konek->prepare("SELECT id, nama, password FROM dokter WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $dokter = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($dokter && $password === $dokter["password"]) {
            $_SESSION["role"]       = "dokter";
            $_SESSION["id_dokter"]  = $dokter["id"];
            $_SESSION["nama"]       = $dokter["nama"];
            header("Location: dashboard_dokter.php");
            exit;
        }

        // 3. Coba sebagai PASIEN (password HASH)
        $stmt = $konek->prepare("SELECT id, nama, password FROM pasien WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $pasien = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($pasien && password_verify($password, $pasien["password"])) {
            $_SESSION["role"]      = "pasien";
            $_SESSION["id_pasien"] = $pasien["id"];
            $_SESSION["nama"]      = $pasien["nama"];
            header("Location: dashboard_pasien.php");
            exit;
        }

        // kalau semua gagal
        $error = "Email atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Rumah Sakit</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font (biar mirip UI modern) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            min-height: 100vh;
            background: radial-gradient(circle at top, #e0f2ff 0, #f4f6fb 45%, #eef1f6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 380px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
            padding: 28px 30px 30px;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 18px;
            color: #111827;
        }

        label {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
        }

        .form-control {
            border-radius: 10px;
            border-color: #d1d5db;
            padding: 10px 12px;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(59,130,246,.25);
            border-color: #2563eb;
        }

        .btn-primary {
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 600;
            background: #2563eb;
            border-color: #2563eb;
            font-size: 16px;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
        }

        .small-text {
            font-size: 13px;
        }

        .error-alert {
            font-size: 13px;
            padding: 8px 10px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="login-card">

    <h1 class="login-title">Login</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger error-alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button class="btn btn-primary w-100 mt-1">Masuk</button>

        <p class="mt-3 text-center small-text">
            Belum punya akun pasien?
            <a href="register.php" class="text-decoration-none">Daftar di sini</a>
        </p>
    </form>
</div>

</body>
</html>
