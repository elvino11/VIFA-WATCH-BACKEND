<?php

include "../../config/koneksi.php";

/**
 * @var $connection PDO
 */

/*
 * Validate HTTP Method
 */
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(400);
    $reply['error'] = 'DELETE methode required';
    echo json_encode($reply);
    exit();
}

/**
 * Get input data from RAW data
 */

$data = file_get_contents('php://input');
$response = [];
parse_str($data, $response);
$kode_barang = $response['kode_barang'] ?? '';

/**
 * Cek apakah Kode Barang Tersedia
 */

try {
    $queryCheck = "SELECT * FROM barang WHERE kode_barang = :kode_barang";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':kode_barang', $kode_barang);
    $statement->execute();
    $row = $statement->rowCount();

    /**
     * Jika tidak ada data ditemukan
     * rowcount == 0
     */
    if ($row === 0) {
        $reply['error'] = 'Data dengan kode_barang : '.$kode_barang. ' tidak ditemukan !!';
        echo json_encode($reply);
        http_response_code(400);
        exit(0);
    }
} catch (Exception $exception) {
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/**
 * Hapus data
 */

try {
    $queryCheck = "DELETE FROM barang WHERE kode_barang = :kode_barang";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':kode_barang', $kode_barang);
    $statement->execute();
} catch (Exception $exception) {
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}
/*
 * Send Output
 */

header('Content-Type: application/json');
$reply['status'] = true;
echo json_encode($reply);