<?php
// ========== signin.php (VERSI PERBAIKAN FINAL - Menggunakan PDO & Hashing) ==========

session_start();
$error = "";

// Konfigurasi Database (Gunakan PDO untuk konsistensi)
$DB_HOST = 'localhost';
$DB_NAME = 'rumahsakit';
$DB_USER = 'root';
$DB_PASS = '';

try {
  $pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (PDOException $e) {
  $error = "Koneksi database gagal: " . $e->getMessage();
}


// Proses login
if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($error)) {
    $email = $_POST["email"] ?? "";
    $password_input = $_POST["password"] ?? "";

    // 1. Ambil hash password dan data user berdasarkan email
    $st = $pdo->prepare("SELECT id, nama, role, password FROM pasien WHERE email=?");
    $st->execute([$email]);
    $dataPasien = $st->fetch();

    if ($dataPasien) {
        // 2. Verifikasi password yang diinput dengan hash dari database
        if (password_verify($password_input, $dataPasien['password'])) {
            // LOGIN SUKSES

            // Set Sesi yang benar untuk pasien
            $_SESSION['pasien_id'] = $dataPasien['id'];
            $_SESSION['email'] = $dataPasien['email'];
            $_SESSION['nama'] = $dataPasien['nama'];
            
            // Asumsi role pasien adalah 'pasien' (jika ada kolom role, gunakan dataPasien['role'])
            $_SESSION['role'] = $dataPasien['role'] ?? 'pasien'; 

            // Arahkan ke dashboard pasien
            header("Location: pasien_dashboard.php");
            exit();
        } else {
            $error = "Email atau password salah.";
        }
    } else {
        $error = "Email atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Antrian RS - Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* CSS DARI FILE SIGNIN.PHP ANDA */
        /* ... masukkan semua CSS Anda di sini ... */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #e8f6ff; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 18px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; }
        .header { display: flex; align-items: center; justify-content: center; color: #1e40af; margin-bottom: 25px; }
        .header h2 { font-size: 1.5rem; font-weight: 600; margin-left: 10px; }
        .header i { font-size: 24px; }
        .tabs { display: flex; margin-bottom: 25px; border-bottom: 2px solid #e0e0e0; }
        .tab-item { flex: 1; text-align: center; padding: 10px 0; text-decoration: none; color: #6b7280; font-weight: 500; transition: color 0.3s; }
        .tab-item.active { color: #1e40af; border-bottom: 3px solid #1e40af; margin-bottom: -2px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #374151; }
        .input-wrapper { display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: 8px; padding: 0 10px; }
        .input-wrapper i { color: #9ca3af; margin-right: 10px; }
        .form-control { width: 100%; padding: 10px 0; border: none; outline: none; font-size: 1rem; }
        .btn-submit { width: 100%; padding: 12px; border: none; background-color: #1e40af; color: white; font-weight: 600; border-radius: 8px; cursor: pointer; transition: background-color 0.3s; }
        .btn-submit:hover { background-color: #1e293b; }
        .footer { text-align: center; margin-top: 20px; font-size: 0.9rem; color: #6b7280; } 
        .footer a { color: #1e40af; text-decoration: none; font-weight: 600; } 
        .footer a:hover { text-decoration: underline; }
        .error-msg { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>

    <div class="card">
        <div class="header"><i class="fa-solid fa-hospital"></i><h2>Sistem Antrian RS</h2></div>

        <div class="tabs">
            <a href="signin.php" class="tab-item active">Sign In</a>
            <a href="register.php" class="tab-item">Register</a>
            <a href="lupa-password.php" class="tab-item">Lupa Password</a>
        </div>

        <?php if (!empty($error)) { ?>
            <div class="error-msg"><?= $error ?></div>
        <?php } ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Email</label>
                <div class="input-wrapper">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" name="email" class="form-control" placeholder="Masukkan email anda" required>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">Masuk</button>

            <div class="footer">
                Belum punya akun? <a href="register.php">Daftar sekarang</a>
            </div>
        </form>
    </div>

</body>
</html>