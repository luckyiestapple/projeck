<?php
$DB_HOST = 'localhost';
$DB_NAME = 'rumahsakit';
$DB_USER = 'root';
$DB_PASS = '';

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  die("DB Error: " . $e->getMessage());
}

session_start();

// sementara hardcode dokter login (ganti 1/2/3 saat test)
// idealnya dari proses login
if (!isset($_SESSION['dokter_id'])) {
  $_SESSION['dokter_id'] = 1;
}
$dokterId = (int) $_SESSION['dokter_id'];

// opsional: pastikan dokterId valid agar foreign key tidak gagal saat check-in
$cekDok = $pdo->prepare("SELECT id FROM dokter WHERE id=?");
$cekDok->execute([$dokterId]);
if (!$cekDok->fetch()) {
  // fallback: ambil dokter pertama yang ada
  $first = $pdo->query("SELECT id FROM dokter ORDER BY id ASC LIMIT 1")->fetch();
  if ($first) {
    $_SESSION['dokter_id'] = (int)$first['id'];
    $dokterId = (int)$first['id'];
  }
}
