<?php

include "../../config/koneksi.php";

/**
 * @var $connection PDO
 */

try {
    /**
     * Prepare query product
     */
    $statement = $connection->prepare("SELECT * FROM barang WHERE stok_barang > 0 ORDER BY created_at DESC");
    $isOK = $statement->execute();
    $resultBarang = $statement->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Ambil data brand
     */

    $stmBrand = $connection->prepare("SELECT * FROM brand");
    $isOK = $stmBrand->execute();
    $resultBrand = $stmBrand->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Transoform hasil query dari table barang dan brand
     * Gabungkan data berdasarkan kolom id brand
     * Jika id brand tidak ditemukan, default "tidak diketahui'
     */
    $finalResults = [];
    $idBrand = array_column($resultBrand, 'id');

    foreach ($resultBarang as $barang) {
        /*
         * Default brand 'Tidak diketahui'
         */
        $brand = [
            'id' => $barang['brand'],
            'nama_brand' => 'Tidak diketahui'
        ];

        /*
         * Cari brand berdasarkan ID
         */

        $findByIdBrand = array_search($barang['brand'], $idBrand);

        /*
         * Jika id ditemukan
         */

        if ($findByIdBrand !== false) {
            $findDataBrand = $resultBrand[$findByIdBrand];
            $brand = [
                'id' => $findDataBrand['id'],
                'nama_brand' => $findDataBrand['nama_brand']
            ];
        }

        $finalResults[] = [
            'kode_barang' => $barang['kode_barang'],
            'nama_barang' => $barang['nama_barang'],
            'brand' => $brand,
            'stok_barang' => $barang['stok_barang'],
            'harga' => $barang['harga'],
            'tanggal' => $barang['tanggal'],
            'deskripsi' => $barang['deskripsi'],
            'gambar' => $barang['gambar'],
            'created_at' => $barang['created_at']
        ];
    }

    $reply['data'] = $finalResults;

} catch (Exception $exception) {
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

if (!$isOK) {
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}
/*
 * Query OK
 * set status == true
 * Output JSON
 */
header('Content-Type: application/json');
$reply['status'] = true;
echo json_encode($reply);
