<?php
session_start();

// Koneksi database
$konek = new mysqli("localhost", "root", "", "rumahsakit");

// Cek koneksi
if ($konek->connect_error) {
    die("Koneksi gagal: " . $konek->connect_error);
}

// Proses login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    // Query cek akun
    $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($konek, $sql);

    if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);

    // Simpan sesi
    $_SESSION['email'] = $data['email'];
    $_SESSION['nama'] = $data['nama'];
    $_SESSION['role'] = $data['role'];

    // Arahkan sesuai role
    if ($data['role'] == "admin") {
        header("Location: admin_dashboard.php");
    } elseif ($data['role'] == "dokter") {
        header("Location: data-dokter.php");
    } else {
        header("Location: pasien_dashboard.php");
    }
    exit();
} else {
    echo "<script>alert('Email atau password salah');</script>";
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
            max-width: 500px; 
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

        .form-group { 
            margin-bottom: 20px; }
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
            margin-top: 10px; 
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

        .error-msg {
            background: #fee2e2;
            padding: 10px;
            border-radius: 8px;
            color: #b91c1c;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="header"><i class="fa-solid fa-hospital"></i><h2>RS Citra Medika</h2></div>

        <div class="tabs">
            <a href="login.php" class="tab-item active">Sign In</a>
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
