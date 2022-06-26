<?php

//Ini untuk koneksi PDO
//digunakan utk ngetuk pintu ke mysql

$dbserver = 'localhost';
$dbname = 'fetano';
$dbuser = 'root';
$dbpassword = '';
$dsn = "mysql:host={$dbserver};dbname={$dbname}";

$connection = null;

try {
    $connection = new PDO($dsn, $dbuser, $dbpassword);

} catch (Exception $exception) {
    die("Terjadi error : ".$exception->getMessage());
}

