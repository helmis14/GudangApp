<?php
require 'function.php';


// Periksa apakah pengguna sudah login
if (isset($_SESSION['iduser']) && isset($_SESSION['email'])) {
    // Data pengguna yang akan digunakan untuk mencatat log
    $iduser = $_SESSION['iduser'];
    $email = $_SESSION['email'];

    // Catat log logout
    $activity = "Logout berhasil: $email";
    catatLog($conn, $activity, $iduser);

    // Hapus semua data sesi
    session_destroy();
}

// Redirect ke halaman login
header('location:login.php');
