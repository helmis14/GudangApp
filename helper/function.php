<?php
session_start();
require_once 'connection.php';
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function convertToBase64($file)
{
    if (is_string($file)) {
        $base64 = base64_encode(file_get_contents($file));
        return $base64;
    } else {
        return false;
    }
}


// Menambah barang baru stock
if (isset($_POST['addnewbarang'])) {
    $namabarang = $_POST['namabarang'];
    $kategori = $_POST['kategori'];
    $unit = $_POST['unit'];
    $stock = $_POST['stock'];
    $lok = $_POST['lokasi'];

    $stmt = $conn->prepare("INSERT INTO stock (namabarang, kategori, unit, stock, lokasi) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $namabarang, $kategori, $unit, $stock, $lok);
    $addtotable = $stmt->execute();
    
   
    if ($addtotable) {
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged menambah data barang stok: $namabarang ($stock $unit) lokasi $lok";
        catatLog($conn, $activity, $iduser_logged);
        header('location:../../view/stock/stock.php');
    } else {
        echo 'Gagal';
        header('location:../../view/stock/stock.php');
    }
}


// Update info barang stock
if (isset($_POST['updatebarang'])) {
    $idb = $_POST['idb'];
    $namabarang = $_POST['namabarang'];
    $kategori = $_POST['kategori'];
    $unit = $_POST['unit'];
    $lok = $_POST['lokasi'];

    $stmt = $conn->prepare("UPDATE stock SET namabarang=?, kategori=?, unit=?, lokasi=? WHERE idbarang=?");
    $stmt->bind_param("ssssi", $namabarang, $kategori, $unit, $lok, $idb);
    $update = $stmt->execute();
    
    if ($update) {
        $query_nama_barang = mysqli_query($conn, "SELECT namabarang FROM stock WHERE idbarang='$idb'");
        $data_nama_barang = mysqli_fetch_assoc($query_nama_barang);
        $nama_barang = $data_nama_barang['namabarang'];

        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged melakukan pembaruan informasi stock barang: $nama_barang (ID: $idb) menjadi $namabarang, $unit, $lok";
        catatLog($conn, $activity, $iduser_logged);

        header('location:../../view/stock/stock.php');
    } else {
        echo 'Gagal';
        header('location:../../view/stock/stock.php');
    }
}



// Menghapus barang dari stock
if (isset($_POST['hapusbarang'])) {
    $idb = $_POST['idb'];

    $stmt = $conn->prepare("SELECT namabarang FROM stock WHERE idbarang=?");
    $stmt->bind_param("i", $idb);
    $stmt->execute();
    $result = $stmt->get_result();
    $data_nama_barang = $result->fetch_assoc();
    $nama_barang = $data_nama_barang['namabarang'];
    $stmt = $conn->prepare("DELETE FROM stock WHERE idbarang=?");

    $stmt->bind_param("i", $idb);
    $hapus = $stmt->execute();
    
    if ($hapus) {
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged menghapus barang dari stok: $nama_barang (ID: $idb)";
        catatLog($conn, $activity, $iduser_logged);

        header('location:../../view/stock/stock.php');
    } else {
        echo 'Gagal';
        header('location:../../view/stock/stock.php');
    }
}




// Menambah barang masuk
if (isset($_POST['barangmasuk'])) {
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];
    $keterangan = $_POST['keterangan'];
    $distributor = $_POST['distributor'];
    $deskripsi = $_POST['deskripsi'];
    $status = $_POST['status'];

    $bukti_masuk_base64 = $_FILES['bukti_masuk_base64'];
    $tmp_name = $bukti_masuk_base64['tmp_name'];

    if (!empty($tmp_name)) {
        $bukti_masuk_base64 = convertToBase64($tmp_name);

        $cekstocksekarang = mysqli_query($conn, "SELECT * FROM stock WHERE idbarang='$barangnya'");
        $ambildatanya = mysqli_fetch_array($cekstocksekarang);

        $stocksekarang = $ambildatanya['stock'];
        $tambahkanstocksekarangdenganquantity = $stocksekarang + $qty;

        $addtomasuk = mysqli_query($conn, "INSERT INTO masuk (idbarang, penerima, qty, keterangan, deskripsi, distributor, status, bukti_masuk_base64) VALUES ('$barangnya','$penerima','$qty', '$keterangan', '$deskripsi', '$distributor', '$status', '$bukti_masuk_base64')");
        $updatestockmasuk = mysqli_query($conn, "UPDATE stock SET stock='$tambahkanstocksekarangdenganquantity' WHERE idbarang='$barangnya'");
        if ($addtomasuk && $updatestockmasuk) {
            $query_nama_barang = mysqli_query($conn, "SELECT namabarang FROM stock WHERE idbarang='$barangnya'");
            $data_nama_barang = mysqli_fetch_assoc($query_nama_barang);
            $nama_barang = $data_nama_barang['namabarang'];

            $iduser_logged = $_SESSION['iduser'];
            $email_logged = $_SESSION['email'];
            $activity = "$email_logged melakukan tambah data barang masuk: $nama_barang ($qty) penerima $penerima distributor $distributor dengan keterangan: $keterangan";
            catatLog($conn, $activity, $iduser_logged);
            header('location:barang_masuk.php');
        } else {
            echo 'Gagal';
            header('location:barang_masuk.php');
        }
    } else {
        echo 'Gambar Tidak ada';
        exit;
    }
}


// Mengubah data barang masuk
if (isset($_POST['updatebarangmasuk'])) {
    $idb = $_POST['idb'];
    $idm = $_POST['idm'];
    $deskripsi = $_POST['keterangan'];
    $qty = $_POST['qty'];
    $penerima = $_POST['penerima'];
    $distributor = $_POST['distributor'];
    $keterangan = $_POST['keterangan'];

    $query_nama_barang = mysqli_query($conn, "SELECT namabarang FROM stock WHERE idbarang='$idb'");
    $data_nama_barang = mysqli_fetch_assoc($query_nama_barang);
    $nama_barang = $data_nama_barang['namabarang'];

    if (isset($_FILES['update_bukti_masuk']) && $_FILES['update_bukti_masuk']['error'] == 0) {
        $tmp_path = $_FILES['update_bukti_masuk']['tmp_name'];
        $update_bukti_masuk_base64 = convertToBase64($tmp_path);

        $queryUpdateGambarMasuk = "UPDATE masuk SET bukti_masuk_base64 = ? WHERE idmasuk = ?";
        $stmtUpdateGambarMasuk = mysqli_prepare($conn, $queryUpdateGambarMasuk);
        mysqli_stmt_bind_param($stmtUpdateGambarMasuk, "si", $update_bukti_masuk_base64, $idm);

        if (mysqli_stmt_execute($stmtUpdateGambarMasuk)) {
            mysqli_stmt_close($stmtUpdateGambarMasuk);
        } else {
            echo 'Error updating image: ' . mysqli_error($conn);
            exit;
        }
    }

    $lihatstock = mysqli_query($conn, "SELECT * FROM stock WHERE idbarang='$idb'");
    $stocknya = mysqli_fetch_array($lihatstock);
    $stocksekarang = $stocknya['stock'];

    $qtysekarang = mysqli_query($conn, "SELECT * FROM masuk WHERE idmasuk='$idm'");
    $qtynya = mysqli_fetch_array($qtysekarang);
    $qtysekarang = $qtynya['qty'];

    if ($qty > $qtysekarang) {
        $selisih = $qty - $qtysekarang;
        $kurangin = $stocksekarang + $selisih;
    } else {
        $selisih = $qtysekarang - $qty;
        $kurangin = $stocksekarang - $selisih;
    }

    $kurangistocknya = mysqli_query($conn, "UPDATE stock SET stock='$kurangin' WHERE idbarang='$idb'");
    $updatenya = mysqli_query($conn, "UPDATE masuk SET qty='$qty', keterangan='$deskripsi', penerima='$penerima', distributor='$distributor' WHERE idmasuk='$idm'");

    if ($kurangistocknya && $updatenya) {
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged mengubah data barang masuk: $nama_barang (ID: $idm) menjadi $qty kepada $penerima dengan keterangan: $keterangan";
        catatLog($conn, $activity, $iduser_logged);

        header('location:barang_masuk.php');
    } else {
        echo 'Gagal';
        header('location:barang_masuk.php');
    }
}



// Menghapus data barang masuk
if (isset($_POST['hapusbarangmasuk'])) {
    $idb = $_POST['idb'];
    $qty = $_POST['qty'];
    $idm = $_POST['idm'];

    // Ambil informasi barang yang akan dihapus
    $query_nama_barang_masuk = mysqli_query($conn, "SELECT penerima, distributor, keterangan FROM masuk WHERE idmasuk='$idm'");
    $query_nama_barang = mysqli_query($conn, "SELECT namabarang FROM stock WHERE idbarang='$idb'");
    $data_nama_barang = mysqli_fetch_assoc($query_nama_barang);
    $data_nama_barang_masuk = mysqli_fetch_assoc($query_nama_barang_masuk);

    $nama_barang = $data_nama_barang['namabarang'];
    $penerima = $data_nama_barang_masuk['penerima'];
    $distributor = $data_nama_barang_masuk['distributor'];
    $keterangan = $data_nama_barang_masuk['keterangan'];

    // Lakukan pengurangan stok
    $ambildatastock = mysqli_query($conn, "SELECT * FROM stock WHERE idbarang='$idb'");
    $data = mysqli_fetch_array($ambildatastock);
    $stok = $data['stock'];
    $selisih = $stok - $qty;

    $update = mysqli_query($conn, "UPDATE stock SET stock='$selisih' WHERE idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "DELETE FROM masuk WHERE idmasuk='$idm'");

    if ($update && $hapusdata) {
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged menghapus data barang masuk: $nama_barang ($qty) penerima $penerima distributor $distributor dengan keterangan: $keterangan";
        catatLog($conn, $activity, $iduser_logged);
        header('location:barang_masuk.php');
    } else {
        header('location:barang_masuk.php');
    }
}

// Menambah barang keluar
if (isset($_POST['addbarangkeluar'])) {
    $gambar_base64 = $_FILES['gambar_base64']['tmp_name'];
    if (!empty($gambar_base64)) {
        $gambar_base64 = convertToBase64($gambar_base64);
        $addPermintaan = mysqli_query($conn, "INSERT INTO permintaan_keluar (tanggal, gambar_base64) VALUES (NOW(), '$gambar_base64')");

        if ($addPermintaan) {
            $idPermintaan = mysqli_insert_id($conn);


            $barangnya = $_POST['barangnya'];
            $penerima = $_POST['penerima'];
            $qty = $_POST['qty'];
            $keterangan = $_POST['keterangan'];

            // Pengecekan stok barang sebelum proses pengiriman barang keluar
            $stok_sufficient = true;
            for ($i = 0; $i < count($barangnya); $i++) {
                $currentNamabarang = mysqli_real_escape_string($conn, $barangnya[$i]);
                $currentQty = mysqli_real_escape_string($conn, $qty[$i]);

                // Query untuk mendapatkan stok barang
                $query_stock = mysqli_query($conn, "SELECT stock FROM stock WHERE idbarang='$currentNamabarang'");
                $data_stock = mysqli_fetch_assoc($query_stock);
                $stock = $data_stock['stock'];

                // Periksa apakah stok mencukupi
                if ($stock < $currentQty) {
                    $stok_sufficient = false;
                    break;
                }
            }

            // Jika stok mencukupi, lanjutkan dengan proses pengiriman barang keluar
            if ($stok_sufficient) {
                for ($i = 0; $i < count($barangnya); $i++) {
                    $currentNamabarang = mysqli_real_escape_string($conn, $barangnya[$i]);
                    $currentQty = mysqli_real_escape_string($conn, $qty[$i]);
                    $currentKet = mysqli_real_escape_string($conn, $keterangan[$i]);
                    $currentPenerima = mysqli_real_escape_string($conn, $penerima[$i]);

                    $addBarang = mysqli_query($conn, "INSERT INTO keluar (idpermintaan, idbarang, qty, keterangan, penerima) VALUES ('$idPermintaan', '$currentNamabarang','$currentQty','$currentKet', '$currentPenerima')");

                    if (!$addBarang) {
                        echo 'Gagal menambahkan barang';
                        header('location: permintaan.php');
                        exit;
                    }

                    $updateStock = mysqli_query($conn, "UPDATE stock SET stock = stock - $currentQty WHERE idbarang = '$currentNamabarang'");
                    if (!$updateStock) {
                        echo 'Gagal memperbarui stok barang';
                        header('location: permintaan.php');
                        exit;
                    }
                }

                $iduser_logged = $_SESSION['iduser'];
                $email_logged = $_SESSION['email'];
                $activity = "$email_logged melakukan pengiriman barang keluar dengan ID permintaan: $idPermintaan";
                catatLog($conn, $activity, $iduser_logged);

                header('location:barang_keluar.php');
            } else {
                echo "<script>alert('Stok barang tidak mencukupi untuk melakukan pengiriman barang keluar.'); window.location.href = 'barang_keluar.php';</script>";
                exit;
            }
        } else {
            echo 'Gagal menambahkan permintaan';
            exit;
        }
    } else {
        echo 'Berkas gambar tidak diunggah.';
        exit;
    }
}


//ubah keluar baru
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'delete_barang') {
            if (isset($_POST['idkeluar'])) {
                $idkeluar = $_POST['idkeluar'];

                if (is_array($idkeluar)) {
                    foreach ($idkeluar as $keluar) {
                        if (delete_barang_keluar($keluar)) {
                            echo 'success';
                        } else {
                            echo 'error';
                        }
                    }
                } else {
                    if (delete_barang_keluar($idkeluar)) {
                        echo 'success';
                    } else {
                        echo 'error';
                    }
                }
            } else {
                echo 'error';
            }
        } elseif ($action === 'update_barang') {
            if (isset($_POST['idkeluar'])) {
                $idkeluar = $_POST['idkeluar'];
                $idbarang = $_POST['$idbarang'];
                $penerima = $_POST['penerima'];
                $qty = $_POST['qty'];
                $keterangan = $_POST['keterangan'];

                if (update_barang_keluar($idkeluar, $idbarang, $penerima, $qty, $keterangan)) {
                    echo 'success';
                } else {
                    echo 'error';
                }
            } else {
                echo 'error';
            }
        }
    }
}



// Fungsi penghapusan barang keluar edit
function delete_barang_keluar($idkeluar)
{
    global $conn;

    $query_select = "SELECT idbarang, qty FROM keluar WHERE idkeluar = $idkeluar";
    $result_select = mysqli_query($conn, $query_select);
    $row = mysqli_fetch_assoc($result_select);
    $idbarang = $row['idbarang'];
    $qty = $row['qty'];

    $query_delete = "DELETE FROM keluar WHERE idkeluar = $idkeluar";

    if (mysqli_query($conn, $query_delete)) {
        $query_update = "UPDATE stock SET stock = stock + $qty WHERE idbarang = $idbarang";
        if (mysqli_query($conn, $query_update)) {
            $iduser_logged = $_SESSION['iduser'];
            $email_logged = $_SESSION['email'];
            $activity = "$email_logged menghapus data barang permintaan dengan idkeluar ($idkeluar) dan mengembalikan stok barang ke tabel barang";
            catatLog($conn, $activity, $iduser_logged);
            return true;
        } else {
            echo '<script>console.log("Gagal mengupdate stok barang: ' . mysqli_error($conn) . '");</script>';
            return false;
        }
    } else {
        echo '<script>console.log("Gagal menghapus barang: ' . mysqli_error($conn) . '");</script>';
        return false;
    }
}

// Fungsi pembaruan barang keluar edit
function update_barang_keluar($idkeluar, $idbarang, $penerima, $qty, $keterangan)
{
    global $conn;

    mysqli_begin_transaction($conn);

    $query_select_old_qty = "SELECT qty FROM keluar WHERE idkeluar = ?";
    $stmt_select_old_qty = mysqli_prepare($conn, $query_select_old_qty);
    mysqli_stmt_bind_param($stmt_select_old_qty, "i", $idkeluar);
    mysqli_stmt_execute($stmt_select_old_qty);
    mysqli_stmt_bind_result($stmt_select_old_qty, $old_qty);
    mysqli_stmt_fetch($stmt_select_old_qty);
    mysqli_stmt_close($stmt_select_old_qty);

    $qty_difference = $qty - $old_qty;

    $query_update_keluar = "UPDATE keluar SET penerima = ?, qty = ?, keterangan = ? WHERE idkeluar = ?";
    $stmt_update_keluar = mysqli_prepare($conn, $query_update_keluar);
    mysqli_stmt_bind_param($stmt_update_keluar, "sisi", $penerima, $qty, $keterangan, $idkeluar);
    $update_keluar_success = mysqli_stmt_execute($stmt_update_keluar);

    // Update stok berdasarkan perbedaan jumlah
    $query_update_stok = "UPDATE stock SET stock = stock - ? WHERE idbarang = ?";
    if ($qty_difference > 0) {
        $query_update_stok = "UPDATE stock SET stock = stock - ? WHERE idbarang = ?";
    } elseif ($qty_difference < 0) {
        $query_update_stok = "UPDATE stock SET stock = stock + ? WHERE idbarang = ?";
    }
    $stmt_update_stok = mysqli_prepare($conn, $query_update_stok);
    mysqli_stmt_bind_param($stmt_update_stok, "ii", abs($qty_difference), $idbarang);
    $update_stok_success = mysqli_stmt_execute($stmt_update_stok);

    if ($update_keluar_success && $update_stok_success) {
        mysqli_commit($conn);
        mysqli_stmt_close($stmt_update_keluar);
        mysqli_stmt_close($stmt_update_stok);
        return true;
    } else {
        mysqli_rollback($conn);
        echo '<script>console.log("Gagal memperbarui barang: ' . mysqli_error($conn) . '");</script>';
        mysqli_stmt_close($stmt_update_keluar);
        mysqli_stmt_close($stmt_update_stok);
        return false;
    }
}



//ubah barang keluar
if (isset($_POST['updatebarangkeluar'])) {
    // Proses update barang keluar
    $idpermintaan = $_POST['id'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];
    $ket = $_POST['ket'];
    $idbarang = $_POST['idbarang'];
    $idkeluar = $_POST['idkeluar'];

    // Transaksi dimulai
    mysqli_begin_transaction($conn);

    if (isset($idkeluar) && is_array($idkeluar)) {
        // Loop melalui setiap barang keluar yang diupdate
        for ($i = 0; $i < count($idkeluar); $i++) {
            $id_keluar = $idkeluar[$i];
            $id_barang = $idbarang[$i];
            $penerima_barang = $penerima[$i];
            $qty_barang = $qty[$i];
            $keterangan_barang = $ket[$i];

            // Panggil fungsi untuk memperbarui barang keluar
            if (!update_barang_keluar($id_keluar, $id_barang, $penerima_barang, $qty_barang, $keterangan_barang)) {
                echo 'Error updating barang_keluar';
                mysqli_rollback($conn);
                exit;
            }
        }
    } else {
        echo "ID barang tidak valid.";
    }

    // Update gambar jika ada
    if (isset($_FILES['gambar_base64']) && $_FILES['gambar_base64']['error'] == 0) {
        $tmp_path = $_FILES['gambar_base64']['tmp_name'];
        $update_permintaan_base64 = convertToBase64($tmp_path);

        $queryUpdatePermintaan = "UPDATE permintaan_keluar SET gambar_base64 = ? WHERE idpermintaan = ?";
        $stmtUpdatePermintaan = mysqli_prepare($conn, $queryUpdatePermintaan);
        mysqli_stmt_bind_param($stmtUpdatePermintaan, "si", $update_permintaan_base64, $idpermintaan);

        if (!mysqli_stmt_execute($stmtUpdatePermintaan)) {
            echo 'Error updating image: ' . mysqli_error($conn);
            mysqli_rollback($conn);
            exit;
        }

        mysqli_stmt_close($stmtUpdatePermintaan);
    }

    // Commit transaksi
    mysqli_commit($conn);

    // Catat log aktivitas
    $iduser_logged = $_SESSION['iduser'];
    $email_logged = $_SESSION['email'];
    $activity = "$email_logged mengubah bukti data permintaan dengan idpermintaan ($idpermintaan)";
    catatLog($conn, $activity, $iduser_logged);

    // Redirect ke halaman barang keluar
    header('location: barang_keluar.php');
    exit;
}



//tambah barang baru keluar
function tambahBarangBaruKeluar($idpermintaan, $idbarang, $penerima, $qty, $keterangan)
{
    global $conn;

    $sql = "INSERT INTO keluar (idpermintaan, idbarang, penerima, qty, keterangan) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error saat membuat prepared statement: " . $conn->error);
    }

    $stmt->bind_param("iisis", $idpermintaan, $idbarang, $penerima, $qty, $keterangan);

    if ($stmt->execute() === true) {
        echo "Barang baru berhasil ditambahkan!";
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged menambah data barang permintaan dengan idpermintaan ($idpermintaan) dan idbarang ($idbarang)";
        catatLog($conn, $activity, $iduser_logged);

        $updateStock = mysqli_query($conn, "UPDATE stock SET stock = stock - $qty WHERE idbarang = '$idbarang'");
        if (!$updateStock) {
            echo 'Gagal memperbarui stok barang';
            header('location: permintaan.php');
            exit;
        }
    } else {
        echo "Gagal menambahkan barang baru: " . $stmt->error;
    }

    $stmt->close();
}



//hapus barang keluar
if (isset($_POST['hapusbarangkeluar']) && isset($_POST['idpermintaan'])) {
    $idpermintaan = $_POST['idpermintaan'];

    mysqli_begin_transaction($conn);
    $query_barang = "SELECT idbarang, qty FROM keluar WHERE idpermintaan='$idpermintaan'";
    $result_barang = mysqli_query($conn, $query_barang);

    if ($result_barang) {
        while ($row_barang = mysqli_fetch_assoc($result_barang)) {
            $idbarang = $row_barang['idbarang'];
            $qty = $row_barang['qty'];

            $updateStock = mysqli_query($conn, "UPDATE stock SET stock = stock + $qty WHERE idbarang = '$idbarang'");
            if (!$updateStock) {
                mysqli_rollback($conn);
                echo 'Gagal memperbarui stok barang';
                exit;
            }
        }

        $hapus_barang = mysqli_query($conn, "DELETE FROM keluar WHERE idpermintaan='$idpermintaan'");
        if (!$hapus_barang) {
            mysqli_rollback($conn);
            echo 'Gagal menghapus barang keluar';
            exit;
        }
    } else {
        mysqli_rollback($conn);
        echo 'Gagal mengambil data barang keluar';
        exit;
    }

    $hapus_permintaan = mysqli_query($conn, "DELETE FROM permintaan_keluar WHERE idpermintaan='$idpermintaan'");
    if (!$hapus_permintaan) {
        mysqli_rollback($conn);
        echo 'Gagal menghapus permintaan';
        exit;
    }

    mysqli_commit($conn);

    $iduser_logged = $_SESSION['iduser'];
    $email_logged = $_SESSION['email'];
    $activity = "$email_logged menghapus permintaan dengan idpermintaan ($idpermintaan)";
    catatLog($conn, $activity, $iduser_logged);
    header('location:barang_keluar.php');
    exit;
}



// Menambah admin baru
if (isset($_POST['addnewadmin'])) {
    $em = $_POST['email'];
    $iduser = $_POST['iduser'];
    $pass = $_POST['password'];
    $role = $_POST['role'];

    $addtotable = mysqli_query($conn, "INSERT INTO login (email, iduser, password, role) VALUES ('$em','$iduser','$pass','$role')");
    if ($addtotable) {
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged menambah admin baru $em";
        catatLog($conn, $activity, $iduser_logged);
        header('location:admin.php');
    } else {
        echo 'Gagal';
        header('location:admin.php');
    }
}




// Update perubahan user
if (isset($_POST['updateadmin'])) {
    $iduser = $_POST['iduser'];
    $em = $_POST['email'];
    $pass = $_POST['password'];
    $role = $_POST['role'];

    // Ambil data admin sebelum update
    $query_before_update = mysqli_query($conn, "SELECT email FROM login WHERE iduser = '$iduser'");
    $data_before_update = mysqli_fetch_assoc($query_before_update);
    $email_before_update = $data_before_update['email'];

    // Lakukan pembaruan pada admin
    $update = mysqli_query($conn, "UPDATE login SET email='$em', password='$pass', role='$role' WHERE iduser ='$iduser'");
    if ($update) {
        // Ambil data admin setelah update
        $query_after_update = mysqli_query($conn, "SELECT email FROM login WHERE iduser = '$iduser'");
        $data_after_update = mysqli_fetch_assoc($query_after_update);
        $email_after_update = $data_after_update['email'];

        // Catat log dengan menyertakan informasi sebelum dan sesudah update
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged mengubah admin sebelumnya ($email_before_update) menjadi $em";
        catatLog($conn, $activity, $iduser_logged);
        header('location:admin.php');
    } else {
        echo 'Gagal';
        header('location:admin.php');
    }
}


// Menghapus admin dari kelola admin
if (isset($_POST['hapusadmin'])) {
    $iduser = $_POST['iduser'];
    $em = $_POST['email'];
    $hapus = mysqli_query($conn, "DELETE FROM login WHERE iduser='$iduser'");

    if ($hapus) {

        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged menghapus admin: $em";
        catatLog($conn, $activity, $iduser_logged);

        header('location:admin.php');
    } else {

        echo 'Gagal menghapus admin';
        header('location:admin.php');
    }
}




// tambah permintaan
if (isset($_POST['addnewpermintaan'])) {
    // Proses permintaan
    $buktiBase64 = $_FILES['bukti_base64']['tmp_name'];
    if (!empty($buktiBase64)) {
        $buktiBase64 = convertToBase64($buktiBase64);

        $addPermintaan = mysqli_query($conn, "INSERT INTO permintaan (tanggal, status, bukti_base64) VALUES (NOW(), '0', '$buktiBase64')");

        if ($addPermintaan) {
            $idPermintaan = mysqli_insert_id($conn); // Mendapatkan ID permintaan baru

            // Ambil data dari formulir barang
            $namabarang = $_POST['namabarang'];
            $unit = $_POST['unit'];
            $qtypermintaan = $_POST['qtypermintaan'];
            $keterangan = $_POST['keterangan'];
            $status = $_POST['status'];

            // Proses untuk setiap barang
            for ($i = 0; $i < count($namabarang); $i++) {
                $currentNamabarang = mysqli_real_escape_string($conn, $namabarang[$i]);
                $currentUnit = mysqli_real_escape_string($conn, $unit[$i]);
                $currentQty = mysqli_real_escape_string($conn, $qtypermintaan[$i]);
                $currentKet = mysqli_real_escape_string($conn, $keterangan[$i]);
                $currentStatus = mysqli_real_escape_string($conn, $status[$i]);

                // Simpan detail barang dengan ID permintaan yang baru dibuat
                $addBarang = mysqli_query($conn, "INSERT INTO barang_permintaan (idpermintaan, namabarang, unit, qtypermintaan, keterangan, status_barang) VALUES ('$idPermintaan','$currentNamabarang','$currentUnit','$currentQty','$currentKet', '$currentStatus')");

                if (!$addBarang) {
                    echo 'Gagal menambahkan barang';
                    header('location: permintaan.php');
                    exit;
                }
            }

            $mail = new PHPMailer(true);

            try {
                // Konfigurasi pengiriman email
                $mail->isSMTP();
                $mail->Host = 'mail.rohedagroup.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'infowh@plazaoleos.com';
                $mail->Password = 'wh@PO2024';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;


                $mail->setFrom('infowh@plazaoleos.com', 'Info Warehouse');
                $mail->addAddress('warehouse@plazaoleos.com', 'Warehouse');


                $mail->Subject = 'Permintaan Masuk (Gudang) - Plaza Oleos';
                $mail->Body = "Dear Pak John,\r\n"
                    . "Harap tanggapi permintaan pada tanggal " . date('d-m-y') . " dengan detail berikut:\r\n";


                $namabarang = $_POST['namabarang'];
                $unit = $_POST['unit'];
                $qtypermintaan = $_POST['qtypermintaan'];
                $keterangan = $_POST['keterangan'];
                $status = $_POST['status'];

                for ($i = 0; $i < count($namabarang); $i++) {
                    $currentNamabarang = mysqli_real_escape_string($conn, $namabarang[$i]);
                    $currentUnit = mysqli_real_escape_string($conn, $unit[$i]);
                    $currentQty = mysqli_real_escape_string($conn, $qtypermintaan[$i]);
                    $currentKet = mysqli_real_escape_string($conn, $keterangan[$i]);
                    $currentStatus = mysqli_real_escape_string($conn, $status[$i]);

                    $mail->Body .= "\r\n"
                        . "Nama barang : $currentNamabarang\r\n"
                        . "Unit : $currentUnit\r\n"
                        . "Quantity: $currentQty\r\n"
                        . "Keterangan: $currentKet"
                        . "\r\n";
                }

                $mail->Body .= "\r\n"
                    . "Terimakasih\r\n"
                    . "Best Regards,\r\n"
                    . "IT Roheda Team\r\n";


                $mail->send();

                echo 'Email berhasil terkirim';

                // Catat log
                $iduser_logged = $_SESSION['iduser'];
                $email_logged = $_SESSION['email'];
                $activity = "$email_logged melakukan tambah permintaan dengan idpermintaan ($idPermintaan)";
                catatLog($conn, $activity, $iduser_logged);

                // Redirect ke halaman permintaan
                header('location: permintaan.php');
            } catch (Exception $e) {
                echo 'Gagal mengirim email: ' . $mail->ErrorInfo;
            }
        } else {
            echo 'Gagal menambahkan permintaan';
        }
    } else {
        echo 'Berkas gambar tidak diunggah';
        exit;
    }
}


//tambah barang baru id sama di permintaan
function tambahBarangBaru($idpermintaan, $namabarang, $unit, $qtypermintaan, $keterangan)
{
    global $conn;
    $sql = "INSERT INTO barang_permintaan (idpermintaan, namabarang, unit, qtypermintaan, keterangan) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error saat membuat prepared statement: " . $conn->error);
    }
    $stmt->bind_param("issis", $idpermintaan, $namabarang, $unit, $qtypermintaan, $keterangan);

    if ($stmt->execute() === true) {

        // Mengambil ID barang berdasarkan nama barang
        $query_id_barang = mysqli_query($conn, "SELECT idbarang FROM barang_permintaan WHERE namabarang='$namabarang'");
        $data_id_barang = mysqli_fetch_assoc($query_id_barang);
        $idbarang = $data_id_barang['idbarang'];

        echo "Barang baru berhasil ditambahkan!";
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged menambah data barang permintaan dengan idpermintaan ($idpermintaan) dan idbarang ($idbarang)";
        catatLog($conn, $activity, $iduser_logged);
    } else {
        echo "Gagal menambahkan barang baru: " . $stmt->error;
    }
    $stmt->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        // Ambil tindakan yang diminta
        $action = $_POST['action'];

        // Tangani tindakan penghapusan
        if ($action === 'delete_barang') {
            if (isset($_POST['idbarang'])) {
                $idbarang = $_POST['idbarang'];
                if (delete_barang($idbarang)) {
                    echo 'success';
                } else {
                    echo 'error';
                }
            }
        }

        // Tangani tindakan pembaruan
        elseif ($action === 'update_barang') {
            if (isset($_POST['idbarang'])) {
                $idbarang = $_POST['idbarang'];
                $namabarang = $_POST['namabarang'];
                $unit = $_POST['unit'];
                $qty = $_POST['qty'];
                $keterangan = $_POST['keterangan'];
                if (update_barangpermin($idbarang, $namabarang, $unit, $qty, $keterangan)) {
                    echo 'success';
                } else {
                    echo 'error';
                }
            }
        }
    }
}

// Fungsi penghapusan barang
function delete_barang($idbarang)
{
    global $conn;
    $query = "DELETE FROM barang_permintaan WHERE idbarang = $idbarang";
    if (mysqli_query($conn, $query)) {
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged menghapus data barang permintaan dengan idbarang ($idbarang)";
        catatLog($conn, $activity, $iduser_logged);
        return true;
    } else {
        echo '<script>console.log("Gagal menghapus barang: ' . mysqli_error($conn) . '");</script>';
        return false;
    }
}

// Fungsi pembaruan barang
function update_barangpermin($idbarang, $nama_barang, $unit, $qty, $keterangan)
{
    global $conn;
    $query = "UPDATE barang_permintaan SET namabarang = '$nama_barang', unit = '$unit', qtypermintaan = '$qty', keterangan = '$keterangan' WHERE idbarang = $idbarang";
    if (mysqli_query($conn, $query)) {
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged mengubah data barang permintaan dengan idbarang ($idbarang)";
        catatLog($conn, $activity, $iduser_logged);
        catatLog($conn, $activity, $iduser_logged);

        return true;
    } else {
        echo '<script>console.log("Gagal memperbarui barang: ' . mysqli_error($conn) . '");</script>';
        return false;
    }
}


// Mengubah data permintaan
if (isset($_POST['updatepermintaan'])) {
    $idpermintaan = $_POST['id'];
    $namabarang = $_POST['namabarang'];
    $unit = $_POST['unit'];
    $qtypermintaan = $_POST['qtypermintaan'];
    $ket = $_POST['ket'];

    // Memulai transaksi
    mysqli_begin_transaction($conn);

    // Update data barang_permintaan
    for ($i = 0; $i < count($namabarang); $i++) {

        $id_barang = $idbarang[$i];
        $nama_barang = $namabarang[$i];
        $unit_barang = $unit[$i];
        $qty_barang = $qtypermintaan[$i];
        $keterangan_barang = $ket[$i];

        // Perbarui data sesuai dengan idbarang dan idpermintaan
        $queryUpdateBarangPermintaan = "UPDATE barang_permintaan SET namabarang = ?, unit = ?, qtypermintaan = ?, keterangan = ? WHERE idpermintaan = ? AND idbarang = ?";
        $stmtUpdateBarangPermintaan = mysqli_prepare($conn, $queryUpdateBarangPermintaan);
        mysqli_stmt_bind_param($stmtUpdateBarangPermintaan, "ssisii", $nama_barang, $unit_barang, $qty_barang, $keterangan_barang, $idpermintaan, $id_barang);

        if (!mysqli_stmt_execute($stmtUpdateBarangPermintaan)) {
            echo 'Error updating barang_permintaan: ' . mysqli_error($conn);
            mysqli_rollback($conn);
            exit;
        }

        mysqli_stmt_close($stmtUpdateBarangPermintaan);
    }

    if (isset($_FILES['update_permintaan']) && $_FILES['update_permintaan']['error'] == 0) {
        $tmp_path = $_FILES['update_permintaan']['tmp_name'];
        $update_permintaan_base64 = convertToBase64($tmp_path);

        $queryUpdatePermintaan = "UPDATE permintaan SET bukti_base64 = ? WHERE idpermintaan = ?";
        $stmtUpdatePermintaan = mysqli_prepare($conn, $queryUpdatePermintaan);
        mysqli_stmt_bind_param($stmtUpdatePermintaan, "si", $update_permintaan_base64, $idpermintaan);

        if (!mysqli_stmt_execute($stmtUpdatePermintaan)) {
            echo 'Error updating image: ' . mysqli_error($conn);
            mysqli_rollback($conn);
            exit;
        }

        mysqli_stmt_close($stmtUpdatePermintaan);
    }


    mysqli_commit($conn);
    $iduser_logged = $_SESSION['iduser'];
    $email_logged = $_SESSION['email'];
    $activity = "$email_logged mengubah bukti data permintaan dengan idpermintaan ($idpermintaan)";
    catatLog($conn, $activity, $iduser_logged);
    header('location: permintaan.php');
    exit;
}



// hapus permintaan barang
if (isset($_POST['hapuspermintaan']) && isset($_POST['idpermintaan'])) {
    $idpermintaan = $_POST['idpermintaan'];

    // Lakukan transaksi untuk menghapus permintaan dan barang terkait
    mysqli_begin_transaction($conn);

    $hapus_barang = mysqli_query($conn, "DELETE FROM barang_permintaan WHERE idpermintaan='$idpermintaan'");
    $hapus_permintaan = mysqli_query($conn, "DELETE FROM permintaan WHERE idpermintaan='$idpermintaan'");

    if ($hapus_barang && $hapus_permintaan) {
        // Jika query berhasil, commit transaksi
        mysqli_commit($conn);

        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged menghapus permintaan dengan idpermintaan ($idpermintaan)";
        catatLog($conn, $activity, $iduser_logged);

        // Redirect setelah berhasil menghapus
        header('location:permintaan.php');
        exit;
    } else {
        // Jika ada kesalahan, rollback transaksi
        mysqli_rollback($conn);
        echo 'Gagal menghapus permintaan: ' . mysqli_error($conn);
    }
} else {
    echo '';
}




/// EXPORT EXCEL PERMINTAAN BARANG
if (isset($_POST['export_permintaan'])) {

    $exportPermintaan = mysqli_query($conn, "select * from permintaan");
    if ($exportPermintaan) {
        header('location:excelpermintaan.php');
    } else {
        echo 'Gagal';
    }
}

/// EXPORT EXCEL STOK BARANG
if (isset($_POST['export'])) {

    $exportExcel = mysqli_query($conn, "select * from stock");
    if ($exportExcel) {
        header('location:excelstock.php');
    } else {
        echo 'Gagal';
    }
}


/// EXPORT EXCEL BARANG KELUAR 
if (isset($_POST['export_keluar'])) {

    $exportKeluar = mysqli_query($conn, "select * from keluar");
    if ($exportKeluar) {
        header('location:excel_keluar.php');
    } else {
        echo 'Gagal';
    }
}


/// EXPORT EXCEL BARANG MASUK 
if (isset($_POST['export_masuk'])) {

    $exportMasuk = mysqli_query($conn, "select * from masuk");
    if ($exportMasuk) {
        header('location:excel_masuk.php');
    } else {
        echo 'Gagal';
    }
}

/// EXPORT EXCEL LOG AKTIVITAS USER 
if (isset($_POST['exportlog'])) {

    $exportLog = mysqli_query($conn, "select * from log");
    if ($exportLog) {
        header('location:excellog.php');
    } else {
        echo 'Gagal';
    }
}


/// EXPORT EXCEL RETUR BARANG 
if (isset($_POST['export_retur'])) {

    $exportLog = mysqli_query($conn, "select * from retur");
    if ($exportLog) {
        header('location:excelretur.php');
    } else {
        echo 'Gagal';
    }
}


// MENAMBAH RETUR BARANG<?php
if (isset($_POST['addbarangretur'])) {
    $barangnya = $_POST['barangnya'];
    $permintaan = $_POST['permintaan'];
    $qty = $_POST['qtyretur'];
    $keterangan = $_POST['keterangan'];

    // Proses konversi gambar ke base64
    $gambar_base64 = $_FILES['gambar_base64'];
    $tmp_name = $gambar_base64['tmp_name'];

    // Pastikan berkas gambar sudah diunggah
    if (!empty($tmp_name)) {
        // Konversi gambar ke base64
        $gambar_base64 = convertToBase64($tmp_name);

        // Cek stok barang
        $cekstocksekarang = mysqli_query($conn, "SELECT * FROM stock WHERE idbarang='$barangnya'");
        $ambildatanya = mysqli_fetch_array($cekstocksekarang);
        $stocksekarang = $ambildatanya['stock'];

        if ($stocksekarang >= $qty) {
            // Jika stok cukup, lanjutkan dengan proses retur barang
            $tambahkanstocksekarangdenganquantity = $stocksekarang - $qty;

            // Parameterisasi query untuk keamanan
            $addtokeluar = mysqli_prepare($conn, "INSERT INTO retur (idbarang, idretur, qtyretur, gambar_base64, keterangan) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($addtokeluar, "isiss", $barangnya, $permintaan, $qty, $gambar_base64, $keterangan);
            mysqli_stmt_execute($addtokeluar);

            $updatestockmasuk = mysqli_query($conn, "UPDATE stock SET stock='$tambahkanstocksekarangdenganquantity' WHERE idbarang='$barangnya'");

            if ($addtokeluar && $updatestockmasuk) {
                // Ambil data nama barang untuk mencatat aktivitas
                $query_nama_barang = mysqli_query($conn, "SELECT namabarang FROM stock WHERE idbarang='$barangnya'");
                $data_nama_barang = mysqli_fetch_assoc($query_nama_barang);
                $nama_barang = $data_nama_barang['namabarang'];

                // Catat log dengan informasi yang diinginkan
                $iduser_logged = $_SESSION['iduser'];
                $email_logged = $_SESSION['email'];
                $activity = "$email_logged melakukan retur barang: $nama_barang ($qty) distributor $distributor dengan keterangan: $keterangan";
                catatLog($conn, $activity, $iduser_logged);
                header('location:retur.php');
            } else {
                echo 'Gagal';
                header('location:retur.php');
            }
        } else {
            echo "<script>alert('Stok barang tidak mencukupi untuk melakukan retur.'); window.location.href = 'retur.php';</script>";
            exit;
        }
    } else {
        echo 'Berkas gambar tidak diunggah.';
        exit;
    }
}





//hapus barang retur
if (isset($_POST['hapusbarangretur']) && isset($_POST['idretur'])) {
    $idretur = $_POST['idretur'];

    mysqli_begin_transaction($conn);
    $query_barang = "SELECT idbarang, qtyretur FROM retur WHERE idretur='$idretur'";
    $result_barang = mysqli_query($conn, $query_barang);

    if ($result_barang) {
        while ($row_barang = mysqli_fetch_assoc($result_barang)) {
            $idbarang = $row_barang['idbarang'];
            $qty = $row_barang['qtyretur'];

            $updateStock = mysqli_query($conn, "UPDATE stock SET stock = stock + $qty WHERE idbarang = '$idbarang'");
            if (!$updateStock) {
                mysqli_rollback($conn);
                echo 'Gagal memperbarui stok barang';
                exit;
            }
        }

        $hapus_barang = mysqli_query($conn, "DELETE FROM retur WHERE idretur='$idretur'");
        if (!$hapus_barang) {
            mysqli_rollback($conn);
            echo 'Gagal menghapus barang retur';
            exit;
        }
    } else {
        mysqli_rollback($conn);
        echo 'Gagal mengambil data barang retur';
        exit;
    }


    mysqli_commit($conn);

    $iduser_logged = $_SESSION['iduser'];
    $email_logged = $_SESSION['email'];
    $activity = "$email_logged menghapus retur dengan idretur ($idretur)";
    catatLog($conn, $activity, $iduser_logged);
    header('location:retur.php');
    exit;
}


// Fungsi pembaruan barang retur
function update_barang_retur($id_retur, $qty_retur, $keterangan_retur, $update_gambar_base64)
{
    global $conn;

    mysqli_begin_transaction($conn);

    // Ambil data retur yang akan diupdate
    $query_select_retur = "SELECT idbarang, qtyretur FROM retur WHERE idretur = ?";
    $stmt_select_retur = mysqli_prepare($conn, $query_select_retur);
    mysqli_stmt_bind_param($stmt_select_retur, "i", $id_retur);
    mysqli_stmt_execute($stmt_select_retur);
    mysqli_stmt_bind_result($stmt_select_retur, $id_barang, $old_qty);
    mysqli_stmt_fetch($stmt_select_retur);
    mysqli_stmt_close($stmt_select_retur);

    // Update data retur
    $query_update_retur = "UPDATE retur SET qtyretur = ?, keterangan = ?";
    if (!empty($update_gambar_base64)) {
        $query_update_retur .= ", gambar_base64 = ?";
    }
    $query_update_retur .= " WHERE idretur = ?";
    $stmt_update_retur = mysqli_prepare($conn, $query_update_retur);
    if (!empty($update_gambar_base64)) {
        mysqli_stmt_bind_param($stmt_update_retur, "issi", $qty_retur, $keterangan_retur, $update_gambar_base64, $id_retur);
    } else {
        mysqli_stmt_bind_param($stmt_update_retur, "isi", $qty_retur, $keterangan_retur, $id_retur);
    }
    $update_retur_success = mysqli_stmt_execute($stmt_update_retur);

    // Hitung perbedaan jumlah retur baru dan lama
    $qty_difference = $qty_retur - $old_qty;

    // Update stok
    $query_update_stok = "UPDATE stock SET stock = stock - ? WHERE idbarang = ?";
    $stmt_update_stok = mysqli_prepare($conn, $query_update_stok);
    mysqli_stmt_bind_param($stmt_update_stok, "ii", $qty_difference, $id_barang);
    $update_stok_success = mysqli_stmt_execute($stmt_update_stok);

    if ($update_retur_success && $update_stok_success) {
        mysqli_commit($conn);
        mysqli_stmt_close($stmt_update_retur);
        mysqli_stmt_close($stmt_update_stok);
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $activity = "$email_logged mengubah qty dari jumlah ($old_qty) menjadi ($qty_retur) retur barang dengan idretur ($id_retur)";
        catatLog($conn, $activity, $iduser_logged);
        return true;
    } else {
        mysqli_rollback($conn);
        echo '<script>console.log("Gagal memperbarui barang retur: ' . mysqli_error($conn) . '");</script>';
        mysqli_stmt_close($stmt_update_retur);
        mysqli_stmt_close($stmt_update_stok);
        return false;
    }
}





// Proses update barang retur jika tombol submit ditekan
if (isset($_POST['updatebarangretur'])) {
    $idretur = $_POST['idretur'];
    $idb = $_POST['idbarang'];
    $qtyretur = $_POST['qtyretur'];
    $keterangan = $_POST['keterangan'];

    $update_gambar_base64 = "";
    if (isset($_FILES['update_gambar']) && $_FILES['update_gambar']['error'] == 0) {
        $tmp_path = $_FILES['update_gambar']['tmp_name'];
        $update_gambar_base64 = convertToBase64($tmp_path);
    }

    if (update_barang_retur($idretur, $qtyretur, $keterangan, $update_gambar_base64)) {
        header('location: retur.php');
        exit;
    } else {
        echo "Gagal memperbarui barang retur.";
    }
}






// Fungsi untuk mencatat log
function catatLog($conn, $activity, $iduser)
{
    $activity = mysqli_real_escape_string($conn, $activity);
    $query = "INSERT INTO log (activity, iduser) VALUES ('$activity', '$iduser')";
    $result = mysqli_query($conn, $query);
    if ($result) {
        return true;
    } else {
        return false;
    }
}
