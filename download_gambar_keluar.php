<?php

if (isset($_GET['id'])) {
    $idkeluar = $_GET['id'];

    $conn = mysqli_connect("localhost", "root", "", "stokbarangs");

    $query = "SELECT gambar_base64 FROM keluar WHERE idkeluar = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idkeluar);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="gambar_keluar_' . $idkeluar . '.jpg"');

        echo base64_decode($row['gambar_base64']);
        exit;
    } else {
        echo "Gambar tidak ditemukan.";
    }
} else {
    echo "ID barang tidak valid.";
}
