<?php

require_once "backend/config/Database.php";

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "Koneksi ke database KantinKu berhasil!";
}