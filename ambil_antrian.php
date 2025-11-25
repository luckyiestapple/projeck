<?php
session_start();
require "koneksi.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "pasien") {
    header("Location: login.php");
    exit;
}

$id_pasien = $_SESSION["id_pasien"];
$poli_id   = $_POST["poli_id"] ?? null;

if (!$poli_id) {
    header("Location: pilih_poli.php");
    exit;
}

// ambil nomor terakhir
$last = $konek->query("SELECT MAX(nomor) AS nomor FROM antrian WHERE poli_id = $poli_id")->fetch_assoc();
$next = ($last["nomor"] ?? 0) + 1;

// ambil nama pasien
$p = $konek->query("SELECT nama FROM pasien WHERE id = $id_pasien")->fetch_assoc();
$nama_pasien = $p["nama"];

// cari dokter untuk poli tersebut
$dok = $konek->query("SELECT id FROM dokter WHERE poli_id = $poli_id LIMIT 1")->fetch_assoc();
$id_dokter = $dok["id"] ?? NULL;

// insert antrian baru
$konek->query("
    INSERT INTO antrian (nomor, pasien_id, nama_pasien, dokter_id, poli_id)
    VALUES ($next, $id_pasien, '$nama_pasien', $id_dokter, $poli_id)
");

header("Location: antrian_saya.php");
exit;
?>
