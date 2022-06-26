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
     * Prepare query transaksi
     */
    $statement = $connection->prepare("SELECT * FROM transaksi WHERE id_user = :id_user");
    $statement->bindValue(":id_user", $id_user, PDO::PARAM_INT);
    $isOk = $statement->execute();
    $resultTransaksi = $statement->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Ambil data keranjang
     */

    $stmKeranjang = $connection->prepare("SELECT * FROM keranjang k, transaksi t WHERE k.id_keranjang = t.id_transaksi");
    $isOk = $stmKeranjang->execute();
    $resultKeranjang = $stmKeranjang->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Transoform hasil query dari table transaksi dan keranjang
     * Gabungkan data berdasarkan kolom id keranjang
     * Jika id keranjang tidak ditemukan, default "tidak diketahui'
     */

    $finalResults = [];
    $idKeranjang = array_column($resultKeranjang, 'id_keranjang');

    foreach ($resultKeranjang as $datakeranjang) {
        /*
         * Default keranjang 'Tidak diketahui'
        */
        $keranjang = [
            'id_keranjang' => $datakeranjang['id_keranjang'],
            'kode_barang' => 'Tidak Diketahui',
            'kuantitas' => 0
        ];
        /*
         * Cari keranjang berdasarkan ID
        */

        $findByIdKeranjang = array_search($datakeranjang['id_keranjang'], $idKeranjang);

        /*
         * Jika id ditemukan
         */
        if ($findByIdKeranjang !== false) {
            $findDataKeranjang = $resultKeranjang[$findByIdKeranjang];
            $keranjang = [
                'id_keranjang' => $findDataKeranjang['id_keranjang'],
                'kode_barang' => $findDataKeranjang['kode_barang'],
                'kuantitas' => $findDataKeranjang['kuantitas']
            ];
        }
        $finalKeranjang[] = [
            $keranjang
        ];
    }

    foreach ($resultTransaksi as $transaksi) {


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
