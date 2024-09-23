<?php
require '../../helper/function.php';
require '../../helper/cek.php';
require '../../vendor/autoload.php';

$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];

$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT * FROM stock";
if ($search !== '') {
    $query .= " WHERE namabarang LIKE '%$search%' OR kategori LIKE '%$search%' OR lokasi LIKE '%$search%'";
}

$ambilsemuadatastock = mysqli_query($conn, $query);

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