<?php

require 'function.php';
require 'cek.php';

$message = "";

$idkeluar_to_delete = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['deletebarangkeluar'])) {
        $idkeluar_to_delete = $_POST['deletebarangkeluar'];


        if (isset($_POST['idkeluar']) && is_array($_POST['idkeluar'])) {
            $idkeluar_array = $_POST['idkeluar'];

            foreach ($idkeluar_array as $idkeluar) {
                $is_deleted = delete_barang_keluar($idkeluar);

                if ($is_deleted) {
                    $message = "Gagal menghapus barang";
                    break;
                }
            }
        }
        if ($is_deleted) {
            $message = "Barang berhasil dihapus";
        } else {
            $message = "Tidak ada idkeluar yang diterima";
        }
    } elseif (isset($_POST['updatebarangkeluar'])) {
        // Loop untuk setiap barang yang akan diubah
        foreach ($_POST['idkeluar'] as $index => $idkeluar) {
            $idbarang = $_POST['idbarang'][$index];
            $penerima = $_POST['penerima'][$index];
            $qty = $_POST['qty'][$index];
            $keterangan = $_POST['keterangan'][$index];

            // Panggil fungsi untuk memperbarui barang keluar
            if (update_barang_keluar($idkeluar, $idbarang, $penerima, $qty, $keterangan)) {
                $message = "Barang berhasil diperbarui";
            } else {
                $message = "Gagal memperbarui barang";
                // Handle error jika diperlukan
            }
        }
    } elseif (isset($_POST['barangbarukeluar'])) {
        $idpermintaan = $_POST['idpermintaan'];
        $idbarang = $_POST['barangnya'];
        $penerima = $_POST['penerima'];
        $qty = $_POST['qty'];
        $keterangan = $_POST['keterangan'];

        tambahBarangBaruKeluar($idpermintaan, $idbarang, $penerima, $qty, $keterangan);
    } else {
        $message = "Tidak ada tindakan yang sesuai";
    }

    $_SESSION['message'] = $message;

    header('Location: barang_keluar.php');
    exit;
} else {
    echo "Metode yang digunakan bukan POST";
}
