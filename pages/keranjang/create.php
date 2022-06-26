<?php

include "../../config/koneksi.php";

/**
 * @var $connection PDO
 */

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(400);
    $reply['error'] = 'POST method required';
    echo json_encode($reply);
    exit();
}

/**
 * Get input data POST
 */

$id_user = $_POST['id_user'] ?? 0;
$kode_barang = $_POST['kode_barang'] ?? '';
$kuantitas = $_POST['kuantitas'] ?? 0;
$id_transaksi = $_POST['id_transaksi'] ?? '';


/**
 * Validation int value
 */

$id_userFilter = filter_var($id_user, FILTER_VALIDATE_INT);
$kuantitasFilter = filter_var($kuantitas, FILTER_VALIDATE_INT);


/**
 * Validation empty fields
 */

$isValidated = true;
if ($id_userFilter === false) {
    $reply['error'] = "ID user harus format INT";
    $isValidated = false;
}
if ($kuantitasFilter === false) {
    $reply['error'] = "Kuantitas harus format INT";
    $isValidated = false;
}
if (empty($id_user)) {
    $reply['error'] = 'ID user harus diisi';
    $isValidated = false;
}
if (empty($kode_barang)) {
    $reply['error'] = 'Kode barang harus diisi';
    $isValidated = false;
}
if (empty($kuantitas)) {
    $reply['error'] = 'Kuantitas harus diisi';
    $isValidated = false;
}
if (empty($id_transaksi)) {
    $reply['error'] = 'ID transaksi harus diisi';
    $isValidated = false;
}

/*
 * Jika filter gagal
 */
if (!$isValidated) {
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}


/**
 * Method OK
 * Validation OK
 * Prepare query
 */

try {
    $query = "INSERT INTO keranjang (id_user, kode_barang, kuantitas, id_transaksi) VALUES (:id_user, :kode_barang, :kuantitas, :id_transaksi)";

    $statement = $connection->prepare($query);

    /**
     * Bind params
     */

    $statement->bindValue(":id_user", $id_user, PDO::PARAM_INT);
    $statement->bindValue(":kode_barang", $kode_barang);
    $statement->bindValue(":kuantitas", $kuantitas, PDO::PARAM_INT);
    $statement->bindValue(":id_transaksi", $id_transaksi);

    /**
     * Execute query
     */
    $isOk = $statement->execute();

} catch (Exception $exception) {
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/**
 * If not OK, add error info
 * HTTP Status code 400: Bad request
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status#client_error_responses
 */

if(!$isOk){
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}

/*
 * Get last data
 */
$getResult = "SELECT * FROM keranjang WHERE kode_barang = :kode_barang";
$stm = $connection->prepare($getResult);
$stm->bindValue(":kode_barang", $kode_barang);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);

/*
 * Get Barang
 */

$stmBarang = $connection->prepare("SELECT * FROM barang WHERE kode_barang = :kode_barang");
$stmBarang->bindValue(':kode_barang', $result['kode_barang']);
$stmBarang->execute();
$resultBarang = $stmBarang->fetch(PDO::FETCH_ASSOC);

/*
 * Defulat Barang 'Tidak diketahui'
 */

$barang = [
    'kode_barang' => $result['kode_barang'],
    'nama_brand' => 'Tidak Diketahui',
    'harga' => 0
];

if ($resultBarang) {
    $barang = [
        'kode_barang' => $resultBarang['kode_barang'],
        'nama_brand' => $resultBarang['nama_barang'],
        'harga' => $resultBarang['harga']
    ];
}

/*
 * Get User
 */
$stmUser = $connection->prepare("SELECT * FROM users WHERE id_user = :id_user");
$stmUser->bindValue(":id_user", $id_user);
$stmUser->execute();
$resultUser = $stmUser->fetch(PDO::FETCH_ASSOC);

/*
 * Defulat User 'Tidak diketahui'
 */

$user = [
    'id_user' => $result['id_user'],
    'username' => 'Tidak diketahui'
];

if ($resultUser) {
    $user = [
        'id_user' => $resultUser['id_user'],
        'username' => $resultUser['username']
    ];
}

/*
 * Update data barang dimasukkan ke keranjang
 * Stok barang berkurang
 */
try {
    $queryUpdate = "UPDATE barang SET stok_barang = stok_barang - :kuantitas WHERE kode_barang = :kode_barang";
    $stmUpdate = $connection->prepare($queryUpdate);
    $stmUpdate->bindValue(":kuantitas", $kuantitas, PDO::PARAM_INT);
    $stmUpdate->bindValue(":kode_barang", $kode_barang);
    $stmUpdate->execute();
} catch (Exception $exception) {
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}
/*
 * Transform result
 */

$dataFinal = [
    'id_keranjang' => $result['id_keranjang'],
    'id_user' => $user,
    'kode_barang' => $barang,
    'kuantitas' => $result['kuantitas'],
    'total_harga' => $resultBarang['harga'] * $result['kuantitas'],
    'id_transaksi' => $result['id_transaksi']
];

/**
 * Show output to client
 * Set status info true
 */

header('Content-Type: application/json');
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
echo json_encode($reply);