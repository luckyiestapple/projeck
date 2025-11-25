<?php
// ========== lupa-password.php (VERSI FINAL BOOTSTRAP + PDO) ==========
$DB_HOST = 'localhost';
$DB_NAME = 'rumahsakit';
$DB_USER = 'root';
$DB_PASS = '';
$message = "";

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    // Biarkan error jika gagal koneksi
}

// PROSES JIKA FORM DIKIRIM
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";

    // Cek apakah email terdaftar di tabel dokter
    $st = $pdo->prepare("SELECT email FROM dokter WHERE email=?");
    $st->execute([$email]);

    if ($st->fetch()) {
        $message = ['type' => 'success', 'text' => 'Link reset password (fiktif) telah dikirim ke email Anda!'];
    } else {
        $message = ['type' => 'danger', 'text' => 'Email tidak ditemukan di sistem Dokter.'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | Sistem RS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
    <style>
        body {
            background-color: #f4f9ff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card-login {
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="card card-login shadow-lg p-3">
    <div class="card-header bg-white text-center border-0 pb-0">
        <span class="material-icons-sharp text-primary fs-1">lock_reset</span>
        <h1 class="h4 fw-bold text-dark mt-2 mb-1">Lupa Password</h1>
        <p class="small text-muted">Sistem Antrian Rumah Sakit</p>
    </div>

    <div class="card-body pt-0">
        <ul class="nav nav-tabs justify-content-center mb-4">
            <li class="nav-item"><a class="nav-link" href="signin.php">Sign In</a></li>
            <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
            <li class="nav-item"><a class="nav-link active" aria-current="page" href="lupa-password.php">Lupa Password</a></li>
        </ul>

        <div class="alert alert-info small text-center" role="alert">
            Masukkan email yang terdaftar. Kami akan mengirimkan link untuk mereset password Anda.
        </div>

        <?php if (!empty($message)) { ?>
            <div class="alert alert-<?= $message['type'] ?>" role="alert"><?= $message['text'] ?></div>
        <?php } ?>

        <form action="" method="POST">
            <div class="mb-4">
                <label for="email" class="form-label small fw-bold">Email Terdaftar</label>
                <input type="email" name="email" id="email" required class="form-control" placeholder="nama@email.com">
            </div>

            <button type="submit" class="btn btn-warning w-100 fw-bold text-dark">Reset Password</button>

            <div class="text-center mt-3">
                <p class="small text-muted mb-0">Kembali ke <a href="signin.php" class="text-primary fw-bold text-decoration-none">Login</a></p>
            </div>
        </form>
    </div>
</div>

</body>
</html>