<?php
require '../../helper/function.php';
require '../../helper/cek.php';

$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];


$search = isset($_GET['search']) ? $_GET['search'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$query = "SELECT 
    keluar.idpermintaan, 
    permintaan_keluar.tanggal, 
    permintaan_keluar.status,
    permintaan_keluar.gambar_base64,
    permintaan_keluar.bukti_wo,
    permintaan_keluar.status2,
    GROUP_CONCAT(CONCAT(stock.namabarang, '')) as nama_barang, 
    GROUP_CONCAT(stock.unit) as unit, 
    GROUP_CONCAT(keluar.qty) as qty, 
    GROUP_CONCAT(keluar.penerima) as penerima, 
    GROUP_CONCAT(keluar.keterangan) as keterangan 
FROM 
    permintaan_keluar 
INNER JOIN 
    keluar ON permintaan_keluar.idpermintaan = keluar.idpermintaan
INNER JOIN 
    stock ON keluar.idbarang = stock.idbarang";

$conditions = [];
if ($start_date !== '' && $end_date !== '') {
    $conditions[] = "permintaan_keluar.tanggal BETWEEN '$start_date' AND '$end_date'";
}

if ($search !== '') {
    $conditions[] = "(stock.namabarang LIKE '%$search%' 
                    OR keluar.penerima LIKE '%$search%' 
                    OR keluar.keterangan LIKE '%$search%' 
                    OR permintaan_keluar.status LIKE '%$search%' 
                    OR permintaan_keluar.tanggal LIKE '%$search%')";
}

if (count($conditions) > 0) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

$query .= " GROUP BY keluar.idpermintaan 
            ORDER BY keluar.idpermintaan DESC 
            LIMIT 100";

$ambilsemuadatabarangkeluar = mysqli_query($conn, $query);

if (mysqli_num_rows($ambilsemuadatabarangkeluar) > 0) {
    while ($data = mysqli_fetch_array($ambilsemuadatabarangkeluar)) {
        $idpermintaan = $data['idpermintaan'];
        $tanggal = $data['tanggal'];
        $nama_barang = explode(",", $data['nama_barang']);
        $unit = explode(",", $data['unit']);
        $qty = explode(",", $data['qty']);
        $penerima = explode(",", $data['penerima']);
        $keterangan = explode(",", $data['keterangan']);
        $status = $data['status'];
        $gambar_base64 = $data['gambar_base64'];
        $bukti_wo = $data['bukti_wo'];
        $status2 = $data['status2'];
?>

        <tr>
            <td style="text-align:center; width:130px"><?= $tanggal; ?></td>
            <td style="width:300px">
                <?php
                foreach ($nama_barang as $key => $barang) {
                    echo ($key + 1) . ". " . $barang . "<br>";
                }
                ?>
            </td>
            <td style="text-align:center">
                <?php
                foreach ($unit as $item) {
                    echo $item . "<br>";
                }
                ?>
            </td>
            <td style="text-align:center">
                <?php
                foreach ($qty as $item) {
                    echo $item . "<br>";
                }
                ?>
            </td>
            <td style="text-align:center">
                <?php
                foreach ($penerima as $item) {
                    echo $item . "<br>";
                }
                ?>
            </td>
            <td style="width:300px">
                <?php
                foreach ($keterangan as $item) {
                    echo $item . "<br>";
                }
                ?>
            </td>
            <td style="text-align:center">
                <button type="button" class="btn btn-success gambar-modal-trigger" data-idpermintaan="<?= $idpermintaan; ?>" data-toggle="modal" data-target="#gambarModal<?= $idpermintaan; ?>">
                    Lihat
                </button>
                <!-- Modal untuk menampilkan gambar penuh WO -->
                <div class="modal fade" id="gambarModal<?= $idpermintaan; ?>" tabindex="-1" role="dialog" aria-labelledby="gambarModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="gambarModalLabel">Bukti WO</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <img src="data:image/jpeg;base64,<?= $gambar_base64; ?>" class="img-fluid" alt="Bukti WO Belum Di Upload">
                            </div>
                            <div class="modal-footer">
                                <a href="download_gambar_keluar.php?id=<?= $idpermintaan; ?>&type=wo" class="btn btn-primary" download>Download</a>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
            <td style="text-align:center">
                <button type="button" class="btn btn-success wo-modal-trigger" data-idpermintaan="<?= $idpermintaan; ?>" data-toggle="modal" data-target="#WoModal<?= $idpermintaan; ?>">
                    Lihat
                </button>
                <!-- Modal untuk menampilkan gambar penuh Keluar-->
                <div class="modal fade" id="WoModal<?= $idpermintaan; ?>" tabindex="-1" role="dialog" aria-labelledby="gambarModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="gambarModalLabel">Bukti Keluar</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <img src="data:image/jpeg;base64,<?= $bukti_wo; ?>" class="img-fluid" alt="Bukti Keluar Belum Di Upload">
                            </div>
                            <div class="modal-footer">
                                <a href="download_bukti_wo.php?id=<?= $idpermintaan; ?>&type=keluar" class="btn btn-primary" download>Download</a>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
            <td>
                <?php
                $allApproved = ($status == 1 && $status2 == 1);
                $allRejected = ($status == 2 && $status2 == 2);
                ?>
                <?php if ($allApproved) : ?>
                    Disetujui
                <?php elseif ($allRejected) : ?>
                    Ditolak
                <?php else : ?>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#viewStatusModal<?= $idpermintaan; ?>">
                        Lihat Status
                    </button>
                    <!-- Modal untuk melihat status permintaan -->
                    <div class="modal fade" id="viewStatusModal<?= $idpermintaan; ?>" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="viewStatusModalLabel">Status Permintaan</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>BM atau CE
                                        <span class="<?= ($status == 0) ? 'text-warning' : ($status == 1 ? 'text-success' : 'text-danger'); ?>">
                                            <?= ($status == 0) ? 'belum menanggapi' : ($status == 1 ? 'menyetujui' : 'tidak menyetujui'); ?>
                                        </span>
                                    </p>
                                    <hr>
                                    <p>Gudang
                                        <span class="<?= ($status2 == 0) ? 'text-warning' : ($status2 == 1 ? 'text-success' : 'text-danger'); ?>">
                                            <?= ($status2 == 0) ? 'belum menanggapi' : ($status2 == 1 ? 'menyetujui' : 'tidak menyetujui'); ?>
                                        </span>
                                    </p>
                                    <hr>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($role === 'superadmin') : ?>
                    <?php if ($status == 0) : ?>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idpermintaan; ?>">
                            Ubah Status
                        </button>
                    <?php else : ?>
                        Tanggapi
                    <?php endif; ?>
                <?php elseif ($role === 'gudang') : ?>
                    <?php if ($status == 1) : ?>
                        <?php if ($status2 == 1) : ?>
                            Ditanggapi
                        <?php else : ?>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idpermintaan; ?>">
                                Ubah Status
                            </button>
                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                                Edit
                            </button>
                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idpermintaan; ?>">
                                Delete
                            </button>
                        <?php endif; ?>
                    <?php else : ?>
                        <p>Belum Ditanggapi</p>
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                            Edit
                        </button>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idpermintaan; ?>">
                            Delete
                        </button>
                    <?php endif; ?>
                <?php elseif ($role === 'dev') : ?>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idpermintaan; ?>">
                        Ubah Status
                    </button>
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                        Edit
                    </button>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idpermintaan; ?>">
                        Delete
                    </button>
                <?php else : ?>
                    Belum Ditanggapi
                <?php endif; ?>
                <!-- Edit Modal -->
                <div class="modal fade" id="edit<?= $idpermintaan; ?>">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post" enctype="multipart/form-data" action="process_form_barangkeluar.php">
                                <div class="modal-header">
                                    <h4 class="modal-title">Ubah Barang Keluar</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <label for="gambar_base64">Bukti WO:</label>
                                    <input type="file" name="gambar_base64" class="form-control-file" accept="image/*">
                                    <br>
                                    <label for="bukti_wo">Bukti Keluar:</label>
                                    <input type="file" name="bukti_wo" class="form-control-file" accept="image/*">
                                    <br>
                                    <button type="submit" class="btn btn-warning" name="updatebarangkeluar">Upload</button>
                                    <br>
                                    <br>
                                    <?php
                                    $query_barang = "SELECT * FROM keluar WHERE idpermintaan = $idpermintaan";
                                    $result_barang = mysqli_query($conn, $query_barang);
                                    $nomor_barang = 1;
                                    while ($row_barang = mysqli_fetch_assoc($result_barang)) {
                                        $idbarang = $row_barang['idbarang'];
                                        $idkeluar = $row_barang['idkeluar'];
                                        $query_stock = "SELECT * FROM stock WHERE idbarang = $idbarang";
                                        $result_stock = mysqli_query($conn, $query_stock);
                                        $row_stock = mysqli_fetch_assoc($result_stock);
                                        $namabarang = $row_stock['namabarang'];
                                        $unit = $row_stock['unit'];
                                        $qty = $row_barang['qty'];
                                        $keterangan = $row_barang['keterangan'];
                                        $penerima = $row_barang['penerima'];
                                    ?>
                                        <div class="barang" id="barang<?= $idkeluar; ?>">
                                            <input type="hidden" name="idkeluar[]" value="<?= $idkeluar; ?>">
                                            <input type="hidden" name="idbarang[]" value="<?= $idbarang; ?>" class="form-control" id="idbarang<?= $idkeluar; ?>">
                                            <br>
                                            <label for="namabarang<?= $idkeluar; ?>">Nama Barang <?= $nomor_barang; ?>:</label>
                                            <input type="text" name="namabarang[]" value="<?= $namabarang; ?>" class="form-control" id="namabarang<?= $idkeluar; ?> " disabled>
                                            <br>
                                            <label for="unit<?= $idkeluar; ?>">Unit:</label>
                                            <input type="text" name="unit[]" value="<?= $unit; ?>" class="form-control" id="unit<?= $idkeluar; ?>" disabled>
                                            <br>
                                            <label for="qty<?= $idkeluar; ?>">Jumlah:</label>
                                            <input type="number" name="qty[]" value="<?= $qty; ?>" class="form-control" id="qtypermintaan<?= $idkeluar; ?>">
                                            <br>
                                            <label for="penerima<?= $idkeluar; ?>">Penerima:</label>
                                            <input type="text" name="penerima[]" value="<?= $penerima; ?>" class="form-control" id="ket<?= $idkeluar; ?>">
                                            <br>
                                            <label for="ket<?= $idkeluar; ?>">Keterangan:</label>
                                            <input type="text" name="ket[]" value="<?= $keterangan; ?>" class="form-control" id="ket<?= $idkeluar; ?>">
                                            <br>
                                            <button type="submit" class="btn btn-danger" name="deletebarangkeluar" value="<?= $idkeluar; ?>">Hapus</button>
                                            <button type="submit" class="btn btn-warning" name="updatebarangkeluar" value="<?= $idkeluar; ?>">Ubah</button>
                                            <hr>
                                        </div>
                                    <?php
                                        $nomor_barang++;
                                    }
                                    ?>
                                    <input type="hidden" name="id" value="<?= $idpermintaan; ?>">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModaledit<?= $idpermintaan; ?>" data-idpermintaan="<?= $idpermintaan; ?>">
                                        Tambah Barang
                                    </button>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Delete Modal -->
                <div class="modal fade" id="delete<?= $idpermintaan; ?>">
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
                                    Apakah anda yakin ingin menghapus permintaan tanggal waktu ini: <?= $tanggal; ?> ?
                                    <br>
                                    <br>
                                    <input type="hidden" name="idpermintaan" value="<?= $idpermintaan; ?>">
                                    <button type="submit" class="btn btn-danger" name="hapusbarangkeluar">Hapus</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
                <!-- Modal untuk mengubah status permintaan -->
                <div class="modal fade" id="statusModal<?= $idpermintaan; ?>" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="statusModalLabel">Ubah Status Permintaan</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="update_status.php" method="POST">
                                    <input type="hidden" name="idpermintaan" value="<?= $idpermintaan; ?>">
                                    <?php if ($role === 'superadmin') : ?>
                                        <div class="form-group">
                                            <label for="status">Status:</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="1">Disetujui</option>
                                                <option value="2">Tidak Disetujui</option>
                                            </select>
                                        </div>
                                    <?php elseif ($role === 'gudang') : ?>
                                        <div class="form-group">
                                            <label for="status2">Status Gudang:</label>
                                            <select class="form-control" id="status2" name="status2">
                                                <option value="1">Disetujui</option>
                                                <option value="2">Tidak Disetujui</option>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
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
    echo '<tr><td colspan="9">Data yang dicari tidak ada</td></tr>';
}
?>