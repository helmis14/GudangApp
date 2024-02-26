<?php

if (isset($_GET['id'])) {
    $id_permintaan = $_GET['id'];

    $conn = mysqli_connect("localhost", "root", "", "stokbarangs");

    $query = "SELECT bukti_base64 FROM permintaan WHERE idpermintaan = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_permintaan);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        header('Content-Type: image/jpeg');

        header('Content-Disposition: attachment; filename="gambar_permintaan_' . $id_permintaan . '.jpg"');

        echo base64_decode($row['bukti_base64']);
        exit;
    } else {
        echo "Gambar tidak ditemukan.";
    }
} else {
    echo "ID permintaan tidak valid.";
}
