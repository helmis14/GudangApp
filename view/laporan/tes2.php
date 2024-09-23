<?php
require '../../helper/function.php';
require '../../helper/cek.php';
require '../../vendor/autoload.php';

$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];
// Fetch total stock
$totalStockQuery = "SELECT idbarang, namabarang, SUM(qty) AS total_stock FROM stock GROUP BY idbarang";
$totalStockResult = mysqli_query($conn, $totalStockQuery);

// Fetch all outflows (keluar)
$outflowQuery = "SELECT idbarang, SUM(qty) AS total_outflow FROM keluar GROUP BY idbarang";
$outflowResult = mysqli_query($conn, $outflowQuery);

// Fetch all inflows (masuk)
$inflowQuery = "SELECT idbarang, SUM(qty) AS total_inflow FROM masuk GROUP BY idbarang";
$inflowResult = mysqli_query($conn, $inflowQuery);

// Fetch all requests
$requestQuery = "SELECT idbarang, SUM(qtypermintaan) AS total_request FROM barang_permintaan GROUP BY idbarang";
$requestResult = mysqli_query($conn, $requestQuery);


if (mysqli_num_rows($ambilsemuadatastock) > 0) {
    while ($data = mysqli_fetch_array($ambilsemuadatastock)) {
        $idbarang = $data['idbarang'];
        $namabarang = $data['namabarang'];
        $kategori = $data['kategori'];
        $unit = $data['unit'];
        $stock = $data['stock'];
        $lok = $data['lokasi'];
        $idb = $data['idbarang'];
?>
        <tr>
            <td><?= $idbarang; ?></td>
            <td><?= $namabarang; ?></td>
            <td><?= $kategori; ?></td>
            <td><?= $unit; ?></td>
            <td><?= $stock; ?></td>
            <td><?= $lok; ?></td>
            <td>
                <?php if (in_array($role, ['dev', 'gudang'])) : ?>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#edit<?= $idb; ?>">
                        Lihat Barang
                    </button>
                <?php endif; ?>
            </td>
        </tr>
<?php
    }
} else {
    echo '<tr><td colspan="7">Data yang dicari tidak ada</td></tr>';
}
?>