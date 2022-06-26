<?php

include "../../config/koneksi.php";

/**
 * @var $connection PDO
 */

/*
 * Valid http method
 */
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    header('Content-Type: application/json');
    http_response_code(400);
    $reply['error'] = 'DELETE method required';
    echo json_encode($reply);
    exit();
}

$dataFinal = [];
$id_user = $_GET['id_user'] ?? 0;

$id_userFilter = filter_var($id_user, FILTER_VALIDATE_INT);

if ($id_userFilter === false) {
    $reply['error'] = "ID user harus format INT";
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}
if (empty($id_user)) {
    $reply['error'] = 'Username Tidak Boleh Kosong';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

try {
    $queryCheck = "SELECT * FROM users WHERE id_user = :id_user";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':id_user', $id_user);
    $statement->execute();
    $row = $statement->rowCount();

    /**
     * Jika tidak ada data ditemukan
     * rowcount == 0
     */
    if ($row === 0) {
        $reply['error'] = 'User dengan : '.$id_user. ' tidak ditemukan !!';
        echo json_encode($reply);
        http_response_code(400);
        exit(0);
    }

    $dataUser = $statement->fetch(PDO::FETCH_ASSOC);

    /*
     * Transform hasil query dari table users
     */

    $dataFinal = [
        'id_user' => $dataUser['id_user'],
        'username' => $dataUser['username'],
        'password' => $dataUser['password'],
        'no_hp' => $dataUser['no_hp'],
        'email' => $dataUser['email'],
        'alamat' => $dataUser['alamat'],
        'tanggal_lahir' => $dataUser['tanggal_lahir'],
        'jenis_kelamin' => $dataUser['jenis_kelamin'],
        'created_at' => $dataUser['created_at']
    ];
} catch (Exception $exception) {
    $reply['error'] = 'Username Tidak Ditemukan'.$id_user;
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/*
 * Otherwise show data
 */

header('Content-Type: application/json');
$reply['status'] = true;
$reply['data'] = $dataFinal;
echo json_encode($reply);
