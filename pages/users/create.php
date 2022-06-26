<?php

include "../../config/koneksi.php";

/**
 * @var $connection PDO
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    $reply['error'] = 'POST method required';
    echo json_encode($reply);
    exit();
}

/**
 * Get input data from POST
 */

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$no_hp = $_POST['no_hp'] ?? 0;
$email = $_POST['email'] ?? '';
$alamat = $_POST['alamat'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? date('dd/mm/yyyy');
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';

/**
 * Validate int value
 */
$no_hpFilter = filter_var($no_hp, FILTER_VALIDATE_INT);

/**
 * Validate empty value
 */
$isValidated = true;
if ($no_hpFilter === false) {
    $reply['error'] = "No Handphone harus angka";
    $isValidated = false;
}
if (empty($username)) {
    $reply['error'] = 'Username harus diisi';
    $isValidated = false;
}
if (empty($password)) {
    $reply['error'] = 'Password harus diisi';
    $isValidated = false;
}
if (empty($email)) {
    $reply['error'] = 'Email harus diisi';
    $isValidated = false;
}
if (empty($alamat)) {
    $reply['error'] = 'Alamat harus diisi';
    $isValidated = false;
}
if (empty($jenis_kelamin)) {
    $reply['error'] = 'Jenis Kelamin harus diisi';
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
 * Prepare Query
 */

try {
    $query = "INSERT INTO users (username, password, no_hp, email, alamat, tanggal_lahir, jenis_kelamin)
VALUES (:username, :password, :no_hp, :email, :alamat, :tanggal_lahir, :jenis_kelamin)";
    $statement = $connection->prepare($query);
    /**
     * Binding params
     */
    $statement->bindValue(":username", $username);
    $statement->bindValue(":password", $password);
    $statement->bindValue(":no_hp", $no_hp, PDO::PARAM_INT);
    $statement->bindValue(":email", $email);
    $statement->bindValue(":alamat", $alamat);
    $statement->bindValue(":tanggal_lahir", $tanggal_lahir);
    $statement->bindValue(":jenis_kelamin", $jenis_kelamin);

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
 * @see http://developer.mozila.org/en-US/docs/Web/HTTP/Status#client_error_responses
 */
if (!$isOk) {
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}

/*
 * Get last data
 */
$getResult = "SELECT * FROM users WHERE username = :username";
$stm = $connection->prepare($getResult);
$stm->bindValue(':username', $username);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);

/*
 * Transform final
 */
$dataFinal = [
    'id_user' => $result['id_user'],
    'username' => $result['password'],
    'no_hp' => $result['no_hp'],
    'email' => $result['email'],
    'alamat' => $result['alamat'],
    'tanggal_lahir' => $result['tanggal_lahir'],
    'jenis_kelamin' => $result['jenis_kelamin'],
    'created_at' => $result['created_at']
];

/**
 * Show output to clien
 * Set status info true
 */
header('Content-Type: application/json');
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
echo json_encode($reply);


