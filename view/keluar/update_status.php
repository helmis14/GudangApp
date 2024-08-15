<?php
require '../../helper/function.php';
require '../../helper/cek.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: ../../login.php');
    exit();
}

$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];
$email_logged = $_SESSION['email'];
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['idpermintaan'])) {
    $idpermintaan = $_POST['idpermintaan'];
    $role = $_SESSION['role'];

    $isSuperadmin =  $role === 'superadmin';
    $isGudang = $role === 'gudang';

    if ($isSuperadmin && isset($_POST['status'])) {
        $status = $_POST['status'];
        $query_update = "UPDATE permintaan_keluar SET status = ? WHERE idpermintaan = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("ii", $status, $idpermintaan);
        $stmt_update->execute();

        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged mengubah status permintaan dengan id ($idpermintaan) menjadi " . ($status == 1 ? 'Disetujui' : 'Tidak Disetujui');
        catatLog($conn, $activity, $iduser_logged);
    } elseif ($isGudang && isset($_POST['status2'])) {
        $status2 = $_POST['status2'];

        // Pastikan status sudah disetujui sebelum mengubah status2 dan mengurangi stok
        $query_check_status = "SELECT status FROM permintaan_keluar WHERE idpermintaan = ?";
        $stmt_check_status = $conn->prepare($query_check_status);
        $stmt_check_status->bind_param("i", $idpermintaan);
        $stmt_check_status->execute();
        $result_check_status = $stmt_check_status->get_result();
        $row_status = $result_check_status->fetch_assoc();

        if ($row_status['status'] == 1) {
            if ($status2 == 1) { // Jika status permintaan adalah 'Diterima'
                // Ambil data barang dari tabel keluar berdasarkan idpermintaan
                $query_barang = "SELECT * FROM keluar WHERE idpermintaan = ?";
                $stmt_barang = $conn->prepare($query_barang);
                $stmt_barang->bind_param("i", $idpermintaan);
                $stmt_barang->execute();
                $result_barang = $stmt_barang->get_result();

                // Loop untuk mengurangi stok barang
                $stok_cukup = true;
                while ($row_barang = $result_barang->fetch_assoc()) {
                    $idbarang = $row_barang['idbarang'];
                    $qty = $row_barang['qty'];

                    // Ambil data stok berdasarkan idbarang
                    $query_stock = "SELECT stock FROM stock WHERE idbarang = ?";
                    $stmt_stock = $conn->prepare($query_stock);
                    $stmt_stock->bind_param("i", $idbarang);
                    $stmt_stock->execute();
                    $result_stock = $stmt_stock->get_result();

                    if ($result_stock->num_rows > 0) {
                        $row_stock = $result_stock->fetch_assoc();
                        $current_stock = $row_stock['stock'];

                        // Kurangi stok dengan qty permintaan
                        $new_stock = $current_stock - $qty;
                        if ($new_stock < 0) {
                            $stok_cukup = false;
                            $error_message = "Stok tidak mencukupi untuk barang dengan idbarang: $idbarang";
                            break;
                        }
                    } else {
                        $stok_cukup = false;
                        $error_message = "Barang dengan idbarang $idbarang tidak ditemukan di stok";
                        break;
                    }
                }

                if ($stok_cukup) {
                    // Lanjutkan mengurangi stok dan mengupdate status2 permintaan
                    $result_barang->data_seek(0); // Kembali ke awal result set
                    while ($row_barang = $result_barang->fetch_assoc()) {
                        $idbarang = $row_barang['idbarang'];
                        $qty = $row_barang['qty'];

                        // Dapatkan stok terbaru sebelum menguranginya
                        $query_stock = "SELECT stock FROM stock WHERE idbarang = ?";
                        $stmt_stock = $conn->prepare($query_stock);
                        $stmt_stock->bind_param("i", $idbarang);
                        $stmt_stock->execute();
                        $result_stock = $stmt_stock->get_result();
                        $row_stock = $result_stock->fetch_assoc();
                        $current_stock = $row_stock['stock'];

                        $new_stock = $current_stock - $qty;

                        $query_update_stock = "UPDATE stock SET stock = ? WHERE idbarang = ?";
                        $stmt_update_stock = $conn->prepare($query_update_stock);
                        $stmt_update_stock->bind_param("ii", $new_stock, $idbarang);
                        $stmt_update_stock->execute();
                    }

                    // Update status2 permintaan
                    $query_update = "UPDATE permintaan_keluar SET status2 = ? WHERE idpermintaan = ?";
                    $stmt_update = $conn->prepare($query_update);
                    $stmt_update->bind_param("ii", $status2, $idpermintaan);
                    $stmt_update->execute();

                    $iduser_logged = $_SESSION['iduser'];
                    $email_logged = $_SESSION['email'];
                    $activity = "$email_logged mengubah status permintaan dengan id ($idpermintaan) menjadi disetujui dan mengurangi stok barang terkait";
                    catatLog($conn, $activity, $iduser_logged);
                } else {
                    $_SESSION['error_message'] = $error_message;
                    header("Location: barang_keluar.php");
                    exit();
                }
            } elseif ($status2 == 2) { // Jika status permintaan adalah 'Ditolak'
                // Update status2 permintaan
                $query_update = "UPDATE permintaan_keluar SET status2 = ? WHERE idpermintaan = ?";
                $stmt_update = $conn->prepare($query_update);
                $stmt_update->bind_param("ii", $status2, $idpermintaan);
                $stmt_update->execute();

                $iduser_logged = $_SESSION['iduser'];
                $email_logged = $_SESSION['email'];
                $activity = "$email_logged mengubah status permintaan dengan id ($idpermintaan) menjadi Ditolak";
                catatLog($conn, $activity, $iduser_logged);
            } else {
                $_SESSION['error_message'] = "Status permintaan tidak valid";
                header("Location: barang_keluar.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Status harus disetujui terlebih dahulu.";
            header("Location: barang_keluar.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Permintaan tidak valid";
        header("Location: barang_keluar.php");
        exit();
    }

    header('Location: barang_keluar.php');
    exit();
} else {
    $_SESSION['error_message'] = "ID permintaan tidak ditemukan";
    header("Location: barang_keluar.php");
    exit();
}
