<?php
require '../../helper/function.php';
require '../../helper/cek.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: ../../login.php');
    exit();
}

<<<<<<< HEAD
if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev' && $_SESSION['role'] !== 'supervisor' && $_SESSION['role'] !== 'user' && $_SESSION['role'] !== 'supervisoradmin' && $_SESSION['role'] !== 'gudang') {
=======
if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev' && $_SESSION['role'] !== 'supervisor' && $_SESSION['role'] !== 'user'&& $_SESSION['role'] !== 'supervisoradmin'&& $_SESSION['role'] !== 'supervisorgudang') {
>>>>>>> 55098ea6017122debef3b3aefb221eb3590a4976
    header('Location: ../../access_denied.php');
    exit();
}
$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];

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


</head>

<body class="sb-nav-fixed">
    <?php
    require_once '../../layout/_nav.php';
    require_once '../../layout/_sidenav.php';
    ?>
    <div class="container-fluid">
        <h1 class="mt-4">Permintaan Barang</h1>
        <div class="card mb-4">
            <!-- Button to Open the Modal "Tambah Barang"-->
<<<<<<< HEAD
            <?php if ($role === 'supervisor' || $role === 'dev' || $role === 'gudang') :  ?>
=======
            <?php if ($role === 'supervisor' || $role === 'dev'|| $role === 'supervisorgudang') :  ?>
>>>>>>> 55098ea6017122debef3b3aefb221eb3590a4976
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                        Tambah Permintaan
                    </button>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filterpermintaan">
                        Date
                    </button>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#permintaan">
                        Export to Excel
                    </button>
                </div>
            <?php endif; ?>

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
                if (isset($_POST['filter_permintaan'])) {
                    $tgl_mulai = $_POST['tgl_mulai'];
                    $tgl_selesai = $_POST['tgl_selesai'];

                    // Query dengan filter tanggal
                    $query = "SELECT permintaan.idpermintaan, permintaan.tanggal, permintaan.status, GROUP_CONCAT(CONCAT(barang_permintaan.namabarang, '')) as detail_barang, GROUP_CONCAT(barang_permintaan.qtypermintaan) as qtypermintaan, GROUP_CONCAT(barang_permintaan.unit) as unit, GROUP_CONCAT(barang_permintaan.keterangan) as keterangan, permintaan.bukti_base64, permintaan.status 
                                        FROM permintaan
                                        INNER JOIN barang_permintaan ON permintaan.idpermintaan = barang_permintaan.idpermintaan
                                        WHERE permintaan.tanggal BETWEEN ? AND ?
                                        GROUP BY permintaan.idpermintaan
                                        ORDER BY permintaan.idpermintaan DESC";

                    // Prepare statement
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ss", $tgl_mulai, $tgl_selesai);
                    mysqli_stmt_execute($stmt);

                    $filtered_result = mysqli_stmt_get_result($stmt);
                } else {
                    // Query tanpa filter tanggal
                    $query = "SELECT permintaan.idpermintaan, permintaan.tanggal, permintaan.status, GROUP_CONCAT(CONCAT(barang_permintaan.namabarang, '')) as detail_barang, GROUP_CONCAT(barang_permintaan.qtypermintaan) as qtypermintaan, GROUP_CONCAT(barang_permintaan.unit) as unit, GROUP_CONCAT(barang_permintaan.keterangan) as keterangan, permintaan.bukti_base64, permintaan.status 
                                        FROM permintaan
                                        INNER JOIN barang_permintaan ON permintaan.idpermintaan = barang_permintaan.idpermintaan";

                    // If no date range is provided, display all data
                    if (!isset($_POST['tgl_mulai']) || !isset($_POST['tgl_selesai'])) {
                        $query .= " GROUP BY permintaan.idpermintaan";
                    } else {
                        $query .= " WHERE permintaan.tanggal BETWEEN ? AND ? GROUP BY permintaan.idpermintaan";
                    }

                    $filtered_result = mysqli_query($conn, $query);
                }
                ?>

                <!-- Displaying the results -->
                <?php if (mysqli_num_rows($filtered_result) > 0) { ?>
                    <div class="table-responsive">
                        <table class="table text-center table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
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
                                ?>
                                    <tr>
                                        <td><?= ($total_rows - $no + 1); ?></td>
                                        <!--<td><?= $no--; ?></td>-->
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
                                                echo  " " . $barang_unit . "<br>";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $qtypermintaan = explode(",", $row['qtypermintaan']);
                                            foreach ($qtypermintaan as $qty) {
                                                echo  " " . $qty . "<br>";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $keterangan = explode(",", $row['keterangan']);
                                            foreach ($keterangan as $ket) {
                                                echo  " " . $ket . "<br>";
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
                                            <a href="#" class="gambar-modal-trigger" data-idpermintaan="<?= $idpermintaan; ?>">
                                                <img src="data:image/jpeg;base64,<?= $row['bukti_base64']; ?>" alt="Bukti Permintaan" style="max-width: 100px; max-height: 100px;">
                                            </a>
                                        </td>
                                        <td><?= ($status_permintaan == 0) ? 'Pending' : ($status_permintaan == 1 ? 'Disetujui' : 'Tidak Disetujui'); ?></td>
                                        <td>
                                            <?php if ($_SESSION['role'] === 'supervisor' && $status_permintaan == 0) { ?>
                                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                                                    Edit
                                                </button>
                                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deletModal<?= $idpermintaan; ?>">
                                                    Delete
                                                </button>
<<<<<<< HEAD
                                            <?php } elseif ($_SESSION['role'] === 'superadmin' && $status_permintaan == 0) { ?>
=======

                                            <?php } elseif ($_SESSION['role'] === 'superadmin' || ($_SESSION['role'] === 'supervisoradmin'  && $status_permintaan == 0)) { ?>

>>>>>>> 55098ea6017122debef3b3aefb221eb3590a4976
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idpermintaan; ?>">
                                                    Ubah Status
                                                </button>
                                            <?php } elseif ($_SESSION['role'] === 'dev') { ?>
                                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                                                    Edit
                                                </button>
                                                <br>
                                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deletModal<?= $idpermintaan; ?>">
                                                    Delete
                                                </button>
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idpermintaan; ?>">
                                                    Ubah Status
                                                </button>
                                            <?php } elseif ($status_permintaan == 1 || $status_permintaan == 2) { ?>
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
                                                        <div class="form-group">
                                                            <label for="status">Status:</label>
                                                            <select class="form-control" id="status" name="status">
                                                                <option value="1">Disetujui</option>
                                                                <option value="2">Tidak Disetujui</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                    </form>
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
                                <?php
                                };

                                ?>
                            </tbody>
                        </table>
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
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../../js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/demo/chart-area-demo.js"></script>
    <script src="../../assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/demo/datatables-demo.js"></script>

    <script>
        $(document).ready(function() {
            var counter = 2; // Mulai dari nomor 2
            // Hapus tombol hapus barang saat formulir utama dimuat
            $("#hapusBarangBtn").hide();

            // Tambahkan barang baru
            $("#addBarangBtn").click(function() {
                var newBarang = `
                <div id="barang${counter}">
                    <label for="namabarang${counter}">Nama Barang ${counter}:</label>
                    <input type="text" name="namabarang[]" placeholder="Nama Barang" class="form-control" required>
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

                    <label for="namabarang[]">Nama Barang:</label>
                    <input type="text" name="namabarang[]" placeholder="Nama Barang" class="form-control" required>
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
                    <label for="namabarang">Nama Barang: </label>
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

<!-- The Modal "Filter Permintaan"-->
<div class="modal fade" id="filterpermintaan">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Filter Permintaan Barang</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data" action="permintaan.php">
                <div class="modal-body">
                    <i><b>*Perhatikan tanggal yang akan di filter</b></i>
                    <br>
                    <br>
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="tgl_mulai" class="form-control">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="tgl_selesai" class="form-control">
                    <br>
                    <button type="submit" name="filter_permintaan" class="btn btn-info">Filter</button>
                </div>
            </form>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

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

</html>