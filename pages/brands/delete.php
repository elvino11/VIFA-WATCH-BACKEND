<?php

include "../../config/koneksi.php";

/**
 * @var $connection PDO
 */

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(400);
    $reply['error'] = 'DELETE method required';
    echo json_encode($reply);
    exit();
}

/**
 * Get input data from RAW data
 */
$data = file_get_contents('php://input');
$res = [];
parse_str($data, $res);
$id = $res['id'] ?? '';

/**
 * Validate int value
 */
$idFilter = filter_var($id, FILTER_VALIDATE_INT);

/**
 * Validation empty fields
 */
$isValidated = true;
if($idFilter === false){
    $reply['error'] = "ID harus format INT";
    $isValidated = false;
}
if(empty($id)){
    $reply['error'] = 'ID harus diisi';
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
 *
 * Cek apakah brand tersedia
 */

try {
    $queryCheck = "SELECT * FROM brand WHERE id = :id";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':id', $id);
    $statement->execute();
    $row = $statement->rowCount();

    /**
     * Jika data tidak ditemukan
     * rowcount == 0
     */

    if ($row === 0) {
        $reply['error'] = 'Data dengan id : '. $id. ' tidak ditemukan';
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
 * Hapus data
 */

try {
    $queryCheck = "DELETE FROM brand where id = :id";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':id', $id);
    if (!$statement->execute()) {
        $reply['error'] = $statement->errorInfo();
        echo json_encode($reply);
        http_response_code(400);
    }
} catch (Exception $exception) {
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/*
 * Send output
 */
header('Content-Type: application/json');
$reply['status'] = true;
echo json_encode($reply);