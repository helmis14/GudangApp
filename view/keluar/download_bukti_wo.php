<?php
require_once '../../helper/connection.php';

if (isset($_GET['id'])) {
    $idpermintaan = $_GET['id'];



    $query = "SELECT bukti_wo FROM permintaan_keluar WHERE idpermintaan = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idpermintaan);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="bukti_wo_' . $idpermintaan . '.jpg"');

        echo base64_decode($row['bukti_wo']);
        exit;
    } else {
        echo "Gambar tidak ditemukan.";
    }
} else {
    echo "ID barang tidak valid.";
}
