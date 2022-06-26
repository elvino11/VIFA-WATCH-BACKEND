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
    $queryCheck = "SELECT * FROM transaksi WHERE id_user = :id_user ORDER BY tanggal_transaksi DESC";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(":id_user", $id_user, PDO::PARAM_INT);
    $isOk = $statement->execute();
    $resultTransaksi = $statement->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Ambil data keranjang
     */

    $stmkeranjang = $connection->prepare("SELECT * FROM keranjang WHERE id_user = :id_user");
    $stmkeranjang->bindValue(":id_user", $id_user, PDO::PARAM_INT);
    $isOk = $stmkeranjang->execute();
    $resultKeranjang = $stmkeranjang->fetchAll(PDO::FETCH_ASSOC);

    /*
    * Ambil data barang
    */

    $stmBarang = $connection->prepare("SELECT * FROM barang");
    $isOk = $stmBarang->execute();
    $resultBarang = $stmBarang->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Transoform hasil query dari table transaksi, keranjang dan barang
     * Gabungkan data berdasarkan kolom id user
     * Jika id user tidak ditemukan, default "tidak diketahui'
     */

    $finalResults = [];
    $user_id = array_column($resultKeranjang, 'id_user');
    $kode_barang = array_column($resultBarang, 'kode_barang');

    foreach ($resultTransaksi as $transaksi) {


        /*
         * Default keranjang 'Tidak diketahui'
         */

        $keranjang = [
            'id_user' => $transaksi['id_user'],
            'kuantitas' => 0
        ];
        /*
        * Cari keranjang berdasarkan ID User
        */

        $findByIdUser = array_search($transaksi['id_user'], $user_id);

        /*
        * Jika user ditemukan
        */

        if ($findByIdUser !== false) {

            foreach ($resultKeranjang as $datKeranjang) {
                /*
                 * Default Barang 'Tidak Diketahui
                 */

                $barang = [
                    'kode_barang' => $datKeranjang['kode_barang'],
                    'nama_barang' => 'Tidak Diketahui',
                ];
                /*
                 * Cari Barang berdasarkan kode barang
                 */

                $findByKodeBarang = array_search($datKeranjang['kode_barang'], $kode_barang);

                /*
                 * Jika kode barang ditemukan
                 */

                if ($findByKodeBarang !== false) {
                    $findDataBarang = $resultBarang[$findByKodeBarang];
                    $barang = [
                        'kode_barang' => $findDataBarang['kode_barang'],
                        'nama_barang' => $findDataBarang['nama_barang'],
                        'harga' => $findDataBarang['harga']
                    ];
                }
            }
            $findDataKeranjang = $resultKeranjang[$findByIdUser];
            $keranjang = [
                'id_user' => $findDataKeranjang['id_user'],
                'kode_barang' => $findDataKeranjang['kode_barang'],
                'barang' => $barang,
                'kuantitas' => $findDataKeranjang['kuantitas']
            ];
        }

        $finalResults[] = [
            'id_transaksi' => $transaksi['id_transaksi'],
            'tanggal_transaksi' => $transaksi['tanggal_transaksi'],
            'total_harga' => $transaksi['total_harga'],
            'total_barang' => $transaksi['total_barang'],
            'diskon' => $transaksi['diskon'],
            'total_bayar' => $transaksi['total_bayar'],
            'pemabayaran' => $transaksi['pembayaran'],
            'kemabalian' => $transaksi['kembalian'],
            'user' => $keranjang
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

header('Content-Type: application/json');
$reply['status'] = true;
echo json_encode($reply);