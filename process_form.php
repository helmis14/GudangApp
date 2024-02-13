<?php


require 'function.php';
require 'cek.php';

// Inisialisasi pesan default
$message = "";

// Pastikan formulir dikirimkan menggunakan metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['deletebarang'])) {
        $idbarang_to_delete = $_POST['deletebarang'];
        $is_deleted = delete_barang($idbarang_to_delete);

        if ($is_deleted) {
            $message = "Barang berhasil dihapus";
        } else {
            $message = "Gagal menghapus barang";
        }
    } elseif (isset($_POST['updatebarangpermin'])) {
        $idbarang_to_update = $_POST['updatebarangpermin'];
        $index = array_search($idbarang_to_update, $_POST['idbarang']);

        $namabarang = $_POST['namabarang'][$index];
        $unit = $_POST['unit'][$index];
        $qty = $_POST['qtypermintaan'][$index];
        $keterangan = $_POST['ket'][$index];
        $is_updated = update_barangpermin($idbarang_to_update, $namabarang, $unit, $qty, $keterangan);

        if ($is_updated) {
            $message = "Barang berhasil diperbarui";
        } else {
            $message = "Gagal memperbarui barang";
        }
    } else {
        $message = "Tidak ada tindakan yang sesuai";
    }

    // Set pesan sesuai dengan keberhasilan atau kegagalan operasi
    $_SESSION['message'] = $message;

    // Redirect ke halaman tujuan
    header('Location: permintaan.php');
    exit;
} else {
    echo "Metode yang digunakan bukan POST";
}
