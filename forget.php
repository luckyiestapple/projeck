<?php
session_start();
require "koneksi.php";

$error   = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email      = trim($_POST["email"] ?? "");
    $password   = $_POST["password"] ?? "";
    $confirm    = $_POST["confirm"] ?? "";

    if ($email === "" || $password === "" || $confirm === "") {
        $error = "Semua field wajib diisi.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok.";
    } else {

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        if (!$success) {
            $stmt = $konek->prepare("SELECT id FROM pasien WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $update = $konek->prepare("UPDATE pasien SET password = ? WHERE email = ? LIMIT 1");
                $update->bind_param("ss", $hashed, $email);
                $update->execute();
                $success = "Password pasien berhasil direset.";
                $update->close();
            } else {
                $error = "Email tidak ditemukan di sistem.";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password - Sistem Rumah Sakit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #c7ddff, #eef2ff);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 400px;
            background: rgba(255,255,255,0.9);
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
            padding: 36px 34px;
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            color: #1e293b;
        }

        .line {
            height: 4px;
            width: 60px;
            background: #2563eb;
            border-radius: 99px;
            margin: 10px auto 22px;
        }

        label { font-size: 14px; font-weight: 600; color: #334155; }

        .form-control {
            border-radius: 12px;
            border: 1px solid #cbd5f5;
            padding: 11px 14px;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(59,130,246,.25);
            border-color: #2563eb;
        }

        .btn-primary {
            border-radius: 999px;
            padding: 12px;
            font-weight: 600;
            background: linear-gradient(135deg, #2563eb, #1e40af);
            border: none;
            font-size: 15px;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.45);
        }

        .small-text {
            font-size: 13px;
            color: #475569;
        }

        .small-text a {
            font-weight: 600;
            color: #2563eb;
        }

        .error-alert {
            font-size: 13px;
            padding: 10px 12px;
            border-radius: 12px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-card">

    <h1 class="login-title">Lupa Password</h1>
    <div class="line"></div>

    <?php if ($error): ?>
        <div class="alert alert-danger error-alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success error-alert">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">

        <div class="mb-3">
            <label>Email Terdaftar</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password Baru</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Konfirmasi Password</label>
            <input type="password" name="confirm" class="form-control" required>
        </div>

        <button class="btn btn-primary w-100 mt-2">Reset Password</button>

        <p class="mt-3 text-center small-text">
            Sudah ingat password?
            <a href="login.php" class="text-decoration-none">Kembali ke Login</a>
        </p>

    </form>
</div>

</body>
</html>
