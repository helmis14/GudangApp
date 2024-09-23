<?php
require '../../helper/function.php';
require '../../helper/cek.php';

$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];

$search = isset($_GET['search']) ? $_GET['search'] : '';

$query = "SELECT m.*, s.namabarang, s.unit FROM masuk m 
          JOIN stock s ON s.idbarang = m.idbarang";

if ($search !== '') {
    $query .= " WHERE s.namabarang LIKE '%$search%' 
                OR m.tanggal LIKE '%$search%' 
                OR m.distributor LIKE '%$search%' 
                OR m.keterangan LIKE '%$search%' 
                OR m.penerima LIKE '%$search%'";
}


$ambilsemuadatamasuk = mysqli_query($conn, $query);

if (mysqli_num_rows($ambilsemuadatamasuk) > 0) {
    while ($data = mysqli_fetch_array($ambilsemuadatamasuk)) {
        $idb = $data['idbarang'];
        $idm = $data['idmasuk'];
        $tanggal = $data['tanggal'];
        $namabarang = $data['namabarang'];
        $qty = $data['qty'];
        $keterangan = $data['keterangan'];
        $penerima = $data['penerima'];
        $unit = $data['unit'];
        $distributor = $data['distributor'];
        $bukti_masuk_base64 = $data['bukti_masuk_base64'];
        $status = $data['status'];
?>
        <tr>
            <td><?= $tanggal; ?></td>
            <td><?= $namabarang; ?></td>
            <td><?= $unit; ?></td>
            <td><?= $qty; ?></td>
            <td><?= $distributor; ?></td>
            <td><?= $penerima; ?></td>
            <td><?= $keterangan; ?></td>
            <td>
                <a href="#" class="gambar-mini-trigger" data-toggle="modal" data-target="#gambarModal<?= $idm; ?>" data-id="<?= $idm; ?>">
                    <button type="button" class="btn btn-success">Lihat Bukti</button>
                </a>
                <!-- Modal untuk menampilkan gambar penuh -->
                <div class="modal fade" id="gambarModal<?= $idm; ?>" tabindex="-1" role="dialog" aria-labelledby="gambarModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="gambarModalLabel">Bukti Masuk</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <img src="data:image/jpeg;base64,<?= $bukti_masuk_base64; ?>" class="img-fluid" alt="Bukti Masuk Belum Di Upload">
                            </div>
                            <div class="modal-footer">
                                <a href="download_gambar_masuk.php?id=<?= $idm; ?>&type=keluar" class="btn btn-primary" download>Download</a>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
            <td><?= ($status == 0) ? 'Dalam Pengiriman' : ($status == 1 ? 'Diterima' : 'Tidak Diterima'); ?></td>
            <td>
                <?php if (($role === 'dev' || $role === 'gudang') && $status == 0) : ?>
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idm; ?>">Edit</button>
                    <!-- Edit Modal Baru -->
                    <div class="modal fade" id="edit<?= $idm; ?>">
                        <div class="modal-dialog">
                            <div class="modal-content">

                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h4 class="modal-title">Edit Barang</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>

                                <!-- Modal body -->
                                <form method="post" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <label for="penerima">Penerima</label>
                                        <input type="text" name="penerima" value="<?= $penerima; ?>" class="form-control">
                                        <br>
                                        <label for="qty">Jumlah:</label>
                                        <input type="number" name="qty" value="<?= $qty; ?>" class="form-control">
                                        <label for="keterangan">Keterangan:</label>
                                        <input type="text" name="keterangan" value="<?= $keterangan; ?>" class="form-control">
                                        <br>
                                        <label for="distributor">Distributor:</label>
                                        <input type="text" name="distributor" value="<?= $distributor; ?>" class="form-control">
                                        <br>
                                        <label for="update_bukti_masuk">Bukti Masuk:</label>
                                        <input type="file" name="update_bukti_masuk" class="form-control-file" accept="image/*">
                                        <br>
                                        <input type="hidden" name="idb" value="<?= $idb; ?>">
                                        <input type="hidden" name="idm" value="<?= $idm; ?>">
                                        <button type="submit" class="btn btn-primary" name="updatebarangmasuk">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($role === 'dev') : ?>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idm; ?>">Delete</button>
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
                                        <input type="hidden" name="qty" value="<?= $qty; ?>">
                                        <input type="hidden" name="idm" value="<?= $idm; ?>">
                                        <br>
                                        <br>
                                        <button type="submit" class="btn btn-danger" name="hapusbarangmasuk">Hapus</button>
                                    </div>
                                </form>

                            </div>

                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($role === 'gudang' && $status == 0) : ?>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idm; ?>">Status</button>
                    <!-- Modal untuk mengubah status barang-->
                    <div class="modal fade" id="statusModal<?= $idm; ?>" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="statusModalLabel">Ubah Status Permintaan</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form action="update_status_masuk.php" method="POST">
                                        <input type="hidden" name="idm" value="<?= $idm; ?>">
                                        <div class="form-group">
                                            <label for="status">Status Barang:</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="0">Dalam Pengiriman</option>
                                                <option value="1">Diterima</option>
                                                <option value="2">Tidak Diterima</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php elseif ($status == 1 || $status == 2) : ?>
                    <span>Ditanggapi</span>
                <?php else : ?>
                    <span>Belum Ditanggapi</span>
                <?php endif; ?>
            </td>
        </tr>
<?php
    }
} else {
    echo '<tr><td colspan="9">Data yang dicari tidak ada</td></tr>';
}
?>