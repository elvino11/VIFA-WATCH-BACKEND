<?php

include "../../config/koneksi.php";

/**
 * @var $connection PDO
 */

/*
 * Validate http method
 */

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    header('Content-Type: application/json');
    http_response_code(400);
    $reply['error'] = 'PATCH method required';
    echo json_encode($reply);
    exit();
}

/**
 * Get input data PATCH
 */

$formData = [];
parse_str(file_get_contents('php://input'), $formData);

$id = $formData['id'] ?? '';
$nama_brand = $formData['nama_brand'] ?? '';
$gambar_brand = $formData['gambar_brand'] ?? '';

/**
 * Validation int value
 */
$idFilter = filter_var($id, FILTER_VALIDATE_INT);

/**
 * Validation empty fields
 */

$isValidated = true;
if ($idFilter === false) {
    $reply['error'] = "ID harus format INT";
    $isValidated = false;
}
if (empty($id)) {
    $reply['error'] = "ID harus diisi";
    $isValidated = false;
}
if (empty($nama_brand)) {
    $reply['error'] = "Nama brand harus diisi";
    $isValidated = false;
}
if (empty($gambar_brand)) {
    $reply['error'] = "Gambar brand harus diisi";
    $isValidated = false;
}

/*
 * Jika filter gagal
 */
if(!$isValidated){
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}
/**
 * METHOD OK
 * Validation OK
 * Check if data is exist
 */

try {
    $queryCheck = "SELECT * FROM brand WHERE id = :id";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':id', $idFilter);
    $statement->execute();
    $row = $statement->rowCount();

    /**
     * Jika data tidak ditemukan
     * row === 0
     */

    if ($row === 0) {
        $reply['error'] = 'Data dengan ID : '. $idFilter. ' tidak ditemukan';
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
 * Prepare Query
 */

try {
    $fields = [];
    $query = "UPDATE brand SET nama_brand = :nama_brand, gambar_brand = :gambar_brand WHERE id = :id";
    $statement = $connection->prepare($query);
    /**
     * Bind Params
     */
    $statement->bindValue(":id", $id);
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

if(!$isOk){
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}

/**
 * Show output to client
 */

header('Content-Type: application/json');
$reply['status'] = $isOk;
echo json_encode($reply);