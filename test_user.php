<?php

require_once "backend/config/Database.php";
require_once "backend/models/User.php";

$db = new Database();
$conn = $db->getConnection();

$user = new User($conn);

try {
    $hasil = $user->register(
        "siswa01",
        "password123",
        "Siswa Pertama",
        "081234567890"
    );

    if ($hasil) {
        echo "Registrasi user berhasil!";
    }
} catch (Exception $e) {
    echo "Gagal: " . $e->getMessage();
}