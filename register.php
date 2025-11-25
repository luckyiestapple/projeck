<?php
// ============== LOGIKA REGISTER =============
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

    if ($pass !== $pass2) {
        $error = "Password dan konfirmasi password tidak sama!";
    } else {
        // Register berhasil (sementara tanpa database)
        // Nanti bisa diganti INSERT ke MySQL
        $success = "Akun berhasil dibuat! Silakan login.";
        header("refresh:2; url=signin.php");
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
/* Semua style ⭐ tetap sama seperti HTML kamu */
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { 
    background-color: #f8f9fa; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    min-height: 100vh; 
    padding: 20px; }

.card { 
    background: white; 
    width: 100%; 
    max-width: 700px; 
    border-radius: 20px; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.05); 
    padding: 40px; 
    border: 1px solid #eaeaea; }

.header { 
    text-align: center; 
    margin-bottom: 30px; 
    color: #1e40af; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    gap: 10px; }

.header i { 
    font-size: 1.8rem; }

.header h2 { 
    font-weight: 600; 
    font-size: 1.5rem; }

.tabs { 
    display: flex; 
    background-color: #e9ecef; 
    border-radius: 50px; 
    padding: 5px; 
    margin-bottom: 30px; }

.tab-item { 
    flex: 1; 
    text-align: center; 
    padding: 10px; 
    border-radius: 50px; 
    cursor: pointer; 
    font-weight: 500; 
    color: #495057; 
    font-size: 0.9rem; 
    text-decoration: none; }

.tab-item.active { 
    background-color: white; 
    color: #000; 
    box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

.form-grid { 
    display: grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 20px; }

.full-width { 
    grid-column: span 2; }

.form-group { 
    margin-bottom: 5px; }

.form-group label { 
    display: block; 
    font-weight: 600; 
    font-size: 0.9rem; 
    margin-bottom: 8px; 
    color: #1f2937; }

.input-wrapper { 
    position: relative; }

.input-wrapper i { 
    position: absolute; 
    left: 15px; 
    top: 50%; 
    transform: translateY(-50%); 
    color: #9ca3af; }

.form-control { 
    width: 100%; 
    padding: 12px 15px 12px 45px; 
    background-color: #f3f4f6; 
    border: 1px solid transparent; 
    border-radius: 8px; 
    font-size: 0.95rem; 
    color: #333; 
    transition: 0.3s; }

.form-control:focus { 
    outline: none; 
    background-color: white; 
    border-color: #1e40af; }

textarea.form-control { 
    resize: none; 
    height: 100px; 
    padding-left: 45px; }

textarea + i { 
    top: 20px !important; 
    transform: none !important; }

.gender-options { 
    display: flex; 
    gap: 20px; 
    margin-top: 5px; }

.radio-label { 
    display: flex; 
    align-items: center; 
    cursor: pointer; 
    font-size: 0.95rem; }

.radio-label input { 
    margin-right: 8px;
    accent-color: #1e40af; 
    width: 16px; 
    height: 16px; }

.btn-submit { 
    background-color: #0f172a; 
    color: white; 
    width: 100%; 
    padding: 14px; 
    border: none; 
    border-radius: 8px; 
    font-size: 1rem;
    font-weight: 600; 
    cursor: pointer; 
    margin-top: 20px; 
    transition: background 0.3s; }

.btn-submit:hover { 
    background-color: #1e293b; }

.footer { 
    text-align: center; 
    margin-top: 20px; 
    font-size: 0.9rem; 
    color: #6b7280; }

.footer a { 
    color: #1e40af; 
    text-decoration: none; 
    font-weight: 600; }

.footer a:hover { 
    text-decoration: underline; }

.alert {
    padding: 12px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 20px;
    font-size: 0.9rem;
}

.error { 
    background: #fee2e2; 
    color: #b91c1c; 
    border: 1px solid #fca5a5; }

.success { 
    background: #dcfce7; 
    color: #166534; 
    border: 1px solid #86efac; }

@media (max-width: 600px) {
    .form-grid { grid-template-columns: 1fr; }
    .full-width { grid-column: span 1; }
    .card { padding: 20px; }
}
</style>
</head>

<body>
    <div class="card">
        <div class="header"><i class="fa-solid fa-hospital"></i><h2>RS Citra Medika</h2></div>

        <div class="tabs">
            <a href="login.php" class="tab-item">Sign In</a>
            <a href="register.php" class="tab-item active">Register</a>
            <a href="lupa-password.php" class="tab-item">Lupa Password</a>
        </div>

        <?php if ($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-grid">
                
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>No. Telepon *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-phone-volume"></i>
                        <input type="text" name="telepon" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tanggal Lahir *</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-calendar"></i>
                        <input type="text" name="tgl_lahir" class="form-control" placeholder="mm/dd/yyyy" 
                        onfocus="(this.type='date')" onblur="(this.type='text')" required>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Jenis Kelamin *</label>
                    <div class="gender-options">
                        <label class="radio-label">
                            <input type="radio" name="gender" value="Laki-laki" required> Laki-laki
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="gender" value="Perempuan"> Perempuan
                        </label>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label>Alamat Lengkap *</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-location-dot"></i>
                        <textarea name="alamat" class="form-control" required></textarea>
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
