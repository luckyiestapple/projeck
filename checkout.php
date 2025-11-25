<?php
// ========== checkout.php (Membuat Rekap Harian & Logout) ==========

include 'header.php'; // sudah memuat $pdo dan $dokterId
$targetDate = date('Y-m-d'); 

// 1. Ambil Waktu Check-in
$st_att = $pdo->prepare("SELECT check_in_time FROM doctor_attendance WHERE doctor_id=? AND check_in_date=?");
$st_att->execute([$dokterId, $targetDate]);
$attendance = $st_att->fetch();

// Jika tidak ada data check-in hari ini, langsung redirect/logout
if (!$attendance) {
    session_destroy();
    header("Location: index.php"); 
    exit;
}

$check_in_time = $attendance['check_in_time'];
$now = date('Y-m-d H:i:s'); // Waktu saat checkout

// 2. Ambil Data Antrian Selesai
$st_antrian = $pdo->prepare("
    SELECT 
        COUNT(id) AS total_pasien,
        MAX(updated_at) AS waktu_selesai
    FROM antrian
    WHERE dokter_id = ? 
    AND status = 'Selesai'
    AND DATE(created_at) = ?
");
$st_antrian->execute([$dokterId, $targetDate]);
$antrian_data = $st_antrian->fetch();

$totalPasien = (int) ($antrian_data['total_pasien'] ?? 0);
$waktuSelesai = $antrian_data['waktu_selesai'];

// --- 3. Perhitungan Waktu (Hanya jika ada pasien selesai) ---
$totalJamMenit = 0;
$rataWaktuMenit = 0;

if ($totalPasien > 0 && $waktuSelesai) {
    $dtCheckIn = new DateTime("$targetDate $check_in_time");
    $dtSelesai = new DateTime($waktuSelesai); 

    // Hitung total menit praktik (dari Check-in sampai pasien terakhir selesai)
    $interval = $dtCheckIn->diff($dtSelesai);
    $totalJamMenit = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i; 
    
    // Hitung rata-rata waktu per pasien (menit)
    $rataWaktuMenit = round($totalJamMenit / $totalPasien);
}

// --- 4. Insert / Update ke rekap_harian ---
$data = [
    $totalPasien,
    $rataWaktuMenit,
    $totalJamMenit,
    $dokterId,
    $targetDate
];

$cek_rekap = $pdo->prepare("SELECT id FROM rekap_harian WHERE dokter_id=? AND tanggal=?");
$cek_rekap->execute([$dokterId, $targetDate]);

if ($cek_rekap->fetch()) {
    // Update (jika sudah pernah di-rekap hari ini)
    $sql = "UPDATE rekap_harian SET pasien_ditangani=?, rata_waktu_menit=?, total_jam_menit=? WHERE dokter_id=? AND tanggal=?";
} else {
    // Insert
    $sql = "INSERT INTO rekap_harian (pasien_ditangani, rata_waktu_menit, total_jam_menit, dokter_id, tanggal) VALUES (?, ?, ?, ?, ?)";
}
$pdo->prepare($sql)->execute($data);

// --- 5. Logout dan Redirect ---
session_destroy();
header("Location: index.php?msg=rekap_sukses"); // Arahkan ke halaman login
exit;

?>