<?php

class Produk
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function getAll()
    {
        $sql = "SELECT p.*, k.nama_kategori
                FROM products p
                JOIN categories k ON p.category_id = k.id
                ORDER BY p.id DESC";

        return $this->db->query($sql)->fetchAll();
    }

    public function create($catId, $nama, $harga, $stok, $deskripsi = '', $foto = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO products
            (category_id, nama_produk, harga, stok, deskripsi, foto)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $catId,
            trim($nama),
            $harga,
            $stok,
            trim($deskripsi),
            $foto
        ]);
    }

    public function updateStok($id, $qtyChange)
    {
        // Ambil stok saat ini
        $stmt = $this->db->prepare("
            SELECT stok
            FROM products
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        $produk = $stmt->fetch();

        if (!$produk) {
            return false;
        }

        // Hitung stok baru
        $stokBaru = $produk['stok'] + $qtyChange;

        // Stok tidak boleh kurang dari 0
        if ($stokBaru < 0) {
            return false;
        }

        // Update stok
        $stmt = $this->db->prepare("
            UPDATE products
            SET stok = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $stokBaru,
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM products
            WHERE id = ?
        ");

        return $stmt->execute([
            $id
        ]);
    }
}