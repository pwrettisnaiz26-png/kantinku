<?php

require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/../models/Kategori.php";

$db = new Database();
$conn = $db->getConnection();

$kategori = new Kategori($conn);

// Tambahkan kategori
$kategori->create("Makanan Berat");
$kategori->create("Minuman Segar");
$kategori->create("Camilan Gurih");

// Tampilkan semua kategori
$data = $kategori->getAll();

echo "<h2>Daftar Kategori KantinKu</h2>";

foreach ($data as $item) {
    echo "ID: " . $item['id'] . "<br>";
    echo "Kategori: " . $item['nama_kategori'] . "<br>";
    echo "Jumlah Produk: " . $item['jumlah_produk'] . "<br>";
    echo "<hr>";
}