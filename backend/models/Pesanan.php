<?php

class Pesanan
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function createOrder($userId, array $cartItems, $metode = 'tunai')
    {
        if (empty($cartItems)) {
            throw new Exception("Keranjang belanja kosong.");
        }

        $statusBayar = ($metode === 'qris' || $metode === 'e_wallet')
            ? 'lunas'
            : 'belum_bayar';

        try {

            // 1. Mulai transaksi
            $this->db->beginTransaction();

            $totalHarga = 0;
            $itemsToProcess = [];

            // 2. Cek stok setiap produk
            foreach ($cartItems as $item) {

                $stmt = $this->db->prepare("
                    SELECT id, nama_produk, harga, stok
                    FROM products
                    WHERE id = ?
                    FOR UPDATE
                ");

                $stmt->execute([
                    (int)$item['product_id']
                ]);

                $prod = $stmt->fetch();

                if (!$prod) {
                    throw new Exception("Produk tidak ditemukan.");
                }

                $jumlah = (int)$item['jumlah'];

                if ($jumlah <= 0) {
                    throw new Exception("Jumlah produk tidak valid.");
                }

                if ($prod['stok'] < $jumlah) {
                    throw new Exception(
                        "Stok produk '" . $prod['nama_produk'] . "' tidak mencukupi."
                    );
                }

                $subtotal = $prod['harga'] * $jumlah;

                $totalHarga += $subtotal;

                $itemsToProcess[] = [
                    'id'    => $prod['id'],
                    'qty'   => $jumlah,
                    'harga' => $prod['harga'],
                    'sub'   => $subtotal
                ];
            }

            // 3. Buat kode pesanan
            $kodePesanan = 'KTK-' . date('YmdHis') . '-' . rand(100, 999);

            // 4. Simpan data pesanan
            $insO = $this->db->prepare("
                INSERT INTO orders
                (
                    user_id,
                    kode_pesanan,
                    total_harga,
                    metode_pembayaran,
                    status_pembayaran,
                    status
                )
                VALUES (?, ?, ?, ?, ?, 'menunggu')
            ");

            $insO->execute([
                $userId,
                $kodePesanan,
                $totalHarga,
                $metode,
                $statusBayar
            ]);

            $orderId = $this->db->lastInsertId();

            // 5. Siapkan query detail dan stok
            $insD = $this->db->prepare("
                INSERT INTO order_details
                (
                    order_id,
                    product_id,
                    jumlah,
                    harga_satuan,
                    subtotal
                )
                VALUES (?, ?, ?, ?, ?)
            ");

            $updS = $this->db->prepare("
                UPDATE products
                SET stok = stok - ?
                WHERE id = ?
            ");

            // 6. Simpan detail dan kurangi stok
            foreach ($itemsToProcess as $it) {

                $insD->execute([
                    $orderId,
                    $it['id'],
                    $it['qty'],
                    $it['harga'],
                    $it['sub']
                ]);

                $updS->execute([
                    $it['qty'],
                    $it['id']
                ]);
            }

            // 7. Simpan semua perubahan
            $this->db->commit();

            return [
                'order_id'    => $orderId,
                'kode_pesanan' => $kodePesanan,
                'total'       => $totalHarga
            ];

        } catch (Exception $e) {

            // Batalkan semua perubahan jika terjadi error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}