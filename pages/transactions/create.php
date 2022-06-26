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

$id_transaksi = $_POST['id_transaksi'];
$total_harga = $_POST['total_harga'] ?? 0;
$diskon = $_POST['diskon'] ?? 0;
$total_bayar = $total_harga - $diskon;
$pembayaran = $_POST['pembayaran'] ?? 0;
$kembalian = $pembayaran - $total_bayar;
$id_user = $_POST['id_user'] ?? 0;

/**
 * Validation int value
 */

$total_hargaFilter = filter_var($total_harga, FILTER_VALIDATE_INT);
$pembayaranFilter = filter_var($pembayaran, FILTER_VALIDATE_INT);
$id_userFilter = filter_var($id_user, FILTER_VALIDATE_INT);

/**
 * Validation empty fields
 */

$isValidated = true;
if ($total_hargaFilter === false) {
    $reply['error'] = "Total harga harus format INT";
    $isValidated = false;
}
if ($pembayaranFilter === false) {
    $reply['error'] = "Pembayaran harus format INT";
    $isValidated = false;
}
if ($id_userFilter === false) {
    $reply['error'] = "ID user harus format INT";
    $isValidated = false;
}
if (empty($id_transaksi)) {
    $reply['error'] = "ID transaksi harus diisi";
    $isValidated = false;
}
if (empty($total_harga)) {
    $reply['error'] = 'Total harga harus diisi';
    $isValidated = false;
}
if (empty($pembayaran)) {
    $reply['error'] = 'Pembayaran harus diisi';
    $isValidated = false;
}
if (empty($id_user)) {
    $reply['error'] = 'ID user harus diisi';
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
    $query = "INSERT INTO transaksi (id_transaksi, total_harga, diskon, total_bayar, pembayaran, kembalian, id_user) 
VALUES (:id_transaksi, :total_harga, :diskon, :total_bayar, :pembayaran, :kembalian, :id_user)";

    $statement = $connection->prepare($query);

    /**
     * Bind params
     */
    $statement->bindValue(":id_transaksi", $id_transaksi);
    $statement->bindValue(":total_harga", $total_harga, PDO::PARAM_INT);
    $statement->bindValue(":diskon", $diskon, PDO::PARAM_INT);
    $statement->bindValue(":total_bayar", $total_bayar);
    $statement->bindValue(":pembayaran", $pembayaran, PDO::PARAM_INT);
    $statement->bindValue(":kembalian", $kembalian);
    $statement->bindValue(":id_user", $id_user, PDO::PARAM_INT);


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

$getResult = "SELECT * FROM transaksi WHERE id_user = :id_user";
$stm = $connection->prepare($getResult);
$stm->bindValue(":id_user", $id_user);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);

/*
 * Get keranjang
 */

$stmKeranjang = $connection->prepare("SELECT * FROM keranjang WHERE id_user = :id_user");
$stmKeranjang->bindValue(":id_user", $id_user);
$stmKeranjang->execute();
$resultKeranjang = $stmKeranjang->fetch(PDO::FETCH_ASSOC);

/*
 * Defulat Keranjang 'Tidak diketahui'
 */

$keranjang = [
    'id_user' => 'Tidak Diketahui'
];

if ($resultKeranjang) {
    $keranjang = [
        'id_user' => $resultKeranjang['id_user']
    ];
}

/*
 * Transform result
 */


$dataFinal = [
    'id_transaksi' => $result['id_transaksi'],
    'tanggal_transaksi' => $result['tanggal_transaksi'],
    'total_harga' => $result['total_harga'],
    'diskon' => $result['diskon'],
    'total_bayar' => $result['total_bayar'],
    'pembayaran' => $result['pembayaran'],
    'kembalian' => $result['kembalian'],
    'id_user' => $keranjang
];

/**
 * Show output to client
 * Set status info true
 */

header('Content-Type: application/json');
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
echo json_encode($reply);