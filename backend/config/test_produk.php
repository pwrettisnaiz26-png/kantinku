<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/../models/Produk.php";

echo "<h2>Test Produk KantinKu</h2>";

try {

    $db = new Database();
    $conn = $db->getConnection();

    echo "Koneksi database berhasil.<br><br>";

    $produk = new Produk($conn);

    echo "Model Produk berhasil dipanggil.<br><br>";

    $data = $produk->getAll();

    echo "Jumlah produk: " . count($data) . "<br><br>";

    if (count($data) > 0) {

        $idProduk = $data[0]['id'];
        $stokSebelum = $data[0]['stok'];

        echo "ID Produk: " . $idProduk . "<br>";
        echo "Stok sebelum: " . $stokSebelum . "<br><br>";

        // Tambah stok sebanyak 5
        $hasil = $produk->updateStok($idProduk, 5);

        if ($hasil) {
            echo "<b>UPDATE STOK BERHASIL!</b><br><br>";
        } else {
            echo "<b>UPDATE STOK GAGAL!</b><br><br>";
        }

        // Ambil data terbaru
        $dataBaru = $produk->getAll();

        echo "Stok setelah: " . $dataBaru[0]['stok'] . "<br>";

    } else {

        echo "Belum ada produk di database.";

    }

} catch (Throwable $e) {

    echo "<h3>TERJADI ERROR:</h3>";
    echo $e->getMessage();
}