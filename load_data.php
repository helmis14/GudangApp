<?php
require 'function.php';
require 'cek.php';
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$database_host = $_ENV['DATABASE_HOST'];
$database_user = $_ENV['DATABASE_USER'];
$database_pass = $_ENV['DATABASE_PASS'];
$database_name = $_ENV['DATABASE_NAME'];
$conn = mysqli_connect($database_host, $database_user, $database_pass, $database_name);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$query = "SELECT * FROM barang_permintaan";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
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

mysqli_close($conn);
