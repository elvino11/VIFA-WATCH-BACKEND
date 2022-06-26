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

$kode_barang = $formData['kode_barang'] ?? '';
$nama_barang = $formData['nama_barang'] ?? '';
$brand = $formData['brand'] ?? 0;
$stok_barang = $formData['stok_barang'] ?? 0;
$harga = $formData['harga'] ?? 0;
$tanggal = $formData['tanggal'] ?? date('Y-m-d');
$deskrips = $formData['deskripsi'] ?? '';
$gambar = $formData['gambar'] ?? '';

/**
 * Validaion int value
 */
$stok_barangFilter = filter_var($stok_barang, FILTER_VALIDATE_INT);
$hargaFilter = filter_var($harga, FILTER_VALIDATE_INT);


/**
 * Validation Empty fields
 */
$isValidated = true;
if ($stok_barangFilter === false) {
    $reply['error'] = "Stok barang harus format INT";
    $isValidated = false;
}
if ($hargaFilter === false) {
    $reply['error'] = "Harga harus format INT";
    $isValidated = false;
}
if (empty($kode_barang)) {
    $reply['error'] = "Kode barang harus diisi";
    $isValidated = false;
}
if (empty($nama_barang)) {
    $reply['error'] = "Nama barang harus diisi";
    $isValidated = false;
}
if (empty($stok_barang)) {
    $reply['error'] = "Stok barang harus diisi";
    $isValidated = false;
}
if (empty($harga)) {
    $reply['error'] = "Harga barang harus diisi";
    $isValidated = false;
}
if (empty($deskrips)) {
    $reply['error'] = "Deskripsi barang harus diisi";
    $isValidated = false;
}
if (empty($gambar)) {
    $reply['error'] = "Gambar barang harus diisi";
    $isValidated = false;
}

/*
 * Jika filter gagal
 */

if (!$isValidated) {
    echo json_encode(400);
    exit(0);
}

/**
 * METHOD OK
 * Validation OK
 * Check if data is exist
 */

try {
    $queryCheck = "SELECT * FROM barang WHERE kode_barang = :kode_barang";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':kode_barang', $kode_barang);
    $statement->execute();
    $row = $statement->rowCount();

    /**
     * Jika data tidak ditemuka
     * Rowcount == 0
     */
    if ($row === 0) {
        $reply['error'] = 'Data dengan kode barang '.$kode_barang. ' tidak ditemukan';
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
    $fileds = [];
    $query = "UPDATE barang SET nama_barang = :nama_barang, brand = :brand, stok_barang = :stok_barang,
harga = :harga, tanggal = :tanggal, deskripsi = :deskripsi, gambar = :gambar WHERE kode_barang = :kode_barang";
    $statement = $connection->prepare($query);

    /**
     * Bind Params
     */
    $statement->bindValue(":kode_barang", $kode_barang);
    $statement->bindValue(":nama_barang", $nama_barang);
    $statement->bindValue(":brand", $brand, PDO::PARAM_INT);
    $statement->bindValue(":stok_barang", $stok_barang, PDO::PARAM_INT);
    $statement->bindValue(":harga", $harga, PDO::PARAM_INT);
    $statement->bindValue(":tanggal", $tanggal);
    $statement->bindValue(":deskripsi", $deskrips);
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

if (!$isOk) {
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}

/*
 * Get data
 */
$stmSelect = $connection->prepare("SELECT * FROM barang WHERE kode_barang = :kode_barang");
$stmSelect->bindValue(":kode_barang", $kode_barang);
$stmSelect->execute();
$dataBarang = $stmSelect->fetch(PDO::FETCH_ASSOC);

/*
 * Ambil data dari brand berdasarkan kolom brand
 */
$datFinal = [];
if ($dataBarang) {
    $stmBrand = $connection->prepare("SELECT * FROM brand WHERE id = :id");
    $stmBrand->bindValue(':id', $dataBarang['brand']);
    $stmBrand->execute();
    $resultBrand = $stmBrand->fetch(PDO::FETCH_ASSOC);
    /*
    * Defulat Brand 'Tidak diketahui'
    */
    $brand = [
        'id' => $dataBarang['brand'],
        'nama_brand' => 'Tidak diketahui'
    ];
    if ($resultBrand) {
        $brand = [
            'id' => $resultBrand['id'],
            'nama_brand' => $resultBrand['nama_brand']
        ];
    }

    /*
     * Transoform hasil query dari table barang dan brand
     * Gabungkan data berdasarkan kolom id brand
     * Jika id brand tidak ditemukan, default "tidak diketahui'
     */

    $datFinal = [
        'kode_barang' => $dataBarang['kode_barang'],
        'nama_barang' => $dataBarang['nama_barang'],
        'brand' => $brand,
        'stok_barang' => $dataBarang['stok_barang'],
        'harga' => $dataBarang['harga'],
        'tanggal' => $dataBarang['tanggal'],
        'deskripsi' => $dataBarang['deskripsi'],
        'gambar' => $dataBarang['gambar']
    ];
}

/**
 * Show output to client
 */

header('Content-Type: application/json');
$reply['data'] = $datFinal;
$reply['error'] = $isOk;
echo json_encode($reply);