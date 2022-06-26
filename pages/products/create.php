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

$kode_barang = $_POST['kode_barang'] ?? '';
$nama_barang = $_POST['nama_barang'] ?? '';
$brand = $_POST['brand'] ?? 0;
$stok_barang = $_POST['stok_barang'] ?? 0;
$harga = $_POST['harga'] ?? 0;
$tanggal = $_POST['tanggal'] ?? date('Y-m-d');
$deskripsi = $_POST['deskripsi'] ?? '';
$gambar = $_POST['gambar'] ?? '';
/*$gambar = $_FILES['gambar'] ?? '';
$file_tmp = $_FILES['gambar']['tmp_name'] ?? '';
move_uploaded_file($file_tmp, '../../file/'.$gambar);*/

/**
 * Validation int value
 */
$stok_barangFilter = filter_var($stok_barang, FILTER_VALIDATE_INT);
$hargaFilter = filter_var($harga, FILTER_VALIDATE_INT);



/**
 * Validation empty fields
 */
$isValidated = true;
if ($stok_barangFilter === false) {
    $reply['error'] = "Stok harus format INT";
    $isValidated = false;
}
if ($harga === false) {
    $reply['error'] = "Harga harus format INT";
    $isValidated = false;
}
if (empty($kode_barang)) {
    $reply['error'] = 'Kode Barang harus diisi';
    $isValidated = false;
}
if (empty($nama_barang)) {
    $reply['error'] = 'Nama Barang harus diisi';
    $isValidated = false;
}
if (empty($stok_barang)) {
    $reply['error'] = 'Stok Barang harus diisi';
    $isValidated = false;
}
if (empty($harga)) {
    $reply['error'] = 'Harga Barang harus diisi';
    $isValidated = false;
}
if (empty($deskripsi)) {
    $reply['error'] = 'Deskripsi harus diisi';
    $isValidated = false;
}
if (empty($gambar)) {
    $reply['error'] = 'Gambar harus diisi';
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
    $query = "INSERT INTO barang (kode_barang, nama_barang, brand, stok_barang, harga, tanggal, deskripsi, gambar)
VALUES (:kode_barang, :nama_barang, :brand, :stok_barang, :harga, :tanggal, :deskripsi, :gambar)";

    $statement = $connection->prepare($query);

    /**
     * Bind params
     */
    $statement->bindValue(":kode_barang", $kode_barang);
    $statement->bindValue(":nama_barang", $nama_barang);
    $statement->bindValue(":brand", $brand, PDO::PARAM_INT);
    $statement->bindValue(":stok_barang", $stok_barang, PDO::PARAM_INT);
    $statement->bindValue(":harga", $harga, PDO::PARAM_INT);
    $statement->bindValue(":tanggal", $tanggal);
    $statement->bindValue(":deskripsi", $deskripsi);
    $statement->bindValue(":gambar", $gambar);

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

$getResult = "SELECT * FROM barang WHERE kode_barang = :kode_barang";
$stm = $connection->prepare($getResult);
$stm->bindValue(':kode_barang', $kode_barang);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);


/*
 * Get Brand
 */

$stmBrand = $connection->prepare("SELECT * FROM brand where id = :id");
$stmBrand->bindValue(':id', $result['brand']);
$stmBrand->execute();
$resulBrand = $stmBrand->fetch(PDO::FETCH_ASSOC);

/*
 * Defulat Brand 'Tidak diketahui'
 */

$brand = [
    'id' => $result['brand'],
    'nama_brand' => 'Tidak diketahui',
    'gambar_brand' => 'Tidak diketahui'
];

if ($resulBrand) {
    $brand = [
        'id' => $resulBrand['id'],
        'nama_brand' => $resulBrand['nama_brand'],
        'gambar_brand' => $resulBrand['gambar_brand']
    ];
}

/*
 * Transform result
 */

$dataFinal = [
    'kode_barang' => $result['kode_barang'],
    'nama_barang' => $result['nama_barang'],
    'brand' => $brand,
    'stok_barang' => $result['stok_barang'],
    'harga' => $result['harga'],
    'tanggal' => $result['tanggal'],
    'deskripsi' => $result['deskripsi'],
    'gambar' => $result['gambar'],
    'created_at' => $result['created_at']
];

/**
 * Show output to client
 * Set status info true
 */

header('Content-Type: application/json');
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
echo json_encode($reply);

