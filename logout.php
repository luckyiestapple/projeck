<?php
// logout.php
session_start();
// Hancurkan semua data sesi
session_unset();
session_destroy();
// Arahkan kembali ke halaman login
header("Location: signin.php"); 
exit();
?>