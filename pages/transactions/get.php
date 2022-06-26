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
/*$dataFinal = [];*/
$id_user = $_GET['id_user'] ?? 0;
$id_transaksi = $_GET['id_transaksi'] ?? '';

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
if (empty($id_transaksi)) {
    $reply['error'] = 'ID transaksi harus diisi';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

try {
     $queryCheck = "SELECT * FROM keranjang WHERE id_user = :id_user AND id_transaksi = :id_transaksi ORDER BY id_keranjang";
     $statement = $connection->prepare($queryCheck);
     $statement->bindValue(':id_user', $id_user, PDO::PARAM_INT);
    $statement->bindValue(':id_transaksi', $id_transaksi);
    $isOk = $statement->execute();
    $resulKeranjang = $statement->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Ambil data user
     */

    $stmUser = $connection->prepare("SELECT * FROM users");
    $isOk = $stmUser->execute();
    $resultUser = $stmUser->fetchAll(PDO::FETCH_ASSOC);

    /*
    * Ambil data barang
    */

    $stmBarang = $connection->prepare("SELECT * FROM barang");
    $isOk = $stmBarang->execute();
    $resultBarang = $stmBarang->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Transoform hasil query dari table keranjang, barang dan user
     * Gabungkan data berdasarkan kolom id user
     * Jika id user tidak ditemukan, default "tidak diketahui'
     */

    $finalResults = [];
    $user_id = array_column($resultUser, 'id_user');
    $kode_barang = array_column($resultBarang, 'kode_barang');

    foreach ($resulKeranjang as $keranjang) {
        /*
         * Default user 'Tidak diketahui'
         */

        $user = [
            'id_user' => $keranjang['id_user'],
            'username' => 'Tidak diketahui'
        ];

        /*
        * Cari user berdasarkan ID
        */

        $findByIdUser = array_search($keranjang['id_user'], $user_id);

        /*
         * Jika id ditemukan
         */

        if ($findByIdUser !== false) {
            $findDataUser = $resultUser[$findByIdUser];
            $user = [
                'id_user' => $findDataUser['id_user'],
                'username' => $findDataUser['username']
            ];
        }
        /*
         * Default barang 'Tidak diketahui'
         */
        $barang = [
            'kode_barang' => $keranjang['kode_barang'],
            'nama_barang' => 'Tidak diketahui',
            'harga' => 0
        ];
        /*
        * Cari brand berdasarkan Kode Barang
        */

        $findByKodeBarang = array_search($keranjang['kode_barang'], $kode_barang);
        /*
         * Jika kode barang ditemukan
         */

        if ($findByKodeBarang !== false) {
            $findDataBarang = $resultBarang[$findByKodeBarang];
            $barang = [
                'kode_barang' => $findDataBarang['kode_barang'],
                'nama_barang' => $findDataBarang['nama_barang'],
                'harga' => $findDataBarang['harga'],
                'gambar' => $findDataBarang['gambar']
            ];
        }

        $finalResults[] = [
            'id_keranjang' => $keranjang['id_keranjang'],
            'user' => $user,
            'barang' => $barang,
            'kuantitas' => $keranjang['kuantitas'],
            'total_harga' => $keranjang['kuantitas'] * $findDataBarang['harga'],
            'id_transaksi' => $keranjang['id_transaksi']
        ];
    }
    $reply['data'] = $finalResults;

} catch (Exception $exception) {
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/*
 * Show response
 */
if (!$isOk) {
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}
/*
 * Otherwise show data
 */
/*
 * Query OK
 * set status == true
 * Output JSON
 */

header('Content-Type: application/json');
$reply['status'] = true;
echo json_encode($reply);