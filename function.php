<?php
session_start();

// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "stokbarangs");

// Menambah barang baru
if (isset($_POST['addnewbarang'])) {
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $stock = $_POST['stock'];
    $lok = $_POST['lokasi'];

    $addtotable = mysqli_query($conn, "INSERT INTO stock (namabarang, deskripsi, stock, lokasi) VALUES ('$namabarang', '$deskripsi', '$stock', '$lok')");
    if ($addtotable) {
        header('location:index.php');
    } else {
        echo 'Gagal';
        header('location:index.php');
    }
}

function convertToBase64($file)
{
    // Memastikan bahwa argumen adalah string yang valid
    if (is_string($file)) {
        // Membaca konten dari file dan mengonversinya ke base64
        $base64 = base64_encode(file_get_contents($file));
        return $base64;
    } else {
        // Menangani kasus di mana argumen bukan string
        return false;
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

    // Proses konversi gambar ke base64
    $bukti_masuk_base64 = $_FILES['bukti_masuk_base64'];
    $tmp_name = $bukti_masuk_base64['tmp_name'];

    if (!empty($tmp_name)) {
        // Konversi gambar ke base64
        $bukti_masuk_base64 = convertToBase64($tmp_name);

        $cekstocksekarang = mysqli_query($conn, "SELECT * FROM stock WHERE idbarang='$barangnya'");
        $ambildatanya = mysqli_fetch_array($cekstocksekarang);

        $stocksekarang = $ambildatanya['stock'];
        $tambahkanstocksekarangdenganquantity = $stocksekarang + $qty;

        $addtomasuk = mysqli_query($conn, "INSERT INTO masuk (idbarang, penerima, qty, keterangan, deskripsi, distributor, bukti_masuk_base64) VALUES ('$barangnya','$penerima','$qty', '$keterangan', '$deskripsi', '$distributor', '$bukti_masuk_base64')");
        $updatestockmasuk = mysqli_query($conn, "UPDATE stock SET stock='$tambahkanstocksekarangdenganquantity' WHERE idbarang='$barangnya'");
        if ($addtomasuk && $updatestockmasuk) {
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

// Menambah barang keluar
if (isset($_POST['addbarangkeluar'])) {
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];
    $keterangan = $_POST['keterangan'];

    // Proses konversi gambar ke base64
    $gambar_base64 = $_FILES['gambar_base64'];
    $tmp_name = $gambar_base64['tmp_name'];

    // Pastikan berkas gambar sudah diunggah
    if (!empty($tmp_name)) {
        // Konversi gambar ke base64
        $gambar_base64 = convertToBase64($tmp_name);

        // Sisipkan data ke database
        $cekstocksekarang = mysqli_query($conn, "SELECT * FROM stock WHERE idbarang='$barangnya'");
        $ambildatanya = mysqli_fetch_array($cekstocksekarang);

        $stocksekarang = $ambildatanya['stock'];
        $tambahkanstocksekarangdenganquantity = $stocksekarang - $qty;

        // Parameterisasi query untuk keamanan
        $addtokeluar = mysqli_prepare($conn, "INSERT INTO keluar (idbarang, penerima, qty, gambar_base64, keterangan) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($addtokeluar, "isiss", $barangnya, $penerima, $qty, $gambar_base64, $keterangan);
        mysqli_stmt_execute($addtokeluar);

        $updatestockmasuk = mysqli_query($conn, "UPDATE stock SET stock='$tambahkanstocksekarangdenganquantity' WHERE idbarang='$barangnya'");

        if ($addtokeluar && $updatestockmasuk) {
            header('location:barang_keluar.php');
        } else {
            echo 'Gagal';
            header('location:barang_keluar.php');
        }
    } else {
        echo 'Berkas gambar tidak diunggah.';
        exit;
    }
}

// Update info barang
if (isset($_POST['updatebarang'])) {
    $idb = $_POST['idb'];
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $lok = $_POST['lokasi'];

    $update = mysqli_query($conn, "UPDATE stock SET namabarang='$namabarang', deskripsi='$deskripsi', lokasi='$lok' WHERE idbarang ='$idb'");
    if ($update) {
        header('location:index.php');
    } else {
        echo 'Gagal';
        header('location:index.php');
    }
}

// Menghapus barang dari stock
if (isset($_POST['hapusbarang'])) {
    $idb = $_POST['idb'];

    $hapus = mysqli_query($conn, "DELETE FROM stock WHERE idbarang='$idb'");
    if ($hapus) {
        header('location:index.php');
    } else {
        echo 'Gagal';
        header('location:index.php');
    }
}

// Mengubah data barang masuk
if (isset($_POST['updatebarangmasuk'])) {
    $idb = $_POST['idb'];
    $idm = $_POST['idm'];
    $deskripsi = $_POST['deskripsi'];
    $qty = $_POST['qty'];
    $distributor = $_POST['distributor'];
    $penerima = $_POST['penerima'];
    $keterangan = $_POST['keterangan'];

    // Handle the updated image
    if (isset($_FILES['update_bukti_masuk']) && $_FILES['update_bukti_masuk']['error'] == 0) {
        $tmp_path = $_FILES['update_bukti_masuk']['tmp_name'];
        $update_bukti_masuk_base64 = convertToBase64($tmp_path);

        // Update the image in the database
        $queryUpdateGambarMasuk = "UPDATE keluar SET bukti_masuk_base64 = ? WHERE idmasuk = ?";
        $stmtUpdateGambarMasuk = mysqli_prepare($conn, $queryUpdateGambarMasuk);
        mysqli_stmt_bind_param($stmtUpdateGambarMasuk, "si", $update_bukti_masuk_base64, $idk);

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
        $kurangistocknya = mysqli_query($conn, "UPDATE stock SET stock='$kurangin' WHERE idbarang='$idb'");
        $updatenya = mysqli_query($conn, "UPDATE masuk SET qty='$qty', keterangan='$deskripsi' WHERE idmasuk='$idm'");
        if ($kurangistocknya && $updatenya) {
            header('location:barang_masuk.php');
        } else {
            echo 'Gagal';
            header('location:barang_masuk.php');
        }
    } else {
        $selisih = $qtysekarang - $qty;
        $kurangin = $stocksekarang - $selisih;
        $kurangistocknya = mysqli_query($conn, "UPDATE stock SET stock='$kurangin' WHERE idbarang='$idb'");
        $updatenya = mysqli_query($conn, "UPDATE masuk SET qty='$qty', keterangan='$deskripsi' WHERE idmasuk='$idm'");
        if ($kurangistocknya && $updatenya) {
            header('location:barang_masuk.php');
        } else {
            echo 'Gagal';
            header('location:barang_masuk.php');
        }
    }
}

// Menghapus data barang masuk
if (isset($_POST['hapusbarangmasuk'])) {
    $idb = $_POST['idb'];
    $qty = $_POST['qty'];
    $idm = $_POST['idm'];

    $ambildatastock = mysqli_query($conn, "SELECT * FROM stock WHERE idbarang='$idb'");
    $data = mysqli_fetch_array($ambildatastock);
    $stok = $data['stock'];
    $selisih = $stok - $qty;

    $update = mysqli_query($conn, "UPDATE stock SET stock='$selisih' WHERE idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "DELETE FROM masuk WHERE idmasuk='$idm'");

    if ($update && $hapusdata) {
        header('location:barang_masuk.php');
    } else {
        header('location:barang_masuk.php');
    }
}

// Mengubah data barang keluar
if (isset($_POST['updatebarangkeluar'])) {
    $idb = $_POST['idb'];
    $idk = $_POST['idk'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];
    $keterangan = $_POST['keterangan'];

    // Handle the updated image
    if (isset($_FILES['update_gambar']) && $_FILES['update_gambar']['error'] == 0) {
        $tmp_path = $_FILES['update_gambar']['tmp_name'];
        $update_gambar_base64 = convertToBase64($tmp_path);

        // Update the image in the database
        $queryUpdateGambar = "UPDATE keluar SET gambar_base64 = ? WHERE idkeluar = ?";
        $stmtUpdateGambar = mysqli_prepare($conn, $queryUpdateGambar);
        mysqli_stmt_bind_param($stmtUpdateGambar, "si", $update_gambar_base64, $idk);

        if (mysqli_stmt_execute($stmtUpdateGambar)) {
            mysqli_stmt_close($stmtUpdateGambar);
        } else {
            echo 'Error updating image: ' . mysqli_error($conn);
            exit;
        }
    }

    $lihatstock = mysqli_query($conn, "SELECT * FROM stock WHERE idbarang='$idb'");
    $stocknya = mysqli_fetch_array($lihatstock);
    $stocksekarang = $stocknya['stock'];

    $qtysekarang = mysqli_query($conn, "SELECT * FROM keluar WHERE idkeluar='$idk'");
    $qtynya = mysqli_fetch_array($qtysekarang);
    $qtysekarang = $qtynya['qty'];

    if ($qty > $qtysekarang) {
        $selisih = $qty - $qtysekarang;
        $kurangin = $stocksekarang - $selisih;
        $kurangistocknya = mysqli_query($conn, "UPDATE stock SET stock='$kurangin' WHERE idbarang='$idb'");
        $updatenya = mysqli_query($conn, "UPDATE keluar SET qty='$qty', penerima='$penerima', keterangan='$keterangan' WHERE idkeluar='$idk'");
        if ($kurangistocknya && $updatenya) {
            header('location:barang_keluar.php');
        } else {
            echo 'Gagal: ' . mysqli_error($conn);
            header('location:barang_keluar.php');
        }
    } else {
        $selisih = $qtysekarang - $qty;
        $kurangin = $stocksekarang + $selisih;
        $kurangistocknya = mysqli_query($conn, "UPDATE stock SET stock='$kurangin' WHERE idbarang='$idb'");
        $updatenya = mysqli_query($conn, "UPDATE keluar SET qty='$qty', penerima='$penerima', keterangan='$keterangan' WHERE idkeluar='$idk'");
        if ($kurangistocknya && $updatenya) {
            header('location:barang_keluar.php');
        } else {
            echo 'Gagal: ' . mysqli_error($conn);
            header('location:barang_keluar.php');
        }
    }
}

// Menghapus data barang keluar
if (isset($_POST['hapusbarangkeluar'])) {
    $idb = $_POST['idb'];
    $qty = $_POST['qty'];
    $idk = $_POST['idk'];

    $ambildatastock = mysqli_query($conn, "SELECT * FROM stock WHERE idbarang='$idb'");
    $data = mysqli_fetch_array($ambildatastock);
    $stok = $data['stock'];
    $selisih = $stok + $qty;

    $update = mysqli_query($conn, "UPDATE stock SET stock='$selisih' WHERE idbarang='$idb'");
    $hapusdata = mysqli_query($conn, "DELETE FROM keluar WHERE idkeluar='$idk'");

    if ($update && $hapusdata) {
        header('location:barang_keluar.php');
    } else {
        header('location:barang_keluar.php');
    }
}

// Menambah admin baru
if (isset($_POST['addnewadmin'])) {
    $em = $_POST['email'];
    $iduser = $_POST['iduser'];
    $pass = $_POST['password'];

    $addtotable = mysqli_query($conn, "INSERT INTO login (email, iduser, password) VALUES ('$em','$iduser','$pass')");
    if ($addtotable) {
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

    $update = mysqli_query($conn, "UPDATE login SET email='$em', password='$pass' WHERE iduser ='$iduser'");
    if ($update) {
        header('location:admin.php');
    } else {
        echo 'Gagal';
        header('location:admin.php');
    }
}

// Menghapus admin dari kelola admin
if (isset($_POST['hapusadmin'])) {
    $iduser = $_POST['iduser'];

    $hapus = mysqli_query($conn, "DELETE FROM login WHERE iduser='$iduser'");
    if ($hapus) {
        header('location:admin.php');
    } else {
        echo 'Gagal';
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

            header('location: permintaan.php');
        } else {
            echo 'Gagal menambahkan permintaan';
        }
    } else {
        echo 'Berkas gambar tidak diunggah';
        exit;
    }
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
        // Ambil nilai yang sesuai dari array
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

    // Handle the updated image
    if (isset($_FILES['update_permintaan']) && $_FILES['update_permintaan']['error'] == 0) {
        $tmp_path = $_FILES['update_permintaan']['tmp_name'];
        $update_permintaan_base64 = convertToBase64($tmp_path);

        // Update the image in the database
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

    // Commit transaksi
    mysqli_commit($conn);

    header('location: permintaan.php');
    exit;
}






// Menghapus permintaan barang
if (isset($_POST['hapuspermintaan']) && isset($_POST['idpermintaan'])) {
    $idpermintaan = $_POST['idpermintaan'];


    mysqli_begin_transaction($conn);
    $hapus_barang = mysqli_query($conn, "DELETE FROM barang_permintaan WHERE idpermintaan='$idpermintaan'");
    $hapus_permintaan = mysqli_query($conn, "DELETE FROM permintaan WHERE idpermintaan='$idpermintaan'");
    if ($hapus_barang && $hapus_permintaan) {
        mysqli_commit($conn);
        header('location:permintaan.php');
        echo 'berhasil menghapus';
    } else {
        mysqli_rollback($conn);
        echo 'Gagal menghapus permintaan';
    }
} else {
    echo ' ';
}
