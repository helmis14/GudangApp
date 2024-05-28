<?php
require '../../helper/function.php';
require '../../helper/cek.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['idpermintaan']) && isset($_POST['status'])) {
    $idpermintaan = $_POST['idpermintaan'];
    $status = $_POST['status'];

    if ($status == 1) { // Jika status permintaan adalah 'Diterima'
        // Update status permintaan
        $query = "UPDATE permintaan SET status = ? WHERE idpermintaan = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $status, $idpermintaan);

        if ($stmt->execute()) {
            // Ambil data barang dari tabel barang_permintaan berdasarkan idpermintaan
            $query_barang = "SELECT * FROM barang_permintaan WHERE idpermintaan = ?";
            $stmt_barang = $conn->prepare($query_barang);
            $stmt_barang->bind_param("i", $idpermintaan);
            $stmt_barang->execute();
            $result_barang = $stmt_barang->get_result();

            // Loop untuk memasukkan data barang ke dalam tabel barang_masuk dan stock
            while ($row_barang = $result_barang->fetch_assoc()) {
                $idbarang = $row_barang['idbarang'];
                $namabarang = $row_barang['namabarang'];
                $qty = $row_barang['qtypermintaan'];
                $keterangan = $row_barang['keterangan'];
                $unit = $row_barang['unit'];
                $tanggal = $row_barang['tanggal'];
                $status = 0;

                // Periksa apakah nama barang sudah ada di stok
                $query_check_stock = "SELECT idbarang FROM stock WHERE namabarang = ?";
                $stmt_check_stock = $conn->prepare($query_check_stock);
                $stmt_check_stock->bind_param("s", $namabarang);
                $stmt_check_stock->execute();
                $result_check_stock = $stmt_check_stock->get_result();

                if ($result_check_stock->num_rows > 0) {
                    // Jika nama barang sudah ada, ambil idbarang dari stok
                    $row_stock = $result_check_stock->fetch_assoc();
                    $idbarang = $row_stock['idbarang'];
                } else {
                    // Jika nama barang belum ada, tambahkan ke dalam stok dengan qty 0
                    $stock = 0;
                    $query_insert_stock = "INSERT INTO stock (namabarang, unit, stock) VALUES (?, ?, ?)";
                    $stmt_insert_stock = $conn->prepare($query_insert_stock);
                    $stmt_insert_stock->bind_param("sss", $namabarang, $unit, $stock);
                    $stmt_insert_stock->execute();

                    // Ambil idbarang yang baru saja ditambahkan
                    $idbarang = $stmt_insert_stock->insert_id;
                }

                // Masukkan barang ke dalam tabel masuk dengan idbarang yang sesuai
                $query_insert_masuk = "INSERT INTO masuk (idbarang, tanggal, keterangan, qty, status) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert_masuk = $conn->prepare($query_insert_masuk);
                $stmt_insert_masuk->bind_param("isssi", $idbarang, $tanggal, $keterangan, $qty, $status);
                $stmt_insert_masuk->execute();
            }

            // Catat log aktivitas
            $iduser_logged = $_SESSION['iduser'];
            $email_logged = $_SESSION['email'];
            $activity = "$email_logged mengubah status permintaan dengan id ($idpermintaan) menjadi Diterima dan memasukkan barang ke dalam tabel barang_masuk serta stock";
            catatLog($conn, $activity, $iduser_logged);

            header('Location: permintaan.php');
            exit();
        } else {
            echo "Gagal mengupdate status permintaan: " . $stmt->error;
        }
    } elseif ($status == 2) { // Jika status permintaan adalah 'Ditolak'
        // Hapus stok terkait dengan permintaan yang ditolak
        $query_delete_stock = "DELETE FROM stock WHERE idbarang IN (SELECT idbarang FROM barang_permintaan WHERE idpermintaan = ?)";
        $stmt_delete_stock = $conn->prepare($query_delete_stock);
        $stmt_delete_stock->bind_param("i", $idpermintaan);
        $stmt_delete_stock->execute();

        // Hapus data masuk terkait dengan permintaan yang ditolak
        $query_delete_masuk = "DELETE FROM masuk WHERE idbarang IN (SELECT idbarang FROM barang_permintaan WHERE idpermintaan = ?)";
        $stmt_delete_masuk = $conn->prepare($query_delete_masuk);
        $stmt_delete_masuk->bind_param("i", $idpermintaan);
        $stmt_delete_masuk->execute();

        // Update status permintaan menjadi 'Ditolak'
        $query_update = "UPDATE permintaan SET status = ? WHERE idpermintaan = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("ii", $status, $idpermintaan);
        $stmt_update->execute();

        // Catat log aktivitas
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged mengubah status permintaan dengan id ($idpermintaan) menjadi Ditolak dan menghapus stok serta data masuk terkait";
        catatLog($conn, $activity, $iduser_logged);

        header('Location: permintaan.php');
        exit();
    } else {
        echo "Status permintaan tidak valid";
    }
} else {
    echo "Permintaan tidak valid";
}
