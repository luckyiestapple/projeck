<?php
$konek = new mysqli("localhost", "root", "", "rumahsakit");

if ($konek->connect_error) {
    die("Koneksi gagal: " . $konek->connect_error);
}
?>
