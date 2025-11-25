<?php
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "dokter") {
    header("Location: login.php");
    exit;
}
