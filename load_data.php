<?php
// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "stokbarangs");

// Periksa koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Query untuk mengambil data barang dari database
$query = "SELECT * FROM barang_permintaan";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    // Tampilkan data barang dalam bentuk tabel
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['idpermintaan'] . "</td>";
        echo "<td>" . $row['idbarang'] . "</td>";
        echo "<td>" . $row['namabarang'] . "</td>";
        echo "<td><button class='btn btn-danger delete-btn' data-id='" . $row['id'] . "'>Hapus</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>Tidak ada data barang</td></tr>";
}

// Tutup koneksi ke database
mysqli_close($conn);
