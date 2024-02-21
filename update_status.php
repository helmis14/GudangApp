<?php
require 'function.php';
require 'cek.php';

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

            // Loop untuk memasukkan data barang ke dalam tabel barang_masuk
            while ($row_barang = $result_barang->fetch_assoc()) {
                $idbarang = $row_barang['idbarang'];
                $namabarang = $row_barang['namabarang'];
                $qty = $row_barang['qtypermintaan'];
                $keterangan = "Barang diterima dari permintaan $idpermintaan";
                $tanggal = date('Y-m-d');
                $penerima = $_SESSION['email']; // Sesuaikan dengan pengguna yang menerima barang

                // Masukkan data barang ke dalam tabel barang_masuk
                $query_insert = "INSERT INTO masuk (idbarang, tanggal, keterangan, qty, penerima) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($query_insert);
                $stmt_insert->bind_param("issss", $idbarang, $tanggal, $keterangan, $qty, $penerima);
                $stmt_insert->execute();
            }

            // Catat log aktivitas
            $iduser_logged = $_SESSION['iduser'];
            $email_logged = $_SESSION['email'];
            $activity = "$email_logged mengubah status permintaan dengan id ($idpermintaan) menjadi Diterima dan memasukkan barang ke dalam tabel barang_masuk";
            catatLog($conn, $activity, $iduser_logged);

            header('Location: permintaan.php');
            exit();
        } else {
            echo "Gagal mengupdate status permintaan: " . $stmt->error;
        }
    } elseif ($status == 2) { // Jika status permintaan adalah 'Ditolak'
        $query_delete = "DELETE FROM masuk WHERE tanggal = (SELECT tanggal FROM permintaan WHERE idpermintaan = ?)";
        $stmt_delete = $conn->prepare($query_delete);
        $stmt_delete->bind_param("i", $idpermintaan);
        $stmt_delete->execute();

        // Update status permintaan menjadi 'Ditolak'
        $query_update = "UPDATE permintaan SET status = ? WHERE idpermintaan = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("ii", $status, $idpermintaan);
        $stmt_update->execute();

        // Catat log aktivitas
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged mengubah status permintaan dengan id ($idpermintaan) menjadi Ditolak dan menghapus data barang dari tabel barang_masuk";
        catatLog($conn, $activity, $iduser_logged);

        header('Location: permintaan.php');
        exit();
    } else {
        echo "Status permintaan tidak valid";
    }
} else {
    echo "Permintaan tidak valid";
}
