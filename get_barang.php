<?php
// Sambungkan ke database
include 'function.php';

// Ambil data barang dari database
$query = "SELECT idbarang, namabarang FROM stock";
$result = mysqli_query($conn, $query);

// Inisialisasi array untuk menyimpan data barang
$barang = array();

// Ambil data barang dan simpan dalam array
while ($row = mysqli_fetch_assoc($result)) {
    $barang[] = $row;
}

// Konversi array barang ke JSON dan kirimkan sebagai respons
echo json_encode($barang);
