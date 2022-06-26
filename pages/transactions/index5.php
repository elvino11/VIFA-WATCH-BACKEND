<?php

include "../../config/koneksi.php";

/**
 * @var $connection PDO
 */
try {
    /**
     * Prepare query
     */

    $statement = $connection->prepare("SELECT * FROM transaksi t, keranjang k WHERE k.id_keranjang = t.id_transaksi");
    $isOk = $statement->execute();
    $resultTransaksi = $statement->fetchAll(PDO::FETCH_ASSOC);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    $stmKeranjang = $connection->prepare("SELECT * FROM  transaksi t, keranjang k WHERE k.id_keranjang = t.id_transaksi");
    $isOk = $stmKeranjang->execute();
    $resultKeranjang = $stmKeranjang->fetchAll(PDO::FETCH_ASSOC);

    $finalResults = [];
    $idKeranjang = array_column($resultKeranjang, 'id_keranjang');

    foreach ($resultKeranjang as $Datakeranjang) {

        $keranjang = [
            'id_keranjang' => $Datakeranjang['id_keranjang'],
            'kode_barang' => 'Tidak Diketahui',
            'kuantitas' => 0
        ];

        $fndByIdKeranjang = array_search($Datakeranjang['id_keranjang'], $idKeranjang);

        if ($fndByIdKeranjang !== false) {
            $findDataKeranjang = $resultKeranjang[$fndByIdKeranjang];
            $keranjang = [
                'id_keranjang' => $findDataKeranjang['id_keranjang'],
                'kode_barang' => $findDataKeranjang['kode_barang'],
                'kuantitas' => $findDataKeranjang['kuantitas']
            ];
        }
        $finalResults[] = [
            $resultTransaksi,
            'id_keranjang' => $keranjang
        ];

    }
    $reply['data'] = $finalResults;
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