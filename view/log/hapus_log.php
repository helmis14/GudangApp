<?php
require '../../helper/function.php';
require '../../helper/cek.php';


// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$query = "DELETE FROM log";
if ($conn->query($query) === TRUE) {
    // Catat aktivitas log
    $iduser_logged = $_SESSION['iduser'];
    $email_logged = $_SESSION['email'];
    $activity = "$email_logged menghapus semua data log";
    catatLog($conn, $activity, $iduser_logged);

    echo "Data log berhasil dihapus.";
} else {
    echo "Error: " . $conn->error;
}

// Tutup koneksi
$conn->close();
