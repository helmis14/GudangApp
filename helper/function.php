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





if (isset($_POST['hapusbarang'])) {
    $idb = $_POST['idb'];

    // Mengambil nama barang dari database
    $stmt = $conn->prepare("SELECT namabarang FROM stock WHERE idbarang = ?");
    if ($stmt) {
        $stmt->bind_param("i", $idb);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $data_nama_barang = $result->fetch_assoc()) {
            $nama_barang = $data_nama_barang['namabarang'];

            // Menghapus barang dari stock
            $stmt = $conn->prepare("DELETE FROM stock WHERE idbarang = ?");
            if ($stmt) {
                $stmt->bind_param("i", $idb);
                $hapus = $stmt->execute();

                if ($hapus) {
                    $iduser_logged = $_SESSION['iduser'];
                    $email_logged = $_SESSION['email'];
                    $activity = "$email_logged menghapus barang dari stok: $nama_barang (ID: $idb)";
                    catatLog($conn, $activity, $iduser_logged);

                    header('Location: ../../view/stock/stock.php');
                    exit;
                } else {
                    echo "Gagal menghapus barang: " . $stmt->error;
                }
            } else {
                echo "Gagal menyiapkan pernyataan penghapusan barang: " . $conn->error;
            }
        } else {
            echo "Barang tidak ditemukan: " . $stmt->error;
        }
    } else {
        echo "Gagal menyiapkan pernyataan pengambilan barang: " . $conn->error;
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
    $status = $_POST['status'];

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

    if ($status == 1) { // Hanya update stok jika statusnya diterima
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
    }

    $updatenya = mysqli_query($conn, "UPDATE masuk SET qty='$qty', keterangan='$deskripsi', penerima='$penerima', distributor='$distributor', status='$status' WHERE idmasuk='$idm'");

    if ($updatenya) {
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



// Menambah barang keluar baru
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

            // Proses pengiriman barang keluar
            $log_details = [];
            for ($i = 0; $i < count($barangnya); $i++) {
                $currentIdbarang = mysqli_real_escape_string($conn, $barangnya[$i]);
                $currentQty = mysqli_real_escape_string($conn, $qty[$i]);
                $currentKet = mysqli_real_escape_string($conn, $keterangan[$i]);
                $currentPenerima = mysqli_real_escape_string($conn, $penerima[$i]);

                // Ambil nama barang dan unit dari tabel stock
                $queryStock = "SELECT namabarang, unit FROM stock WHERE idbarang = '$currentIdbarang'";
                $resultStock = mysqli_query($conn, $queryStock);
                $rowStock = mysqli_fetch_assoc($resultStock);
                $currentNamabarang = $rowStock['namabarang'];
                $currentUnit = $rowStock['unit'];

                $addBarang = mysqli_query($conn, "INSERT INTO keluar (idpermintaan, idbarang, qty, keterangan, penerima) VALUES ('$idPermintaan', '$currentIdbarang','$currentQty','$currentKet', '$currentPenerima')");

                if (!$addBarang) {
                    echo 'Gagal menambahkan barang';
                    header('location: permintaan.php');
                    exit;
                }

                // Menyimpan detail untuk log
                $log_details[] = "Nama Barang: $currentNamabarang, Jumlah: $currentQty, Unit: $currentUnit, Penerima: $currentPenerima, Keterangan: $currentKet";
            }

            // Mencatat log
            $iduser_logged = $_SESSION['iduser'];
            $email_logged = $_SESSION['email'];
            $log_details_string = implode("; ", $log_details);

            $activity = "$email_logged membuat permintaan keluar dengan ID $idPermintaan Detail: $log_details_string";
            catatLog($conn, $activity, $iduser_logged);

            header('location:barang_keluar.php');
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

    // Ambil detail barang yang akan dihapus
    $query = "SELECT keluar.*, stock.namabarang, stock.unit FROM keluar INNER JOIN stock ON keluar.idbarang = stock.idbarang WHERE keluar.idkeluar = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $idkeluar);
    $stmt->execute();
    $result = $stmt->get_result();
    $barang = $result->fetch_assoc();

    // Hapus data dari tabel keluar
    $sql = "DELETE FROM keluar WHERE idkeluar = ?";
    $stmtDelete = $conn->prepare($sql);

    if ($stmtDelete === false) {
        die("Error saat membuat prepared statement: " . $conn->error);
    }

    $stmtDelete->bind_param("i", $idkeluar);

    if ($stmtDelete->execute() === true) {
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $log_details_string = "Nama Barang: " . $barang['namabarang'] . ", Jumlah: " . $barang['qty'] . ", Unit: " . $barang['unit'] . ", Penerima: " . $barang['penerima'] . ", Keterangan: " . $barang['keterangan'];
        $activity = "$email_logged menghapus barang di permintaan dengan ID keluar $idkeluar  Detail: $log_details_string";
        catatLog($conn, $activity, $iduser_logged);

        return true;
    } else {
        return false;
    }

    $stmtDelete->close();
}

// Fungsi pembaruan barang keluar edit baru
function update_barang_keluar($idkeluar, $idbarang, $penerima, $qty, $keterangan)
{
    global $conn;

    // Ambil data sebelum diubah untuk log
    $queryOld = "SELECT keluar.*, stock.namabarang, stock.unit 
                 FROM keluar 
                 INNER JOIN stock ON keluar.idbarang = stock.idbarang 
                 WHERE keluar.idkeluar = ?";
    $stmtOld = $conn->prepare($queryOld);
    $stmtOld->bind_param("i", $idkeluar);
    $stmtOld->execute();
    $resultOld = $stmtOld->get_result();
    $oldData = $resultOld->fetch_assoc();

    $oldNamabarang = $oldData['namabarang'];
    $oldUnit = $oldData['unit'];
    $oldQty = $oldData['qty'];
    $oldPenerima = $oldData['penerima'];
    $oldKeterangan = $oldData['keterangan'];

    // Update data barang keluar
    $sql = "UPDATE keluar SET idbarang = ?, penerima = ?, qty = ?, keterangan = ? WHERE idkeluar = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error saat membuat prepared statement: " . $conn->error);
    }

    $stmt->bind_param("isisi", $idbarang, $penerima, $qty, $keterangan, $idkeluar);

    if ($stmt->execute() === true) {
        $queryStock = "SELECT namabarang, unit FROM stock WHERE idbarang = ?";
        $stmtStock = $conn->prepare($queryStock);
        $stmtStock->bind_param("i", $idbarang);
        $stmtStock->execute();
        $resultStock = $stmtStock->get_result();
        $rowStock = $resultStock->fetch_assoc();
        $newNamabarang = $rowStock['namabarang'];
        $newUnit = $rowStock['unit'];

        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $log_details_before = "Sebelum Nama $oldNamabarang, Jumlah $oldQty, Unit $oldUnit, Penerima $oldPenerima, Keterangan $oldKeterangan";
        $log_details_after = "Sesudah Nama $newNamabarang, Jumlah $qty, Unit $oldUnit, Penerima $penerima, Keterangan $keterangan";
        $activity = "$email_logged mengubah barang keluar ID $idkeluar. $log_details_before; $log_details_after";
        catatLog($conn, $activity, $iduser_logged);

        return true;
    } else {
        return false;
    }

    $stmt->close();
}






//ubah barang keluar baru
//ubah barang keluar baru
if (isset($_POST['updatebarangkeluar'])) {
    $idpermintaan = $_POST['id'];
    $idbarang = $_POST['idbarang'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];
    $ket = $_POST['ket'];
    $idkeluar = $_POST['idkeluar'];

    // Mulai transaksi
    mysqli_begin_transaction($conn);

    if (isset($idkeluar) && is_array($idkeluar)) {
        for ($i = 0; $i < count($idkeluar); $i++) {
            $id_keluar = $idkeluar[$i];
            $id_barang = $idbarang[$i];
            $penerima_barang = $penerima[$i];
            $qty_barang = $qty[$i];
            $keterangan_barang = $ket[$i];

            if (!update_barang_keluar($id_keluar, $id_barang, $penerima_barang, $qty_barang, $keterangan_barang)) {
                echo 'Error updating barang_keluar';
                mysqli_rollback($conn);
                exit;
            }
        }
    } else {
        echo "ID barang tidak valid.";
    }

    // Update gambar pertama jika ada
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

    // Update gambar kedua jika ada
    if (isset($_FILES['bukti_wo']) && $_FILES['bukti_wo']['error'] == 0) {
        $tmp_path = $_FILES['bukti_wo']['tmp_name'];
        $update_bukti_wo = convertToBase64($tmp_path);

        $queryUpdateBuktiWO = "UPDATE permintaan_keluar SET bukti_wo = ? WHERE idpermintaan = ?";
        $stmtUpdateBuktiWO = mysqli_prepare($conn, $queryUpdateBuktiWO);
        mysqli_stmt_bind_param($stmtUpdateBuktiWO, "si", $update_bukti_wo, $idpermintaan);

        if (!mysqli_stmt_execute($stmtUpdateBuktiWO)) {
            echo 'Error updating image: ' . mysqli_error($conn);
            mysqli_rollback($conn);
            exit;
        }

        mysqli_stmt_close($stmtUpdateBuktiWO);
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

    $log_details = [];

    $sql = "INSERT INTO keluar (idpermintaan, idbarang, penerima, qty, keterangan) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error saat membuat prepared statement: " . $conn->error);
    }

    $stmt->bind_param("iisis", $idpermintaan, $idbarang, $penerima, $qty, $keterangan);

    if ($stmt->execute() === true) {
        $queryStock = "SELECT namabarang, unit FROM stock WHERE idbarang = ?";
        $stmtStock = $conn->prepare($queryStock);
        $stmtStock->bind_param("i", $idbarang);
        $stmtStock->execute();
        $resultStock = $stmtStock->get_result();
        $rowStock = $resultStock->fetch_assoc();
        $currentNamabarang = $rowStock['namabarang'];
        $currentUnit = $rowStock['unit'];

        $log_details[] = "Nama Barang: $currentNamabarang, Jumlah: $qty, Unit: $currentUnit, Penerima: $penerima, Keterangan: $keterangan";

        // Mencatat log
        $iduser_logged = $_SESSION['iduser'];
        $email_logged = $_SESSION['email'];
        $log_details_string = implode("; ", $log_details);
        $activity = "$email_logged menambah data barang permintaan keluar dengan ID $idpermintaan  Detail: $log_details_string";
        catatLog($conn, $activity, $iduser_logged);

        echo "Barang baru berhasil ditambahkan!";
    } else {
        echo "Gagal menambahkan barang baru: " . $stmt->error;
    }

    $stmt->close();
}


//hapus barang keluar
if (isset($_POST['hapusbarangkeluar']) && isset($_POST['idpermintaan'])) {
    $idpermintaan = $_POST['idpermintaan'];

    mysqli_begin_transaction($conn);

    // Mengambil data barang keluar sebelum menghapusnya
    $query_barang = "SELECT k.idbarang, k.qty, s.namabarang FROM keluar k JOIN stock s ON k.idbarang = s.idbarang WHERE k.idpermintaan='$idpermintaan'";
    $result_barang = mysqli_query($conn, $query_barang);

    if (!$result_barang) {
        mysqli_rollback($conn);
        echo 'Gagal mengambil data barang keluar';
        exit;
    }

    // Membuat log dengan informasi detail barang yang akan dihapus
    $log_details = [];
    while ($row_barang = mysqli_fetch_assoc($result_barang)) {
        $log_details[] = "Nama Barang: " . $row_barang['namabarang'] . ", Jumlah: " . $row_barang['qty'];
    }

    // Menghapus data dari tabel keluar tanpa mempengaruhi stok
    $hapus_barang = mysqli_query($conn, "DELETE FROM keluar WHERE idpermintaan='$idpermintaan'");
    if (!$hapus_barang) {
        mysqli_rollback($conn);
        echo 'Gagal menghapus barang keluar';
        exit;
    }

    // Menghapus data dari tabel permintaan_keluar
    $hapus_permintaan = mysqli_query($conn, "DELETE FROM permintaan_keluar WHERE idpermintaan='$idpermintaan'");
    if (!$hapus_permintaan) {
        mysqli_rollback($conn);
        echo 'Gagal menghapus permintaan';
        exit;
    }

    mysqli_commit($conn);

    // Mencatat log
    $iduser_logged = $_SESSION['iduser'];
    $email_logged = $_SESSION['email'];
    $log_details_string = implode("; ", $log_details);
    $activity = "$email_logged menghapus permintaan keluar dengan ID $idpermintaan Detail: $log_details_string";
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





// tambah permintaan baru
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

            // Catat log
            $iduser_logged = $_SESSION['iduser'];
            $email_logged = $_SESSION['email'];
            $activity = "$email_logged melakukan tambah permintaan dengan idpermintaan ($idPermintaan)";
            catatLog($conn, $activity, $iduser_logged);

            // Redirect ke halaman permintaan
            header('location: permintaan.php');
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
        header('location:export_keluar.php');
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

// Fungsi untuk mengambil data laporan barang masuk dan keluar
function getLaporanData($conn)
{
    $sql = "
        SELECT 
            b.idbarang,
            b.namabarang,
            COALESCE(SUM(m.qty), 0) AS qty_masuk,
            COALESCE(SUM(k.qty), 0) AS qty_keluar,
            (COALESCE(SUM(m.qty), 0) - COALESCE(SUM(k.qty), 0)) AS stok,
            MAX(m.tanggal) AS tanggal_masuk
        FROM 
            barang_permintaan b
        LEFT JOIN masuk m ON b.idbarang = m.idbarang
        LEFT JOIN keluar k ON b.idbarang = k.idbarang
        GROUP BY b.idbarang, b.namabarang
        ORDER BY b.namabarang, b.idbarang
    ";

    $result = $conn->query($sql);

    $laporanData = [];
    while ($row = $result->fetch_assoc()) {
        $laporanData[] = $row;
    }

    return $laporanData;
}
