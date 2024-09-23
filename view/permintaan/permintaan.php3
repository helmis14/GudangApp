<?php
require '../../helper/function.php';
require '../../helper/cek.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: ../../login.php');
    exit();
}

if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev' && $_SESSION['role'] !== 'supervisor' && $_SESSION['role'] !== 'user' && $_SESSION['role'] !== 'supervisoradmin' && $_SESSION['role'] !== 'gudang') {
    header('Location: ../../access_denied.php');
    exit();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query = "SELECT permintaan.idpermintaan, permintaan.tanggal, permintaan.status, permintaan.status2, 
              GROUP_CONCAT(CONCAT(barang_permintaan.namabarang, '')) as detail_barang, 
              GROUP_CONCAT(barang_permintaan.qtypermintaan) as qtypermintaan, 
              GROUP_CONCAT(barang_permintaan.unit) as unit, 
              GROUP_CONCAT(barang_permintaan.keterangan) as keterangan, 
              permintaan.bukti_base64, permintaan.status 
              FROM permintaan
              INNER JOIN barang_permintaan ON permintaan.idpermintaan = barang_permintaan.idpermintaan
              GROUP BY permintaan.idpermintaan
              ORDER BY permintaan.idpermintaan DESC
              LIMIT $limit OFFSET $offset";
$filtered_result = mysqli_query($conn, $query);
$totalDataQuery = "SELECT COUNT(DISTINCT permintaan.idpermintaan) AS total FROM permintaan 
                   INNER JOIN barang_permintaan ON permintaan.idpermintaan = barang_permintaan.idpermintaan";

$totalDataResult = mysqli_query($conn, $totalDataQuery);
$totalData = mysqli_fetch_assoc($totalDataResult)['total'];
$totalPages = ceil($totalData / $limit);

$currentRange = 2;

$startRange = max(1, $page - $currentRange);
$endRange = min($totalPages, $page + $currentRange);

$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];
$email_logged = $_SESSION['email'];

error_reporting(E_ALL);
ini_set('display_errors', 1);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <link data-n-head="ssr" rel="icon" type="image/png" sizes="16x16" href="../../assets/img/icon.png">
    <title>Permintaan Barang</title>
    <link href="../../css/styles.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/compressorjs/dist/compressor.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


</head>

<body class="sb-nav-fixed">
    <?php
    require_once '../../layout/_nav.php';
    require_once '../../layout/_sidenav.php';
    ?>
    <div class="container-fluid">
        <h1 class="mt-4">Permintaan Barang</h1>
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <!-- Button to Open the Modal "Tambah Barang"-->
                <?php if ($role === 'supervisor' || $role === 'dev' || $role === 'gudang') :  ?>
                    <div class="p-2">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                            Tambah Permintaan
                        </button>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filterpermintaan">
                            Cari Permintaan Barang
                        </button>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#permintaan">
                            Export to Excel
                        </button>
                    </div>


                <?php endif; ?>
                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#barang">
                    Cari Barang
                </button>
                <div class="p-2 ml-auto">
                    <input class="form-control" type="text" id="search-input" placeholder="Cari Barang" aria-label="Search">
                    <button class="btn btn-secondary mt-2" id="cancel-search" style="display:none;">Cancel</button>
                </div>
            </div>
            <div class="card-body">
                <?php

                if ($_SESSION['role'] == 'superadmin' || $_SESSION['role'] == 'dev') {
                    $ambildatastock = mysqli_query($conn, "SELECT * FROM permintaan WHERE status = 0");
                    $count_pending = mysqli_num_rows($ambildatastock);
                    if ($count_pending > 0) {
                ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong>Perhatian!</strong> Mohon tanggapi permintaan yang masih status pending.
                        </div>
                <?php
                    }
                }
                ?>

                <!-- Displaying the results -->
                <?php if (mysqli_num_rows($filtered_result) > 0) { ?>
                    <div class="table-responsive">
                        <table class="table text-center table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <div id="loading" style="display:none;text-align:center;">
                                        <p> <img src="../../assets/gif/loading.gif" alt="Loading..." /></p>
                                    </div>
                                    <th>No </th>
                                    <th>Tanggal</th>
                                    <th>Nama barang</th>
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Keterangan</th>
                                    <th>Bukti</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_rows = mysqli_num_rows($filtered_result);
                                $no = $total_rows;
                                while ($row = mysqli_fetch_assoc($filtered_result)) {
                                    $idpermintaan = $row['idpermintaan'];
                                    $tanggal = $row['tanggal'];
                                    $status_permintaan = $row['status'];
                                    $status_permintaan2 = $row['status2'];
                                ?>
                                    <tr>
                                        <td><?= ($total_rows - $no + 1); ?></td>
                                        <!-- <td><?= $no--; ?></td> -->
                                        <td><?= $tanggal; ?></td>
                                        <td>
                                            <?php
                                            $detail_barang = explode(",", $row['detail_barang']);
                                            foreach ($detail_barang as $key => $barang) {
                                                echo ($key + 1) . ". " . $barang . "<br>";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $unit = explode(",", $row['unit']);
                                            foreach ($unit as $barang_unit) {
                                                echo $barang_unit . "<br>";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $qtypermintaan = explode(",", $row['qtypermintaan']);
                                            foreach ($qtypermintaan as $qty) {
                                                echo $qty . "<br>";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $keterangan = explode(",", $row['keterangan']);
                                            foreach ($keterangan as $ket) {
                                                echo $ket . "<br>";
                                            }
                                            ?>
                                        </td>
                                        <!-- Modal untuk menampilkan gambar penuh -->
                                        <div class="modal fade" id="gambarModal<?= $idpermintaan; ?>" tabindex="-1" role="dialog" aria-labelledby="gambarModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="gambarModalLabel">Gambar Permintaan</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <img src="data:image/jpeg;base64,<?= $row['bukti_base64']; ?>" class="img-fluid">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="download_gambar_permintaan.php?id=<?= $idpermintaan; ?>" class="btn btn-primary" download>Download</a>
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <td>
                                            <button type="button" class="btn btn-success gambar-modal-trigger" data-idpermintaan="<?= $idpermintaan; ?>" data-toggle="modal" data-target="#gambarModal<?= $idpermintaan; ?>">
                                                Lihat Bukti
                                            </button>

                                        </td>
                                        <td>
                                            <?php
                                            $allApproved = ($status_permintaan == 1 && $status_permintaan2 == 1);
                                            $allRejected = ($status_permintaan == 2 && $status_permintaan2 == 2);
                                            $anyResponded = ($status_permintaan != 0 || $status_permintaan2 != 0);
                                            ?>
                                            <?php if ($allApproved) : ?>
                                                Disetujui
                                            <?php elseif ($allRejected) : ?>
                                                Ditolak
                                            <?php else : ?>
                                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#viewStatusModal<?= $idpermintaan; ?>">
                                                    Lihat Status
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($_SESSION['role'] === 'supervisor' && $status_permintaan == 0 && $status_permintaan2 == 0) { ?>
                                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                                                    Edit
                                                </button>
                                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal<?= $idpermintaan; ?>">
                                                    Delete
                                                </button>
                                            <?php } elseif ($_SESSION['role'] === 'superadmin' && ($status_permintaan == 0 || $status_permintaan2 == 0)) { ?>
                                                <?php if ($_SESSION['email'] === 'bm@plazaoleos.com' && $status_permintaan == 0) { ?>
                                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idpermintaan; ?>">
                                                        Ubah Status
                                                    </button>
                                                <?php } elseif ($_SESSION['email'] === 'ce@plazaoleos.com' && $status_permintaan2 == 0) { ?>
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
                                            <?php } elseif ($status_permintaan == 1 && $status_permintaan2 == 1) { ?>
                                                Ditanggapi
                                            <?php } else { ?>
                                                Belum Ditanggapi
                                            <?php } ?>
                                        </td>
                                    </tr>

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
                                                        <span class="<?= ($status_permintaan == 0) ? 'text-warning' : ($status_permintaan == 1 ? 'text-success' : 'text-danger'); ?>">
                                                            <?= ($status_permintaan == 0) ? 'belum menanggapi' : ($status_permintaan == 1 ? 'menyetujui' : 'tidak menyetujui'); ?>
                                                        </span>
                                                    </p>
                                                    <hr>
                                                    <p>ce@plazaoleos.com
                                                        <span class="<?= ($status_permintaan2 == 0) ? 'text-warning' : ($status_permintaan2 == 1 ? 'text-success' : 'text-danger'); ?>">
                                                            <?= ($status_permintaan2 == 0) ? 'belum menanggapi' : ($status_permintaan2 == 1 ? 'menyetujui' : 'tidak menyetujui'); ?>
                                                        </span>
                                                    </p>
                                                    <hr>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                                            <label for="search-barang">Cari Nama Barang:</label>
                                                            <select name="namabarang" class="form-control" required>
                                                                <?php
                                                                $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM stock");
                                                                while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                                                                    $namabarangnya = $fetcharray['namabarang'];
                                                                ?>
                                                                    <option value="<?= $namabarangnya; ?>"><?= $namabarangnya; ?></option>
                                                                <?php
                                                                }
                                                                ?>
                                                            </select>
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
                                                            <select name="status" class="form-control" disabled>
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
                                <?php
                                };

                                ?>
                            </tbody>
                        </table>
                        <div style="text-align:center" id="loadingSpinner" style="display: none;">
                            <img src="../../assets/gif/loading.gif" alt="Loading..." />
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <!-- Tombol First -->
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=1" aria-label="First">
                                        <span aria-hidden="true">« Awal</span>
                                    </a>
                                </li>

                                <!-- Tombol Previous -->
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?= max(1, $page - 1); ?>" aria-label="Previous">
                                        <span aria-hidden="true">‹ Sebelumnya</span>
                                    </a>
                                </li>

                                <!-- Halaman yang ditampilkan dalam rentang -->
                                <?php for ($i = $startRange; $i <= $endRange; $i++): ?>
                                    <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Tombol Next -->
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?= min($totalPages, $page + 1); ?>" aria-label="Next">
                                        <span aria-hidden="true">Selanjutnya ›</span>
                                    </a>
                                </li>

                                <!-- Tombol Last -->
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?= $totalPages; ?>" aria-label="Last">
                                        <span aria-hidden="true">Terakhir »</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>

                    </div>
                <?php
                } else {
                    echo "Tidak ada hasil yang ditemukan.";
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    require_once '../../layout/_footer.php';
    require_once '../../component/modalLogout.php';
    ?>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../../js/scripts.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            function bindEditButtons() {
                $('.btn-warning').off('click').on('click', function() {
                    var target = $(this).data('target');
                    $(target).modal('show');
                });
            }

            $('#search-input').on('input', function() {
                $('#loading').show();

                var search = $(this).val();

                $.ajax({
                    url: "search_datapermintaan.php",
                    type: 'GET',
                    data: {
                        search: search
                    },
                    success: function(data) {
                        $('#loading').hide();
                        $('#dataTable tbody').html(data);
                        bindEditButtons();
                    },
                    error: function() {
                        $('#loading').hide();
                        alert('Pencarian gagal');
                    }

                });
            });
            bindEditButtons();
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var paginationLinks = document.querySelectorAll('.pagination .page-link');

            paginationLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    document.getElementById('loadingSpinner').style.display = 'block';
                });
            });
        });
    </script>
    <script>
        window.addEventListener('load', function() {
            document.getElementById('loadingSpinner').style.display = 'none';
        });
    </script>

    <script>
        $(document).ready(function() {
            var counter = 2; // Mulai dari nomor 2
            // Hapus tombol hapus barang saat formulir utama dimuat
            $("#hapusBarangBtn").hide();

            // Tambahkan barang baru
            $("#addBarangBtn").click(function() {
                var newBarang = `
                <div id="barang${counter}">
                    <label for="search-barang${counter}">Cari Nama Barang:</label>
                    <select name="namabarang[]" id="search-barang${counter}" class="form-control select-plugin" required>
                        <?php
                        $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM stock");
                        while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                            $namabarangnya = $fetcharray['namabarang'];
                        ?>
                            <option value="<?= $namabarangnya; ?>"><?= $namabarangnya; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <br>
                    <br>
                    <label for="unit${counter}">Unit:</label>
                    <select name="unit[]" class="form-control">
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
                    <label for="qtypermintaan${counter}">Jumlah:</label>
                    <input type="Number" name="qtypermintaan[]" placeholder="Quantity" class="form-control" required>
                    <br>
                    <label for="keterangan${counter}">Keterangan:</label>
                    <input type="text" name="keterangan[]" placeholder="Keterangan" class="form-control" required>
                    <br>

                    <label for="status${counter}" style="display: none;">Status:</label>
                    <select name="status[]" class="form-control" style="display: none;">
                        <option value="0">Pending</option>
                        <option value="1" disabled>Disetujui</option>
                        <option value="2" disabled>Tidak Disetujui</option>
                    </select>
                    <hr>
                </div>
            `;
                $("#barangContainer").append(newBarang);
                $('.select-plugin').select2();
                $("#hapusBarangBtn").show();
                counter++;
            });

            // Hapus barang baru
            $("#hapusBarangBtn").click(function() {
                if (counter > 2) {
                    counter--;
                    $("#barangContainer #barang" + counter).remove();
                }
                // Sembunyikan tombol hapus barang jika tidak ada barang lagi
                if (counter === 1) {
                    $("#hapusBarangBtn").hide();
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $("#myModaledit").on('show.bs.modal', function(e) {
                var idpermintaan = $(e.relatedTarget).data('idpermintaan');
                $("#idpermintaanInput").val(idpermintaan);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.gambar-modal-trigger').click(function() {
                var idPermintaan = $(this).data('idpermintaan');
                $('#gambarModal' + idPermintaan).modal('show');
            });
        });
    </script>
</body>

<!-- The Modal "Tambah Permintaan"-->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Tambah Permintaan</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Formulir utama -->
                    <label for="bukti_base64">Bukti Permintaan:</label>
                    <input type="file" name="bukti_base64" class="form-control-file" required>
                    <p style="font-size: small; padding-top: 7px">Ukuran bukti maksimal 5 mb </p>
                    <label for="search-barang">Cari Nama Barang:</label>
                    <select name="namabarang[]" class="form-control" required>
                        <?php
                        $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM stock");
                        while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                            $namabarangnya = $fetcharray['namabarang'];
                        ?>
                            <option value="<?= $namabarangnya; ?>"><?= $namabarangnya; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <br>
                    <br>
                    <label for="unit[]">Unit:</label>
                    <select name="unit[]" class="form-control">
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
                    <label for="qtypermintaan[]">Jumlah:</label>
                    <input type="Number" name="qtypermintaan[]" placeholder="Quantity" class="form-control" required>
                    <br>
                    <label for="keterangan[]">Keterangan:</label>
                    <input type="text" name="keterangan[]" placeholder="Keterangan" class="form-control" required>
                    <br>

                    <label for="status[]" style="display: none;">Status:</label>
                    <select name="status[]" class="form-control" style="display: none;">
                        <option value="0">Pending</option>
                        <option value="1" disabled>Disetujui</option>
                        <option value="2" disabled>Tidak Disetujui</option>
                    </select>

                    <hr>
                    <!-- Tempat untuk menambahkan barang-barang baru -->
                    <div id="barangContainer">
                        <!-- Ini adalah tempat untuk menambahkan detail barang -->
                    </div>
                    <!-- Tombol untuk menambahkan barang baru -->
                    <button type="button" class="btn btn-success" id="addBarangBtn">Tambah Barang</button>
                    <!-- Tombol untuk menghapus barang baru -->
                    <button type="button" class="btn btn-danger" id="hapusBarangBtn">Hapus Barang</button>
                    <!-- Tombol untuk mengirim -->
                    <button type="submit" class="btn btn-primary" name="addnewpermintaan">Submit</button>
                </div>
            </form>
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<!-- The Modal "Tambah barang dengan id yang sama"-->
<div class="modal fade" id="tes<?= $idpermintaan; ?>">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Tambah Barang <?= $tanggal; ?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="post" enctype="multipart/form-data" action="process_form.php">
                <div class="modal-body">
                    <label for="search-barang">Cari Nama Barang:</label>
                    <select name="namabarang" class="form-control" required>
                        <?php
                        $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM stock");
                        while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                            $namabarangnya = $fetcharray['namabarang'];
                        ?>
                            <option value="<?= $namabarangnya; ?>"><?= $namabarangnya; ?></option>
                        <?php
                        }
                        ?>
                    </select>
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
                    <input type="hidden" name="idpermintaan" value="<?= $idpermintaan; ?>">
                    <br>
                    <div id="barangContainer">
                    </div>
                    <button type="submit" class="btn btn-primary" name="barangbaru">Submit</button>
                </div>
            </form>
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<!-- The Modal "Filter Barang "-->
<div class="modal fade" id="filterpermintaan">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Filter Permintaan Barang</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <i><b>*Perhatikan tanggal yang akan di filter</b></i>
                <br><br>
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="tgl_mulai" class="form-control">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="tgl_selesai" class="form-control">
                <br>
                <button type="button" id="filterBtn" class="btn btn-info">Filter</button>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#filterBtn').click(function() {
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();

            if (start_date && end_date) {
                $.ajax({
                    url: 'filter_permintaan.php',
                    type: 'GET',
                    data: {
                        'start_date': start_date,
                        'end_date': end_date
                    },
                    success: function(response) {
                        $('table tbody').html(response);
                        $('#filterpermintaan').modal('hide'); // Close the modal
                    },
                    error: function(xhr, status, error) {
                        console.error("Terjadi kesalahan: " + error);
                    }
                });
            } else {
                alert('Silakan pilih kedua tanggal untuk melakukan filter.');
            }
        });
    });
</script>

<!-- The Modal "Export"-->
<div class="modal fade" id="permintaan">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Export Data Permintaan Barang</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    Apakah Anda Yakin Ingin Mengexport Data Permintaan Barang
                    <br>
                    <br>
                    <button type="submit" class="btn btn-outline-success" name="export_permintaan">Export to Excel</button>
                </div>
            </form>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>





<!-- The Modal Search -->
<div class="modal fade" id="barang">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <form id="filterForm">
                    <div class="form-row">
                        <div class="col">
                            <label for="tgl_mulai">Start Date:</label>
                            <input type="date" name="tgl_mulai" class="form-control" id="tgl_mulai">
                        </div>
                        <div class="col">
                            <label for="tgl_selesai">End Date:</label>
                            <input type="date" name="tgl_selesai" class="form-control" id="tgl_selesai">
                        </div>
                        <div class="col">
                            <label for="nama_barang">Nama Barang:</label>
                            <input type="text" name="nama_barang" class="form-control" id="nama_barang">
                        </div>
                        <div class="col">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-info btn-block">Filter</button>
                        </div>
                    </div>
                </form>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
        </div>

        <!-- Filter Form -->

        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center" id="dataTableSearch" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Barang</th>
                                <th>Unit</th>
                                <th>Quantity</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="dataTableBody">
                            <!-- Data akan dimuat di sini menggunakan AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    $(document).ready(function() {
        $('#dataTableSearch').DataTable();
    });
</script>


<script>
    $(document).ready(function() {
        function loadData(tgl_mulai = '', tgl_selesai = '', nama_barang = '') {
            $.ajax({
                url: 'filter_barang.php',
                method: 'POST',
                data: {
                    tgl_mulai: tgl_mulai,
                    tgl_selesai: tgl_selesai,
                    nama_barang: nama_barang
                },
                success: function(response) {
                    $('#dataTableBody').html(response);
                    $('#dataTableSearch').DataTable();
                }
            });
        }

        // Load data from localStorage if exists
        var savedFilter = JSON.parse(localStorage.getItem('filterData'));
        if (savedFilter) {
            $('#tgl_mulai').val(savedFilter.tgl_mulai);
            $('#tgl_selesai').val(savedFilter.tgl_selesai);
            $('#nama_barang').val(savedFilter.nama_barang);
            loadData(savedFilter.tgl_mulai, savedFilter.tgl_selesai, savedFilter.nama_barang);
        } else {
            loadData();
        }

        // Filter data on form submission
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();

            var tgl_mulai = $('#tgl_mulai').val();
            var tgl_selesai = $('#tgl_selesai').val();
            var nama_barang = $('#nama_barang').val();


            // Save filter data to localStorage
            var filterData = {
                tgl_mulai: tgl_mulai,
                tgl_selesai: tgl_selesai,
                nama_barang: nama_barang
            };
            localStorage.setItem('filterData', JSON.stringify(filterData));

            loadData(tgl_mulai, tgl_selesai, nama_barang);
        });

        // Clear filter data on modal close
        $('#barang').on('hidden.bs.modal', function() {
            localStorage.removeItem('filterData');
        });

        // Clear filter data on close button click
        $('.close').on('click', function() {
            localStorage.removeItem('filterData');
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('select[name="namabarang[]"]').select2({
            placeholder: 'Cari Nama Barang',
            allowClear: true
        });
    });
</script>



</html>