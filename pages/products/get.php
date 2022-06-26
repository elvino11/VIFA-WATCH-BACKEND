<?php

include "../../config/koneksi.php";

/**
 * @var $connection PDO
 */

/*
 * Validate HTTP Method
 */

if($_SERVER['REQUEST_METHOD'] !== 'GET'){
    header('Content-Type: application/json');
    http_response_code(400);
    $reply['error'] = 'DELETE method required';
    echo json_encode($reply);
    exit();

}

$dataFinal = [];
$kode_barang = $_GET['kode_barang'] ?? '';

if (empty($kode_barang)) {
    $reply['error'] = 'Kode Barang harus diisi';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

try {
    $queryCheck = "SELECT * FROM barang WHERE kode_barang = :kode_barang";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':kode_barang', $kode_barang);
    $statement->execute();
    $dataBarang = $statement->fetch(PDO::FETCH_ASSOC);


    /*
     * Ambil data dari brand berdasarkan kolom brand
     */

    if ($dataBarang) {
        $stmBrand = $connection->prepare("SELECT * FROM brand WHERE id = :id");
        $stmBrand->bindValue(':id', $dataBarang['brand']);
        $stmBrand->execute();
        $resultBrand = $stmBrand->fetch(PDO::FETCH_ASSOC);

        /*
         * Defaul Brand 'Tidak diketahui'
         */

        $brand = [
            'id' => $dataBarang['brand'],
            'nama_brand' => 'Tidak Diketahui'
        ];

        if ($resultBrand) {
            $brand = [
                'id' => $resultBrand['id'],
                'nama_brand' => $resultBrand['nama_brand']
            ];
        }

        /*
        * Transoform hasil query dari table barang dan brand
        * Gabungkan data berdasarkan kolom id brand
        * Jika id kategori tidak ditemukan, default "tidak diketahui'
        */

        $dataFinal = [
            'kode_barang' => $dataBarang['kode_barang'],
            'nama_barang' => $dataBarang['nama_barang'],
            'brand' => $brand,
            'stok_barang' => $dataBarang['stok_barang'],
            'harga' => $dataBarang['harga'],
            'tanggal' => $dataBarang['tanggal'],
            'deskripsi' => $dataBarang['deskripsi'],
            'gambar' => $dataBarang['gambar'],
            'created_at' => $dataBarang['created_at']
        ];
    }
} catch (Exception $exception) {
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/*
 * Show response
 */
if (!$dataFinal) {
    $reply['error'] = 'Data dengan Kode Barang : '.$kode_barang. ' tidak ditemukan !!';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/*
 * Otherwise show data
 */

header('Content-Type: application/json');
$reply['status'] = true;
$reply['data'] = $dataFinal;
echo json_encode($reply);