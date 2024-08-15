<?php
require '../../helper/function.php';
require '../../helper/cek.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['idpermintaan'])) {
    $idpermintaan = $_POST['idpermintaan'];
    $email_logged = $_SESSION['email'];
    $role = $_SESSION['role'];

    $isSuperadmin1 = $email_logged === 'bm@plazaoleos.com' && $role === 'superadmin';
    $isSuperadmin2 = $email_logged === 'ce@plazaoleos.com' && $role === 'superadmin';

    if ($isSuperadmin1 && isset($_POST['status'])) {
        $status = $_POST['status'];
        $query_update = "UPDATE permintaan SET status = ? WHERE idpermintaan = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("ii", $status, $idpermintaan);

        if ($stmt_update->execute()) {
            if ($status == 1) { // Jika status permintaan adalah 'Diterima'
                $query_barang = "SELECT * FROM barang_permintaan WHERE idpermintaan = ?";
                $stmt_barang = $conn->prepare($query_barang);
                $stmt_barang->bind_param("i", $idpermintaan);
                $stmt_barang->execute();
                $result_barang = $stmt_barang->get_result();

                while ($row_barang = $result_barang->fetch_assoc()) {
                    $idbarang = $row_barang['idbarang'];
                    $namabarang = $row_barang['namabarang'];
                    $qty = $row_barang['qtypermintaan'];
                    $keterangan = $row_barang['keterangan'];
                    $unit = $row_barang['unit'];
                    $tanggal = $row_barang['tanggal'];
                    $status_masuk = 0;

                    $query_check_stock = "SELECT idbarang FROM stock WHERE namabarang = ?";
                    $stmt_check_stock = $conn->prepare($query_check_stock);
                    $stmt_check_stock->bind_param("s", $namabarang);
                    $stmt_check_stock->execute();
                    $result_check_stock = $stmt_check_stock->get_result();

                    if ($result_check_stock->num_rows > 0) {
                        $row_stock = $result_check_stock->fetch_assoc();
                        $idbarang = $row_stock['idbarang'];
                    } else {
                        $stock = 0;
                        $query_insert_stock = "INSERT INTO stock (namabarang, unit, stock) VALUES (?, ?, ?)";
                        $stmt_insert_stock = $conn->prepare($query_insert_stock);
                        $stmt_insert_stock->bind_param("sss", $namabarang, $unit, $stock);
                        $stmt_insert_stock->execute();
                        $idbarang = $stmt_insert_stock->insert_id;
                    }

                    $query_insert_masuk = "INSERT INTO masuk (idbarang, tanggal, keterangan, qty, status) VALUES (?, ?, ?, ?, ?)";
                    $stmt_insert_masuk = $conn->prepare($query_insert_masuk);
                    $stmt_insert_masuk->bind_param("isssi", $idbarang, $tanggal, $keterangan, $qty, $status_masuk);
                    $stmt_insert_masuk->execute();
                }

                $iduser_logged = $_SESSION['iduser'];
                $activity = "$email_logged mengubah status permintaan dengan id ($idpermintaan) menjadi Diterima dan memasukkan barang ke dalam tabel barang_masuk serta stock";
                catatLog($conn, $activity, $iduser_logged);
            } elseif ($status == 2) { // Jika status permintaan adalah 'Ditolak'
                $query_delete_stock = "DELETE FROM stock WHERE idbarang IN (SELECT idbarang FROM barang_permintaan WHERE idpermintaan = ?)";
                $stmt_delete_stock = $conn->prepare($query_delete_stock);
                $stmt_delete_stock->bind_param("i", $idpermintaan);
                $stmt_delete_stock->execute();

                $query_delete_masuk = "DELETE FROM masuk WHERE idbarang IN (SELECT idbarang FROM barang_permintaan WHERE idpermintaan = ?)";
                $stmt_delete_masuk = $conn->prepare($query_delete_masuk);
                $stmt_delete_masuk->bind_param("i", $idpermintaan);
                $stmt_delete_masuk->execute();

                $iduser_logged = $_SESSION['iduser'];
                $activity = "$email_logged mengubah status permintaan dengan id ($idpermintaan) menjadi Ditolak dan menghapus stok serta data masuk terkait";
                catatLog($conn, $activity, $iduser_logged);
            }

            header('Location: permintaan.php');
            exit();
        } else {
            echo "Gagal mengupdate status permintaan: " . $stmt_update->error;
        }
    } elseif ($isSuperadmin2 && isset($_POST['status2'])) {
        $status2 = $_POST['status2'];
        $query_update = "UPDATE permintaan SET status2 = ? WHERE idpermintaan = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("ii", $status2, $idpermintaan);
        $stmt_update->execute();

        $iduser_logged = $_SESSION['iduser'];
        $activity = "$email_logged mengubah status permintaan dengan id ($idpermintaan) menjadi " . ($status2 == 1 ? 'Disetujui' : 'Tidak Disetujui');
        catatLog($conn, $activity, $iduser_logged);

        header('Location: permintaan.php');
        exit();
    } else {
        echo "Permintaan tidak valid atau role tidak sesuai";
        exit();
    }
} else {
    echo "Permintaan tidak valid";
}
?>
