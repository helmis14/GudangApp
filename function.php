<?php
session_start();

//koneksi ke database
$conn = mysqli_connect("localhost","root","","stokbarangs");

//menambah barang baru
if(isset($_POST['addnewbarang'])){
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $stock = $_POST['stock'];
    $lok = $_POST['lokasi'];

    $addtotable = mysqli_query($conn,"insert into stock (namabarang, deskripsi, stock, lokasi) values('$namabarang','$deskripsi','$stock','$lok')");
    if($addtotable){
        header('location:index.php');
    } else {
        echo 'Gagal';
        header('location:index.php');
    
    }
};


function convertToBase64($file_path)
{
    $image_data = file_get_contents($file_path);
    $base64_image = base64_encode($image_data);
    return $base64_image;
}

if(isset($_POST['barangmasuk'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];
    $keterangan = $_POST['keterangan'];
    $distributor = $_POST['distributor'];
    $deskripsi = $_POST['deskripsi'];
     // Proses konversi gambar ke base64
     $bukti_masuk_base64 = $_FILES['bukti_masuk_base64'];
     $tmp_name = $bukti_masuk_base64['tmp_name'];
     // $bukti_masuk_base64 = $_POST['bukti_masuk_base64'];
 
     if (!empty($tmp_name)) {
     // Konversi gambar ke base64
     $bukti_masuk_base64 = convertToBase64($tmp_name);
   

    $cekstocksekarang = mysqli_query($conn,"select * from stock where idbarang='$barangnya'");
    $ambildatanya = mysqli_fetch_array($cekstocksekarang);

    $stocksekarang = $ambildatanya['stock'];
    $tambahkanstocksekarangdenganquantity = $stocksekarang+$qty;

    $addtomasuk = mysqli_query($conn,"insert into masuk (idbarang, penerima, qty, keterangan, deskripsi, distributor, bukti_masuk_base64) values('$barangnya','$penerima','$qty', '$keterangan', '$deskripsi', '$distributor', '$bukti_masuk_base64')");
    $updatestockmasuk = mysqli_query($conn,"update stock set stock='$tambahkanstocksekarangdenganquantity' where idbarang='$barangnya'");
    if($addtomasuk&&$updatestockmasuk){
        header('location:barang_masuk.php');
    } else {
        echo 'Gagal';
        header('location:barang_masuk.php');
    }
}else{
    echo 'Gambar Tidak ada';
    exit;
}
}

//menambah barang keluar
if(isset($_POST['addbarangkeluar'])){
    $barangnya = $_POST['barangnya'];
    $penerima = $_POST['penerima'];
    // $deskripsi = $_POST['deskripsi'];
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
        
        if($addtokeluar && $updatestockmasuk){
            header('location:barang_keluar.php');
        } else {
            echo 'Gagal';
            header('location:barang_keluar.php');
        }
    } else {
        // Handle jika berkas gambar tidak diunggah
        echo 'Berkas gambar tidak diunggah.';
        exit;
    }
}






//Update info barang
if(isset($_POST['updatebarang'])){
    $idb = $_POST['idb'];
    $namabarang = $_POST['namabarang'];
    $deskripsi = $_POST['deskripsi'];
    $lok = $_POST['lokasi'];

    $update = mysqli_query($conn,"update stock set namabarang='$namabarang', deskripsi='$deskripsi', lokasi='$lok' where idbarang ='$idb'");
    if($update){
        header('location:index.php');
    } else {
        echo 'Gagal';
        header('location:index.php');
    }
}



//Menghapus barang dari stock
if(isset($_POST['hapusbarang'])){
    $idb = $_POST['idb'];

    $hapus = mysqli_query($conn, "delete from stock where idbarang='$idb'");
    if($hapus){
        header('location:index.php');
    } else {
        echo 'Gagal';
        header('location:index.php');
    }
}


//Mengubah data barang masuk
if(isset($_POST['updatebarangmasuk'])){
    $idb = $_POST['idb'];
    $idm = $_POST['idm'];
    $deskripsi = $_POST['deskripsi'];
    $qty = $_POST['qty'];
    $distributor = $_POST['distributor'];
    $penerima = $_POST['penerima'];
    $keterangan = $_POST['keterangan'];

    // Handle the updated image
    if(isset($_FILES['update_bukti_masuk']) && $_FILES['update_bukti_masuk']['error'] == 0) {
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
    

    $lihatstock = mysqli_query($conn,"select * from stock where idbarang='$idb'");
    $stocknya = mysqli_fetch_array($lihatstock);
    $stocksekarang = $stocknya['stock'];

    $qtysekarang = mysqli_query($conn, "select * from masuk where idmasuk='$idm'");
    $qtynya = mysqli_fetch_array($qtysekarang);
    $qtysekarang = $qtynya['qty'];

    if($qty>$qtysekarang){
        $selisih = $qty-$qtysekarang;
        $kurangin = $stocksekarang + $selisih;
        $kurangistocknya = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn,"update masuk set qty='$qty', keterangan='$deskripsi' where idmasuk='$idm'");
            if($kurangistocknya&&$updatenya){
                header('location:barang_masuk.php');
                } else {
                    echo 'Gagal';
                    header('location:barang_masuk.php');
                }
    } else {
        $selisih = $qtysekarang-$qty;
        $kurangin = $stocksekarang - $selisih;
        $kurangistocknya = mysqli_query($conn, "update stock set stock='$kurangin' where idbarang='$idb'");
        $updatenya = mysqli_query($conn,"update masuk set qty='$qty', keterangan='$deskripsi' where idmasuk='$idm'");
            if($kurangistocknya&&$updatenya){
                header('location:barang_masuk.php');
                } else {
                    echo 'Gagal';
                    header('location:barang_masuk.php');
                }  
    }
}


//Menghapus data barang masuk

if(isset($_POST['hapusbarangmasuk'])){
    $idb = $_POST['idb'];
    $qty = $_POST['qty'];
    $idm = $_POST['idm'];

    $ambildatastock = mysqli_query($conn, "select * from stock where idbarang='$idb'");
    $data = mysqli_fetch_array($ambildatastock);
    $stok = $data['stock'];
    $selisih = $stok-$qty;

    $update = mysqli_query($conn,"update stock set stock='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn,"delete from masuk where idmasuk='$idm'");

    if($update&&$hapusdata){
        header('location:barang_masuk.php');
    } else {
        header('location:barang_masuk.php');
    }
}


//Mengubah data barang keluar
if(isset($_POST['updatebarangkeluar'])){
    $idb = $_POST['idb'];
    $idk = $_POST['idk'];
    $penerima = $_POST['penerima'];
    $qty = $_POST['qty'];
    $keterangan = $_POST['keterangan'];

    // Handle the updated image
    if(isset($_FILES['update_gambar']) && $_FILES['update_gambar']['error'] == 0) {
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

    $lihatstock = mysqli_query($conn,"SELECT * FROM stock WHERE idbarang='$idb'");
    $stocknya = mysqli_fetch_array($lihatstock);
    $stocksekarang = $stocknya['stock'];

    $qtysekarang = mysqli_query($conn, "SELECT * FROM keluar WHERE idkeluar='$idk'");
    $qtynya = mysqli_fetch_array($qtysekarang);
    $qtysekarang = $qtynya['qty'];

    if($qty > $qtysekarang){
        $selisih = $qty - $qtysekarang;
        $kurangin = $stocksekarang - $selisih;
        $kurangistocknya = mysqli_query($conn, "UPDATE stock SET stock='$kurangin' WHERE idbarang='$idb'");
        $updatenya = mysqli_query($conn,"UPDATE keluar SET qty='$qty', penerima='$penerima', keterangan='$keterangan' WHERE idkeluar='$idk'");
        if($kurangistocknya && $updatenya){
            header('location:barang_keluar.php');
        } else {
            echo 'Gagal: ' . mysqli_error($conn);
            header('location:barang_keluar.php');
        }
    } else {
        $selisih = $qtysekarang - $qty;
        $kurangin = $stocksekarang + $selisih;
        $kurangistocknya = mysqli_query($conn, "UPDATE stock SET stock='$kurangin' WHERE idbarang='$idb'");
        $updatenya = mysqli_query($conn,"UPDATE keluar SET qty='$qty', penerima='$penerima', keterangan='$keterangan' WHERE idkeluar='$idk'");
        if($kurangistocknya && $updatenya){
            header('location:barang_keluar.php');
        } else {
            echo 'Gagal: ' . mysqli_error($conn);
            header('location:barang_keluar.php');
        }  
    }
}




//Menghapus data barang keluar

if(isset($_POST['hapusbarangkeluar'])){
    $idb = $_POST['idb'];
    $qty = $_POST['qty'];
    $idk = $_POST['idk'];

    $ambildatastock = mysqli_query($conn, "select * from stock where idbarang='$idb'");
    $data = mysqli_fetch_array($ambildatastock);
    $stok = $data['stock'];
    $selisih = $stok+$qty;

    $update = mysqli_query($conn,"update stock set stock='$selisih' where idbarang='$idb'");
    $hapusdata = mysqli_query($conn,"delete from keluar where idkeluar='$idk'");

    if($update&&$hapusdata){
        header('location:barang_keluar.php');
    } else {
        header('location:barang_keluar.php');
    }
}


//menambah admin baru
if(isset($_POST['addnewadmin'])){
    $em = $_POST['email'];
    $iduser = $_POST['iduser'];
    $pass = $_POST['password'];

    $addtotable = mysqli_query($conn,"insert into login (email, iduser, password) values('$em','$iduser','$pass')");
    if($addtotable){
        header('location:admin.php');
    } else {
        echo 'Gagal';
        header('location:admin.php');
    
    }
};

//Update perubahan user
if(isset($_POST['updateadmin'])){
    $iduser = $_POST['iduser'];
    $em = $_POST['email'];
    $pass = $_POST['password'];

    $update = mysqli_query($conn,"update login set email='$em', password='$pass' where iduser ='$iduser'");
    if($update){
        header('location:admin.php');
    } else {
        echo 'Gagal';
        header('location:admin.php');
    }
}

//Menghapus admin dari kelola admin
if(isset($_POST['hapusadmin'])){
    $iduser = $_POST['iduser'];

    $hapus = mysqli_query($conn,"delete from login where iduser='$iduser'");
    if($hapus){
        header('location:admin.php');
    } else {
        echo 'Gagal';
        header('location:admin.php');
    }
}


//menambah permintaan barang
if(isset($_POST['addnewpermintaan'])){
    $namabarang = $_POST['namabarang'];
    $unit = $_POST['unit'];
    $qtypermintaan = $_POST['qtypermintaan'];
    $ket = $_POST['keterangan'];
    $status = $_POST['status'];

    // Proses konversi gambar ke base64
    $bukti_base64 = $_FILES['bukti_base64'];
    $tmp_name = $bukti_base64['tmp_name'];
    // $bukti_base64 = $_POST['bukti_base64'];

    if (!empty($tmp_name)) {
    // Konversi gambar ke base64
    $bukti_base64 = convertToBase64($tmp_name);

    $addtotable = mysqli_query($conn,"insert into permintaan (namabarang, unit, qtypermintaan, keterangan, bukti_base64, status) values('$namabarang','$unit','$qtypermintaan','$ket','$bukti_base64', '$status')");
    if($addtotable){
        header('location:permintaan.php');
    } else {
        echo 'Gagal';
        header('location:permintaan.php');
    
    }
} else {
    echo 'Berkas gambar tidak diunggah';
    exit;
}
};

//mengubah data permintaan 
if(isset($_POST['updatepermintaan'])){
    $idpermintaan = $_POST['idpermintaan']; // Sesuaikan nama input dengan yang sesuai
    $namabarang = $_POST['namabarang'];
    $unit = $_POST['unit'];
    $qtypermintaan = $_POST['qtypermintaan'];
    $ket = $_POST['keterangan'];
    $status = $_POST['status'];

    // Handle the updated image
    if(isset($_FILES['update_permintaan']) && $_FILES['update_permintaan']['error'] == 0) {
        $tmp_path = $_FILES['update_permintaan']['tmp_name'];
        $update_permintaan_base64 = convertToBase64($tmp_path);

        // Update the image in the database
        $queryUpdatePermintaan = "UPDATE permintaan SET bukti_base64 = ? WHERE idpermintaan = ?";
        $stmtUpdatePermintaan = mysqli_prepare($conn, $queryUpdatePermintaan);
        mysqli_stmt_bind_param($stmtUpdatePermintaan, "si", $update_permintaan_base64, $idpermintaan);

        if (!mysqli_stmt_execute($stmtUpdatePermintaan)) {
            echo 'Error updating image: ' . mysqli_error($conn);
            exit;
        }

        mysqli_stmt_close($stmtUpdatePermintaan);
    }

    // Update other fields in the database
    $queryUpdateDataPermintaan = "UPDATE permintaan SET namabarang = ?, unit = ?, qtypermintaan = ?, keterangan = ?, status = ? WHERE idpermintaan = ?";
    $stmtUpdateDataPermintaan = mysqli_prepare($conn, $queryUpdateDataPermintaan);
    mysqli_stmt_bind_param($stmtUpdateDataPermintaan, "sssisi", $namabarang, $unit, $qtypermintaan, $ket, $status, $idpermintaan);

    if (!mysqli_stmt_execute($stmtUpdateDataPermintaan)) {
        echo 'Error updating data: ' . mysqli_error($conn);
        exit;
    }

    mysqli_stmt_close($stmtUpdateDataPermintaan);

    header('location:permintaan.php');
}


//Menghapus permintaan barang
if(isset($_POST['hapuspermintaan'])){
    $idp = $_POST['idpermintaan'];

    $hapus = mysqli_query($conn,"delete from permintaan where idpermintaan='$idp'");
    if($hapus){
        header('location:permintaan.php');
    } else {
        echo 'Gagal';
        header('location:permintaan.php');
    }
}

?>