<?php
// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "stokbarangs");


// Periksa koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Periksa apakah ID data yang akan dihapus telah diterima dari AJAX
if (isset($_POST['idbarang'])) {
    $idToDelete = $_POST['idbarang'];

    // Query untuk menghapus data barang berdasarkan ID
    $query = "DELETE FROM barang_permintaan WHERE idbarang = $idToDelete";


    if (mysqli_query($conn, $query)) {
        echo "Data berhasil dihapus";
    } else {
        echo "Gagal menghapus data: " . mysqli_error($conn);
    }
} else {
    echo "ID data tidak diterima";
}

// Tutup koneksi ke database
mysqli_close($conn);
