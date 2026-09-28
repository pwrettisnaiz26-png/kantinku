<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/../models/Pesanan.php";

echo "<h2>Test Pesanan KantinKu</h2>";

try {

    // Koneksi database
    $db = new Database();
    $conn = $db->getConnection();

    echo "Koneksi database berhasil.<br><br>";

    // Panggil model Pesanan
    $pesanan = new Pesanan($conn);

    echo "Model Pesanan berhasil dipanggil.<br><br>";

    // Ambil stok sebelum pesanan
    $stmt = $conn->prepare("
        SELECT id, nama_produk, harga, stok
        FROM products
        WHERE id IN (1, 2)
        ORDER BY id
    ");

    $stmt->execute();

    $produk = $stmt->fetchAll();

    if (count($produk) < 2) {
        throw new Exception("Produk ID 1 dan ID 2 harus tersedia.");
    }

    echo "<h3>Stok Sebelum Pesanan</h3>";

    foreach ($produk as $item) {
        echo "ID: " . $item['id'] . "<br>";
        echo "Produk: " . $item['nama_produk'] . "<br>";
        echo "Stok: " . $item['stok'] . "<br><br>";
    }

    // Keranjang berisi 2 produk
    $cartItems = [
        [
            'product_id' => 1,
            'jumlah' => 1
        ],
        [
            'product_id' => 2,
            'jumlah' => 1
        ]
    ];

    // Buat pesanan
    $hasil = $pesanan->createOrder(
        1,
        $cartItems,
        'tunai'
    );

    echo "<h3>PESANAN BERHASIL!</h3>";

    echo "ID Pesanan: " . $hasil['order_id'] . "<br>";
    echo "Kode Pesanan: " . $hasil['kode_pesanan'] . "<br>";
    echo "Total Harga: Rp " . $hasil['total'] . "<br><br>";

    // Cek stok setelah pesanan
    $stmt = $conn->prepare("
        SELECT id, nama_produk, stok
        FROM products
        WHERE id IN (1, 2)
        ORDER BY id
    ");

    $stmt->execute();

    $produkSetelah = $stmt->fetchAll();

    echo "<h3>Stok Setelah Pesanan</h3>";

    foreach ($produkSetelah as $item) {
        echo "ID: " . $item['id'] . "<br>";
        echo "Produk: " . $item['nama_produk'] . "<br>";
        echo "Stok: " . $item['stok'] . "<br><br>";
    }

} catch (Throwable $e) {

    echo "<h3>TERJADI ERROR:</h3>";
    echo $e->getMessage();
}