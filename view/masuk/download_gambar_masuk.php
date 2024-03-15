<?php
require_once '../../helper/connection.php';

if (isset($_GET['id'])) {
    $idmasuk = $_GET['id'];



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
