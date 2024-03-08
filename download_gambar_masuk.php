<?php
require 'function.php';
require 'cek.php';
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


if (isset($_GET['id'])) {
    $idmasuk = $_GET['id'];


    $database_host = $_ENV['DATABASE_HOST'];
    $database_user = $_ENV['DATABASE_USER'];
    $database_pass = $_ENV['DATABASE_PASS'];
    $database_name = $_ENV['DATABASE_NAME'];
    $conn = mysqli_connect($database_host, $database_user, $database_pass, $database_name);

    $query = "SELECT bukti_masuk_base64 FROM masuk WHERE idmasuk = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idmasuk);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="gambar_masuk_' . $idmasuk . '.jpg"');

        echo base64_decode($row['bukti_masuk_base64']);
        exit;
    } else {
        echo "Gambar tidak ditemukan.";
    }
} else {
    echo "ID barang tidak valid.";
}
