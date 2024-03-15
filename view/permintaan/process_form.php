<?php

require '../../helper/function.php';
require '../../helper/cek.php';

$message = "";

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
    } elseif (isset($_POST['barangbaru'])) {
        $idpermintaan = $_POST['idpermintaan'];
        $namabarang = $_POST['namabarang'];
        $unit = $_POST['unit'];
        $qtypermintaan = $_POST['qtypermintaan'];
        $keterangan = $_POST['keterangan'];

        tambahBarangBaru($idpermintaan, $namabarang, $unit, $qtypermintaan, $keterangan);
    } else {
        $message = "Tidak ada tindakan yang sesuai";
    }

    $_SESSION['message'] = $message;

    header('Location: permintaan.php');
    exit;
} else {
    echo "Metode yang digunakan bukan POST";
}
