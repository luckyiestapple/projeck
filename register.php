<?php
// ========== register.php (VERSI PDO + KEAMANAN PASSWORD) ==========
// 1) Konfigurasi Database (Pastikan sama dengan db.php/header.php)
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
  // Jika koneksi gagal, tampilkan error
  die("DB Error: " . $e->getMessage());
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = $_POST["nama"] ?? "";
    $email = $_POST["email"] ?? "";
    $telepon = $_POST["telepon"] ?? "";
    $tgl = $_POST["tgl_lahir"] ?? "";
    $gender = $_POST["gender"] ?? "";
    $alamat = $_POST["alamat"] ?? "";
    $pass = $_POST["password"] ?? "";
    $pass2 = $_POST["password2"] ?? "";
    
    // --- 1. Validasi Input ---
    if (empty($nama) || empty($email) || empty($telepon) || empty($tgl) || empty($gender) || empty($alamat) || empty($pass)) {
        $error = "Semua kolom bertanda * wajib diisi!";
    } elseif ($pass !== $pass2) {
        $error = "Password dan konfirmasi password tidak sama!";
    } elseif (strlen($pass) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        // --- 2. Cek Duplikasi Email ---
        $st = $pdo->prepare("SELECT COUNT(id) FROM pasien WHERE email = ?");
        $st->execute([$email]);
        if ($st->fetchColumn() > 0) {
            $error = "Email sudah terdaftar. Silakan login atau gunakan email lain.";
        } else {
            // --- 3. Generasi Nomor Rekam Medis (RM) ---
            // Cari nomor RM tertinggi lalu +1, atau mulai dari 1000000 jika kosong
            $last_rm = $pdo->query("SELECT MAX(CAST(SUBSTRING(no_rm, 3) AS UNSIGNED)) FROM pasien")->fetchColumn();
            $new_rm_num = ($last_rm !== null) ? $last_rm + 1 : 1000000;
            $no_rm = "RM" . str_pad($new_rm_num, 7, '0', STR_PAD_LEFT); 
            
            // --- 4. Proses Hashing Password ---
            // Gunakan PASSWORD_DEFAULT (bcrypt) untuk keamanan
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT); 
            
            // --- 5. Insert Data Pasien ---
            $sql = "INSERT INTO pasien (nama, no_rm, email, password, jenis_kelamin, tgl_lahir, alamat, no_hp) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nama, 
                $no_rm,
                $email, 
                $hashed_password, // Simpan password yang sudah di-hash
                $gender, 
                $tgl, 
                $alamat, 
                $telepon
            ]);
            
            $success = "Akun berhasil dibuat! Nomor Rekam Medis Anda: **$no_rm**. Anda akan diarahkan ke halaman login.";
            // Arahkan ke halaman login setelah 3 detik
            header("refresh:3; url=signin.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Antrian RS - Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* Semua style dari file Anda sebelumnya */
/* ... (Tetap masukkan semua CSS yang ada di file register.php Anda,
       termasuk .card, .header, .tabs, .form-group, dll.) ... */
/* Saya hanya menyertakan CSS tambahan untuk notifikasi */
.success-msg, .error-msg {
    padding: 10px 15px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 0.9rem;
    text-align: center;
    font-weight: 500;
}
.success-msg {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.error-msg {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>
<style>
/* --- Semua style dari file register.php Anda (Bagian style) --- */
* { margin: 0; padding: 0; box-sizing: border-box; }

body { 
    font-family: 'Poppins', sans-serif; 
    background-color: #f4f9ff; 
    color: #333; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    min-height: 100vh;
    padding: 20px;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
    max-width: 800px;
    width: 100%;
    margin-top: 20px;
}

.header {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    color: #0052cc;
}

.header i {
    font-size: 24px;
    margin-right: 10px;
}

.header h2 {
    font-size: 1.5rem;
    font-weight: 600;
}

.tabs {
    display: flex;
    justify-content: space-around;
    border-bottom: 1px solid #e0e0e0;
    margin-bottom: 20px;
}

.tab-item {
    padding: 10px 15px;
    text-decoration: none;
    color: #6b7280;
    font-weight: 500;
    position: relative;
    transition: color 0.3s;
}

.tab-item.active {
    color: #0052cc;
}

.tab-item.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 100%;
    height: 3px;
    background-color: #0052cc;
    border-radius: 2px 2px 0 0;
}

form {
    margin-top: 20px;
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.form-group {
    flex: 1 1 calc(50% - 10px); /* Default 50% width */
    margin-bottom: 15px;
}

.form-group.full-width {
    flex: 1 1 100%; /* Full width for textarea */
}

label {
    display: block;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 5px;
    color: #4b5563;
}

.input-wrapper {
    display: flex;
    align-items: center;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 8px 12px;
    background-color: #f9fafb;
}

.input-wrapper i {
    color: #9ca3af;
    margin-right: 10px;
}

.form-control {
    border: none;
    outline: none;
    width: 100%;
    padding: 5px 0;
    background-color: transparent;
    font-size: 1rem;
    color: #1f2937;
}

input[type="date"], textarea {
    padding: 0;
    font-family: inherit;
    font-size: 1rem;
    background-color: transparent;
}

textarea {
    resize: vertical;
    min-height: 80px;
    width: 100%;
}

.radio-group {
    display: flex;
    gap: 20px;
    margin-top: 5px;
}

.radio-group label {
    display: flex;
    align-items: center;
    font-weight: 400;
    cursor: pointer;
}

.radio-group input[type="radio"] {
    margin-right: 5px;
}

.btn-submit {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    background-color: #0052cc;
    color: white;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: 15px;
}

.btn-submit:hover {
    background-color: #003e99;
}

.footer {
    text-align: center;
    margin-top: 20px;
    font-size: 0.9rem;
    color: #6b7280;
}

.footer a {
    color: #0052cc;
    text-decoration: none;
    font-weight: 600;
}

.footer a:hover {
    text-decoration: underline;
}

@media (max-width: 600px) {
    .form-group {
        flex: 1 1 100%;
    }
}
/* Tambahkan CSS di atas ke file register.php Anda, atau pastikan sudah ada */
</style>


</head>
<body>
    <div class="card">
        <div class="header"><i class="fa-solid fa-hospital"></i><h2>Rs Citra Medika</h2></div>

        <div class="tabs">
            <a href="signin.php" class="tab-item">Sign In</a>
            <a href="register.php" class="tab-item active">Register</a>
            <a href="lupa-password.php" class="tab-item">Lupa Password</a>
        </div>
        
        <?php if (!empty($success)) { ?>
            <div class="success-msg"><?= $success ?></div>
        <?php } ?>
        <?php if (!empty($error)) { ?>
            <div class="error-msg"><?= $error ?></div>
        <?php } ?>

        <form action="" method="POST">
            <div class="form-row">
                <div class="form-group full-width">
                    <label>Nama Lengkap *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($nama) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>No. Telepon *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-phone"></i>
                        <input type="text" name="telepon" class="form-control" required value="<?= htmlspecialchars($telepon) ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Tanggal Lahir *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-calendar-days"></i>
                        <input type="date" name="tgl_lahir" class="form-control" required value="<?= htmlspecialchars($tgl) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin *</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="gender" value="Laki-laki" <?= $gender == 'Laki-laki' ? 'checked' : '' ?> required> Laki-laki
                        </label>
                        <label>
                            <input type="radio" name="gender" value="Perempuan" <?= $gender == 'Perempuan' ? 'checked' : '' ?>> Perempuan
                        </label>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Alamat Lengkap *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-location-dot"></i>
                        <textarea name="alamat" class="form-control" required><?= htmlspecialchars($alamat) ?></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password2" class="form-control" required minlength="6">
                    </div>
                </div>

            </div>

            <button type="submit" class="btn-submit">Daftar sebagai Pasien</button>

            <div class="footer">
                Sudah punya akun? <a href="signin.php">Login di sini</a>
            </div>
        </form>
    </div>
</body>
</html>