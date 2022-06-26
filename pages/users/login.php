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
$username = $_GET['username'] ?? '';
$password = $_GET['password'] ?? '';

if (empty($username)) {
    $reply['error'] = 'Username Tidak Boleh Kosong';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}
if (empty($password)) {
    $reply['error'] = 'Password Tidak Boleh Kosong';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

try {
    $queryCheck = "SELECT * FROM users WHERE username = :username AND password = :password";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':username', $username);
    $statement->bindValue(':password', $password);
    $statement->execute();
    $row = $statement->rowCount();

    /**
     * Jika tidak ada data ditemukan
     * rowcount == 0
     */
    if ($row === 0) {
        $reply['error'] = 'User dengan : '.$username. ' tidak ditemukan !!';
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
    $reply['error'] = 'Username Tidak Ditemukan'.$username;
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
