<?php
require_once '../../helper/connection.php';

if (isset($_GET['id'])) {
    $idretur = $_GET['id'];



    $query = "SELECT gambar_base64 FROM retur WHERE idretur = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idretur);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="gambar_retur_' . $idretur . '.jpg"');

        echo base64_decode($row['gambar_base64']);
        exit;
    } else {
        echo "Gambar tidak ditemukan.";
    }
} else {
    echo "ID barang tidak valid.";
}
