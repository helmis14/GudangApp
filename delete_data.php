<?php
require 'function.php';
require 'cek.php';
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$database_host = $_ENV['DATABASE_HOST'];
$database_user = $_ENV['DATABASE_USER'];
$database_pass = $_ENV['DATABASE_PASS'];
$database_name = $_ENV['DATABASE_NAME'];
$conn = mysqli_connect($database_host, $database_user, $database_pass, $database_name);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if (isset($_POST['idbarang'])) {
    $idToDelete = $_POST['idbarang'];

    $query = "DELETE FROM barang_permintaan WHERE idbarang = $idToDelete";


    if (mysqli_query($conn, $query)) {
        echo "Data berhasil dihapus";
    } else {
        echo "Gagal menghapus data: " . mysqli_error($conn);
    }
} else {
    echo "ID data tidak diterima";
}


mysqli_close($conn);
