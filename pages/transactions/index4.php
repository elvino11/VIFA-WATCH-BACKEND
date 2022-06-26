<?php

include "../../config/koneksi.php";

/**
 * @var $connection PDO
 */

if($_SERVER['REQUEST_METHOD'] !== 'GET'){
    header('Content-Type: application/json');
    http_response_code(400);
    $reply['error'] = 'DELETE method required';
    echo json_encode($reply);
    exit();
}

$id_user = $_GET['id_user'] ?? 0;

$id_userFilter = filter_var($id_user, FILTER_VALIDATE_INT);

if ($id_userFilter === false) {
    $reply['error'] = "ID user harus format INT";
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}
if (empty($id_user)) {
    $reply['error'] = 'ID user harus diisi';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

try {
    /**
     * Prepare query
     */

    $statement = $connection->prepare("SELECT * FROM transaksi WHERE id_user = :id_user ORDER BY tanggal_transaksi DESC ");
    $statement->bindValue(":id_user", $id_user, PDO::PARAM_INT);
    $isOk = $statement->execute();
    $resultKeranjang = $statement->fetchAll(PDO::FETCH_ASSOC);
    $reply['data'] = $resultKeranjang;
} catch (Exception $exception) {
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

if (!$isOk) {
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}

//try {
//    /**
//     * Prepare query
//     */
//
//
//
//    $statement = $connection->prepare("SELECT * FROM transaksi t, keranjang k WHERE t.id_transaksi = k.id_transaksi");
//    //$statement = $connection->prepare("SELECT * FROM transaksi");
//
//    $isOk = $statement->execute();
//    $results = $statement->fetch(PDO::FETCH_ASSOC);
//    $resultTransaksi = $statement->fetchAll(PDO::FETCH_ASSOC);
//
//    $stmKeranjang = $connection->prepare("SELECT k.id_keranjang, k.id_user, k.kode_barang, k.kuantitas, k.id_transaksi FROM keranjang k, transaksi t WHERE t.id_transaksi = k.id_transaksi ");
//    $isOk = $stmKeranjang->execute();
//    $resultsKeranjang = $stmKeranjang->fetchAll(PDO::FETCH_ASSOC);
//
//    $reply['data'] = [
//        'transaksi' => $resultTransaksi,
//        'id_transaksi' => $results['id_transaksi'],
//        'tanggal_transaksi' => $results['tanggal_transaksi'],
//        'total_harga' => $results['total_harga'],
//        'total_barang' => $results['total_barang'],
//        'diskon' => $results['diskon'],
//        'total_bayar' => $results['total_bayar'],
//        'pembayaran' => $results['pembayaran'],
//        'kembalian' => $results['kembalian'],
//        'data_barang' => $resultsKeranjang
//    ];
//
//
//} catch (Exception $exception) {
//    $reply['error'] = $exception->getMessage();
//    echo json_encode($reply);
//    http_response_code(400);
//    exit(0);
//}
//
//if (!$isOk) {
//    $reply['error'] = $statement->errorInfo();
//    http_response_code(400);
//}



/*
 * Query OK
 * set status == true
 * Output JSON
 */
header('Content-Type: application/json');
$reply['status'] = true;
echo json_encode($reply);