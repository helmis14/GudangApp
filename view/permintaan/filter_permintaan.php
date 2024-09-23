<?php
require '../../helper/function.php';
require '../../helper/cek.php';

$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];
$search = isset($_GET['search']) ? $_GET['search'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$query = "SELECT permintaan.idpermintaan, permintaan.tanggal, 
            GROUP_CONCAT(CONCAT(barang_permintaan.namabarang, '')) as detail_barang, 
            GROUP_CONCAT(barang_permintaan.qtypermintaan) as qtypermintaan, 
            GROUP_CONCAT(barang_permintaan.unit) as unit, 
            GROUP_CONCAT(barang_permintaan.keterangan) as keterangan, 
            permintaan.bukti_base64, permintaan.status, permintaan.status2
          FROM permintaan
          INNER JOIN barang_permintaan ON permintaan.idpermintaan = barang_permintaan.idpermintaan";

$filters = [];

if (!empty($start_date) && !empty($end_date)) {
    $filters[] = "permintaan.tanggal BETWEEN '$start_date' AND '$end_date'";
}

if ($search !== '') {
    $filters[] = " (barang_permintaan.namabarang LIKE '%$search%' 
                 OR permintaan.idpermintaan LIKE '%$search%')";
}

if (count($filters) > 0) {
    $query .= " WHERE " . implode(' AND ', $filters);
}

$query .= " GROUP BY permintaan.idpermintaan ORDER BY permintaan.idpermintaan DESC LIMIT 100";
$ambilsemuadatapermintaan = mysqli_query($conn, $query);

if (mysqli_num_rows($ambilsemuadatapermintaan) > 0) {
    $no = 1;
    while ($data = mysqli_fetch_array($ambilsemuadatapermintaan)) {
        $idpermintaan = $data['idpermintaan'];
        $tanggal = $data['tanggal'];
        $detail_barang = $data['detail_barang'];
        $qtypermintaan = $data['qtypermintaan'];
        $unit = $data['unit'];
        $keterangan = $data['keterangan'];
        $bukti_base64 = $data['bukti_base64'];
        $status = $data['status'];
        $status2 = $data['status2'];
?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $tanggal; ?></td>
            <td>
                <?php
                $detail_barang_arr = explode(",", $detail_barang);
                foreach ($detail_barang_arr as $key => $barang) {
                    echo ($key + 1) . ". " . $barang . "<br>";
                }
                ?>
            </td>
            <td>
                <?php
                $unit_arr = explode(",", $unit);
                foreach ($unit_arr as $barang_unit) {
                    echo $barang_unit . "<br>";
                }
                ?>
            </td>
            <td>
                <?php
                $qtypermintaan_arr = explode(",", $qtypermintaan);
                foreach ($qtypermintaan_arr as $qty) {
                    echo $qty . "<br>";
                }
                ?>
            </td>
            <td>
                <?php
                $keterangan_arr = explode(",", $keterangan);
                foreach ($keterangan_arr as $ket) {
                    echo $ket . "<br>";
                }
                ?>
            </td>
            <td>
                <button type="button" class="btn btn-success gambar-modal-trigger" data-idpermintaan="<?= $idpermintaan; ?>" data-toggle="modal" data-target="#gambarModal<?= $idpermintaan; ?>">
                    Lihat Bukti
                </button>
            </td>
            <td>
                <?php
                $allApproved = ($status == 1 && $status2 == 1);
                $allRejected = ($status == 2 && $status2 == 2);
                $anyResponded = ($status != 0 || $status2 != 0);
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
                                    <p>bm@plazaoleos.com
                                        <span class="<?= ($status == 0) ? 'text-warning' : ($status == 1 ? 'text-success' : 'text-danger'); ?>">
                                            <?= ($status == 0) ? 'belum menanggapi' : ($status == 1 ? 'menyetujui' : 'tidak menyetujui'); ?>
                                        </span>
                                    </p>
                                    <hr>
                                    <p>ce@plazaoleos.com
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
                <?php if ($_SESSION['role'] === 'supervisor' && $status == 0 && $status2 == 0) { ?>
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                        Edit
                    </button>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal<?= $idpermintaan; ?>">
                        Delete
                    </button>
                <?php } elseif ($_SESSION['role'] === 'superadmin' && ($status == 0 || $status2 == 0)) { ?>
                    <?php if ($_SESSION['email'] === 'bm@plazaoleos.com' && $status == 0) { ?>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idpermintaan; ?>">
                            Ubah Status
                        </button>
                    <?php } elseif ($_SESSION['email'] === 'ce@plazaoleos.com' && $status2 == 0) { ?>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idpermintaan; ?>">
                            Ubah Status
                        </button>
                    <?php } else { ?>
                        Ditanggapi
                    <?php } ?>
                <?php } elseif ($_SESSION['role'] === 'dev') { ?>
                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                        Edit
                    </button>
                    <br>
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal<?= $idpermintaan; ?>">
                        Delete
                    </button>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idpermintaan; ?>">
                        Ubah Status
                    </button>
                <?php } elseif ($status == 1 && $status2 == 1) { ?>
                    Ditanggapi
                <?php } else { ?>
                    Belum Ditanggapi
                <?php } ?>
                <!-- Edit Modal -->
                <div class="modal fade" id="edit<?= $idpermintaan; ?>">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="post" enctype="multipart/form-data" action="process_form.php">
                                <div class="modal-header">
                                    <h4 class="modal-title">Ubah Permintaan</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <label for="update_permintaan">Bukti:</label>
                                    <input type="file" name="update_permintaan" class="form-control-file" accept="image/*">
                                    <br>
                                    <button type="submit" class="btn btn-warning" name="updatepermintaan">Ubah Bukti</button>
                                    <br>
                                    <br>
                                    <?php
                                    $query_barang = "SELECT * FROM barang_permintaan WHERE idpermintaan = $idpermintaan";
                                    $result_barang = mysqli_query($conn, $query_barang);
                                    $nomor_barang = 1;
                                    while ($row_barang = mysqli_fetch_assoc($result_barang)) {
                                        $idbarang = $row_barang['idbarang'];
                                        $namabarang = $row_barang['namabarang'];
                                        $unit = $row_barang['unit'];
                                        $qtypermintaan = $row_barang['qtypermintaan'];
                                        $keterangan = $row_barang['keterangan'];
                                    ?>


                                        <div class="barang" id="barang<?= $idbarang; ?>">
                                            <input type="hidden" name="idbarang[]" value="<?= $idbarang; ?>" class="form-control" id="idbarang<?= $idbarang; ?>">
                                            <br>
                                            <label for="namabarang<?= $idbarang; ?>">Nama Barang <?= $nomor_barang; ?>:</label>
                                            <input type="text" name="namabarang[]" value="<?= $namabarang; ?>" class="form-control" id="namabarang<?= $idbarang; ?>">
                                            <br>
                                            <label for="unit<?= $idbarang; ?>">Unit:</label>
                                            <input type="text" name="unit[]" value="<?= $unit; ?>" class="form-control" id="unit<?= $idbarang; ?>">
                                            <br>
                                            <label for="qtypermintaan<?= $idbarang; ?>">Jumlah:</label>
                                            <input type="number" name="qtypermintaan[]" value="<?= $qtypermintaan; ?>" class="form-control" id="qtypermintaan<?= $idbarang; ?>">
                                            <br>
                                            <label for="ket<?= $idbarang; ?>">Keterangan:</label>
                                            <input type="text" name="ket[]" value="<?= $keterangan; ?>" class="form-control" id="ket<?= $idbarang; ?>">
                                            <br>
                                            <button type="submit" class="btn btn-danger" name="deletebarang" value="<?= $idbarang; ?>">Hapus</button>
                                            <button type="submit" class="btn btn-warning" name="updatebarangpermin" value="<?= $idbarang; ?>">Ubah</button>
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

                <!-- Modal tambah barang permintaan edit -->
                <div class="modal fade" id="myModaledit<?= $idpermintaan; ?>" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="statusModalLabel">Ubah Status Permintaan</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form action="process_form.php" method="POST">
                                    <input type="hidden" name="idpermintaan" value="<?= $idpermintaan; ?>">
                                    <div class="form-group">
                                        <!-- Formulir utama -->
                                        <label for="namabarang">Nama Barang:</label>
                                        <input type="text" name="namabarang" placeholder="Nama Barang" class="form-control" required>
                                        <br>
                                        <label for="unit">Unit:</label>
                                        <select name="unit" class="form-control">
                                            <option value="PCS">PCS</option>
                                            <option value="PACK">PACK</option>
                                            <option value="KG">KG</option>
                                            <option value="BALL">BALL</option>
                                            <option value="BTG">BTG</option>
                                            <option value="ROLL">ROLL</option>
                                            <option value="METER">METER</option>
                                            <option value="BOTOL">BOTOL</option>
                                            <option value="LITER">LITER</option>
                                            <option value="PAIL">PAIL</option>
                                            <option value="GALON">GALON</option>
                                            <option value="CAN">CAN</option>
                                            <option value="UNIT">UNIT</option>
                                            <option value="TAB">TAB</option>
                                            <option value="SET">SET</option>
                                            <option value="DUS">DUS</option>
                                            <option value="SAK">SAK</option>
                                            <option value="SLABE">SLABE</option>
                                            <option value="ALUR">ALUR</option>
                                        </select>
                                        <br>
                                        <label for="qtypermintaan">Jumlah:</label>
                                        <input type="Number" name="qtypermintaan" placeholder="Quantity" class="form-control" required>
                                        <br>
                                        <label for="keterangan">Keterangan:</label>
                                        <input type="text" name="keterangan" placeholder="Keterangan" class="form-control" required>
                                        <br>

                                        <label for="status">Status:</label>
                                        <select name="status" class="form-control">
                                            <option value="0">Pending</option>
                                            <option value="1" disabled>Disetujui</option>
                                            <option value="2" disabled>Tidak Disetujui</option>
                                        </select>
                                        <hr>
                                        <br>
                                        <div>
                                        </div>
                                        <button type="submit" class="btn btn-primary" name="barangbaru">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Delete Modal -->
                <div class="modal fade" id="deletModal<?= $idpermintaan; ?>">
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
                                    <button type="submit" class="btn btn-danger" name="hapuspermintaan">Hapus</button>
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
                                    <?php if ($_SESSION['role'] === 'superadmin' && $_SESSION['email'] === 'bm@plazaoleos.com') : ?>
                                        <div class="form-group">
                                            <label for="status">Status Superadmin 1:</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="1">Disetujui</option>
                                                <option value="2">Tidak Disetujui</option>
                                            </select>
                                        </div>
                                    <?php elseif ($_SESSION['role'] === 'superadmin' && $_SESSION['email'] === 'ce@plazaoleos.com') : ?>
                                        <div class="form-group">
                                            <label for="status2">Status Superadmin 2:</label>
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