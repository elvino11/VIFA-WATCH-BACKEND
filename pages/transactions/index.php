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
     * Prepare query product
     */

    $statement = $connection->prepare("SELECT * FROM transaksi t, keranjang k WHERE t.id_user = :id_user AND t.id_transaksi = k.id_keranjang  ");
    $statement->bindValue(":id_user", $id_user, PDO::PARAM_INT);
    $isOk = $statement->execute();
    $resultTransaksi = $statement->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Ambil Data Kerajang
     */

    $stmKeranjang = $connection->prepare("SELECT * FROM keranjang k, transaksi t WHERE k.id_user = :id_user AND t.id_transaksi = k.id_keranjang");
    $stmKeranjang->bindValue(":id_user", $id_user, PDO::FETCH_ASSOC);
    $isOk = $stmKeranjang->execute();
    $resultKeranjang = $stmKeranjang->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Transoform hasil query dari table barang dan brand
     * Gabungkan data berdasarkan kolom id brand
     * Jika id brand tidak ditemukan, default "tidak diketahui'
     */

    $finalResults = [];
    $idKeranjang = array_column($resultKeranjang, 'id_keranjang');

    foreach ($resultTransaksi as $transaksi) {

        foreach ($resultKeranjang as $keranjang) {

            /*
             * Default keranjang 'Tidak Diketahui'
             */
            $keranjang = [
                'id_keranjang' => $keranjang['id_keranjang'],
                'id_user' => 'Tidak Diketahui',
                'kode_barang' => 'Tidak Diketahui',
                'kuantitas' => 0,
                'id_transaksi' => 0
            ];
            /*
             * Cari keranjang berdasarkan id_user
             */
            $findByIdKeranjang = array_search($keranjang['id_keranjang'], $idKeranjang);

            /*
             * Jika ID ditemukan
             */

            if ($findByIdKeranjang !== false) {
                $findDataKeranjang = $resultKeranjang[$findByIdKeranjang];
                $keranjang = [
                    'id_keranjang' => $findDataKeranjang['id_keranjang'],
                    'id_user' => $findDataKeranjang['id_user'],
                    'kode_barang' => $findDataKeranjang['kode_barang'],
                    'kuantitas' => $findDataKeranjang['kuantitas'],
                    'id_transaksi' => $findDataKeranjang['id_transaksi']
                ];
            }
            $finalKeranjang[] = [
                $keranjang
            ];
        }
        $finalResults[] = [
            'id_transaksi' => $transaksi['id_transaksi'],
            'tanggal_transaksi' => $transaksi['tanggal_transaksi'],
            'total_harga' => $transaksi['total_harga'],
            'total_barang' => $transaksi['total_barang'],
            'diskon' => $transaksi['diskon'],
            'total_bayar' => $transaksi['total_bayar'],
            'pembayaran' => $transaksi['pembayaran'],
            'kembalian' => $transaksi['kembalian'],
            'data_barang' => $finalKeranjang
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
/*
 * Query OK
 * set status == true
 * Output JSON
 */
header('Content-Type: application/json');
$reply['status'] = true;
echo json_encode($reply);