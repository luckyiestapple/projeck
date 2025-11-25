<?php
session_start();
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "pasien") {
    header("Location: ../login.php");
    exit;
}
