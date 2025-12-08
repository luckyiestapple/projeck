<?php
session_start();
require "koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Email dan password wajib diisi.";
    } else {
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

        $error = "Email atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Rumah Sakit</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
       * { 
    font-family: 'Inter', sans-serif; 
}

body {
    min-height: 100vh;
    background: linear-gradient(135deg, #c7ddff, #eef2ff);
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-card {
    width: 380px;
    background: rgba(255,255,255,0.88);
    backdrop-filter: blur(14px);
    border-radius: 20px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
    padding: 35px 32px;
    animation: fadeIn 0.7s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

.login-title {
    font-size: 30px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 22px;
    color: #1e293b;
    letter-spacing: .3px;
}

label {
    font-size: 14px;
    font-weight: 600;
    color: #334155;
}

.form-control {
    border-radius: 12px;
    border: 1px solid #cbd5f5;
    padding: 11px 14px;
    transition: .3s ease;
}

.form-control:focus {
    box-shadow: 0 0 0 4px rgba(59,130,246,.25);
    border-color: #2563eb;
}

.btn-primary {
    border-radius: 999px;
    padding: 12px 16px;
    font-weight: 600;
    background: linear-gradient(135deg, #2563eb, #1e40af);
    border: none;
    font-size: 16px;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
    transition: .3s ease;
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

.small-text a:hover {
    text-decoration: underline;
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
    
    <h1 class="login-title">Login</h1>
<div style="height:4px;width:60px;background:#2563eb;border-radius:99px;margin:0 auto 20px;"></div>

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
             <p class="mt-3 text-center small-text">
            Lupa Password?
            <a href="forget.php" class="text-decoration-none">Klik Disini</a>
        </p>
    </form>
</div>

</body>
</html>
