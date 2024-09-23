<?php
require '../../helper/function.php';
require '../../helper/cek.php';

if (isset($_POST['tgl_mulai']) && isset($_POST['tgl_selesai'])) {
    $tgl_mulai = $_POST['tgl_mulai'];
    $tgl_selesai = $_POST['tgl_selesai'];
    $nama_barang = isset($_POST['nama_barang']) ? $_POST['nama_barang'] : '';

    // Query to fetch filtered data for barang masuk
    $query = "SELECT 
                masuk.tanggal, 
                stock.namabarang, 
                stock.unit, 
                masuk.qty, 
                masuk.distributor,
                masuk.penerima,
                masuk.keterangan 
            FROM 
                masuk
            INNER JOIN 
                stock ON masuk.idbarang = stock.idbarang
            WHERE 
                masuk.tanggal BETWEEN ? AND ? 
                AND stock.namabarang LIKE ?
            ORDER BY 
                masuk.tanggal DESC";

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
            $distributor = htmlspecialchars($row['distributor'], ENT_QUOTES, 'UTF-8');
            $penerima = htmlspecialchars($row['penerima'], ENT_QUOTES, 'UTF-8');
            $keterangan = htmlspecialchars($row['keterangan'], ENT_QUOTES, 'UTF-8');

            echo "<tr>
                    <td>$tanggal</td>
                    <td>$namabarang</td>
                    <td>$unit</td>
                    <td>$qty</td>
                    <td>$distributor</td>
                    <td>$penerima</td>
                    <td>$keterangan</td>
                </tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No data found</td></tr>";
    }
} else {
    echo "<!-- No dates received -->";
}
