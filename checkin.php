<?php
require 'header.php'; // sudah koneksi + $dokterId (boleh juga require db.php jika tak ingin render HTML)

$today = date('Y-m-d');
$now   = date('H:i:s');

// Pastikan tabel attendance ada; jika belum, buat cepat (opsional safety)
$pdo->exec("
  CREATE TABLE IF NOT EXISTS doctor_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_in_time TIME NOT NULL,
    status ENUM('Hadir','Tidak Hadir') DEFAULT 'Hadir',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_att_doctor FOREIGN KEY (doctor_id) REFERENCES dokter(id) ON DELETE CASCADE
  )
");

// Insert hanya jika belum ada untuk hari ini
$cek = $pdo->prepare("SELECT id FROM doctor_attendance WHERE doctor_id=? AND check_in_date=?");
$cek->execute([$dokterId,$today]);
if (!$cek->fetch()) {
  $ins = $pdo->prepare("INSERT INTO doctor_attendance (doctor_id,check_in_date,check_in_time,status) VALUES (?,?,?,'Hadir')");
  $ins->execute([$dokterId,$today,$now]);
}

// Kembali ke halaman sebelumnya agar banner berubah
header("Location: ".($_SERVER['HTTP_REFERER'] ?? 'data-dokter.php'));
