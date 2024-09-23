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
                <?php if (in_array($role, ['dev', 'gudang'])) : ?>
                 <td>
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idb; ?>">
                        Edit
                    </button>
                <?php endif; ?>

                <?php if ($role === 'dev') : ?>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idb; ?>">
                        Delete
                    </button>
                <?php endif; ?>

                <!-- Edit Modal -->
                <div class="modal fade" id="edit<?= $idb; ?>">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">Edit Barang</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <form method="post">
                                <div class="modal-body">
                                    <label for="namabarang">Nama Barang</label>
                                    <input type="text" name="namabarang" value="<?= $namabarang; ?>" class="form-control" required>
                                    <br>
                                    <label for="kategori">Kategori:</label>
                                    <input type="text" name="kategori" value="<?= $kategori; ?>" class="form-control" required>
                                    <br>
                                    <label for="unit">Unit:</label>
                                    <input type="text" name="unit" value="<?= $unit; ?>" class="form-control" required>
                                    <br>
                                    <label for="lokasi">Lokasi:</label>
                                    <input type="text" name="lokasi" value="<?= $lok; ?>" class="form-control" required>
                                    <br>
                                    <input type="hidden" name="idb" value="<?= $idb; ?>">
                                    <button type="submit" class="btn btn-primary" name="updatebarang">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div class="modal fade" id="delete<?= $idb; ?>">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h4 class="modal-title">Hapus Barang</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>

                            <!-- Modal body -->
                            <form method="post">
                                <div class="modal-body">
                                    Apakah Anda Yakin Ingin Menghapus <?= $namabarang; ?>?
                                    <input type="hidden" name="idb" value="<?= $idb; ?>">
                                    <br>
                                    <br>
                                    <button type="submit" class="btn btn-danger" name="hapusbarang">Hapus</button>
                                </div>
                            </form>

                        </div>

                    </div>
                </div>
                </div>
            </td>
        </tr>
<?php
    }
} else {
    echo '<tr><td colspan="7">Data yang dicari tidak ada</td></tr>';
}
?>