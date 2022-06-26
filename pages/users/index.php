<?php

include "../../config/koneksi.php";

/**
 * @var $connection PDO
 */

try {
    $statement = $connection->prepare("SELECT * FROM users ORDER BY created_at ");
    $isOk = $statement->execute();
    $resultUser = $statement->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Transform hasil query table users
     */

    foreach ($resultUser as $user) {
        $finalResults[] = [
            'id_user' => $user['id_user'],
            'username' => $user['username'],
            'password' => $user['password'],
            'no_hp' => $user['no_hp'],
            'email' => $user['email'],
            'alamat' => $user['alamat'],
            'tanggal_lahir' => $user['tanggal_lahir'],
            'jenis_kelamin' => $user['jenis_kelamin'],
            'created_at' => $user['created_at']
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