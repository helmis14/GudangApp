<?php
require '../../helper/function.php';
require '../../helper/cek.php';

if (isset($_POST['idm']) && isset($_POST['status'])) {
    $idm = $_POST['idm'];
    $status = $_POST['status'];

    $update_query = "UPDATE masuk SET status = $status WHERE idmasuk = $idm";
    $result_update = mysqli_query($conn, $update_query);

    if ($result_update) {
        if ($status == 1) {
            $query_qty = "SELECT qty, idbarang FROM masuk WHERE idmasuk = $idm";
            $result_qty = mysqli_query($conn, $query_qty);
            $row_qty = mysqli_fetch_assoc($result_qty);
            $qty = $row_qty['qty'];
            $idbarang = $row_qty['idbarang'];

            $update_stock_query = "UPDATE stock SET stock = stock + $qty WHERE idbarang = $idbarang";
            $result_update_stock = mysqli_query($conn, $update_stock_query);

            if (!$result_update_stock) {
                echo "Gagal memperbarui stok";
                exit;
            }
        }

        header("Location: barang_masuk.php");
        exit;
    } else {
        echo "Gagal memperbarui status barang masuk";
    }
} else {
    echo "Data tidak lengkap";
}


