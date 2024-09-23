<?php
require '../../helper/function.php';
require '../../helper/cek.php';

if (isset($_POST['tgl_mulai']) && isset($_POST['tgl_selesai'])) {
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $nama_barang = isset($_POST['nama_barang']) ? $_POST['nama_barang'] : '';

    // Debug: Output the dates and nama_barang received from the form
    echo "<!-- Received dates: tgl_mulai = '$tgl_mulai', tgl_selesai = '$tgl_selesai', nama_barang = '$nama_barang' -->";

    // Query to fetch filtered data
    $query = "SELECT 
                keluar.idpermintaan, 
                permintaan_keluar.tanggal, 
                stock.namabarang, 
                stock.unit, 
                keluar.qty, 
                keluar.keterangan 
            FROM 
                permintaan_keluar
            INNER JOIN 
                keluar ON permintaan_keluar.idpermintaan = keluar.idpermintaan
            INNER JOIN 
                stock ON keluar.idbarang = stock.idbarang
            WHERE 
                permintaan_keluar.tanggal BETWEEN ? AND ? 
                AND stock.namabarang LIKE ?
            ORDER BY 
                permintaan_keluar.idpermintaan DESC";

    $like_nama_barang = '%' . $nama_barang . '%';

    // Prepare statement
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $tgl_mulai, $tgl_selesai, $like_nama_barang);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $tanggal = htmlspecialchars($row['tanggal'], ENT_QUOTES, 'UTF-8');
            $namabarang = htmlspecialchars($row['namabarang'], ENT_QUOTES, 'UTF-8');
            $unit = htmlspecialchars($row['unit'], ENT_QUOTES, 'UTF-8');
            $qty = htmlspecialchars($row['qty'], ENT_QUOTES, 'UTF-8');
            $keterangan = htmlspecialchars($row['keterangan'], ENT_QUOTES, 'UTF-8');

            echo "<tr>
                    <td>$tanggal</td>
                    <td>$namabarang</td>
                    <td>$qty</td>
                    <td>$unit</td>
                    <td>$keterangan</td>
                </tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No data found</td></tr>";
    }
} else {
    echo "<!-- No dates received -->";
}
?>
