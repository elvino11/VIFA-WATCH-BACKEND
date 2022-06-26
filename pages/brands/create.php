<?php

include "../../config/koneksi.php";

/**
 * @var $connection PDO
 */

/*
 * Validate http method
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    $reply['error'] = 'POST method required';
    echo json_encode($reply);
    exit();
}
/**
 * Get input data POST
 */

$nama_brand = $_POST['nama_brand'] ?? '';
$gambar_brand = $_POST['gambar_brand'] ?? '';

/**
 * Validation empty fields
 */
$isValidated = true;
if (empty($nama_brand)) {
    $reply['error'] = 'Nama brand harus diisi';
    $isValidated = false;
}
if (empty($gambar_brand)) {
    $reply['error'] = 'Gambar brand harus diisi';
    $isValidated = false;
}

/*
 * Jika filter gagal
 */

if(!$isValidated) {
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/**
 * Method Ok
 * Validation OK
 * Prepare query
 */

try {
    $query = "INSERT INTO brand (nama_brand, gambar_brand) VALUES (:nama_brand, :gambar_brand)";
    $statement = $connection->prepare($query);
    /**
     * Bind Params
     */
    $statement->bindValue(":nama_brand", $nama_brand);
    $statement->bindValue(":gambar_brand", $gambar_brand);
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

if (!$isOk) {
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}

/*
 * Get last data
 */

$lastId = $connection->lastInsertId();
$getResult = "SELECT * FROM brand WHERE id = :id";
$stm = $connection->prepare($getResult);
$stm->bindValue(':id', $lastId);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);

/**
 * Show output to client
 * Set status info true
 */

header('Content-Type: application/json');
$reply['data'] = $result;
$reply['status'] = $isOk;
echo json_encode($reply);