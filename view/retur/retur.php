<?php
require '../../helper/function.php';
require '../../helper/cek.php';


if (!isset($_SESSION['iduser'])) {
    header('Location: ../../login.php');
    exit();
}

if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev'  && $_SESSION['role'] !== 'gudang' && $_SESSION['role'] !== 'user') {
    header('Location: ../../access_denied.php');
    exit();
}

$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];
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
    <title>Retur Barang</title>
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
        <h1 class="mt-4">Retur Barang </h1>
        <div class="card mb-4">
            <div class="card-header">
                <!-- Button to Open the Modal -->
                <?php if ($role === 'gudang' || $role === 'dev') :  ?>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                        Tambah Barang
                    </button>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filter">
                        Date
                    </button>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#retur">
                        Export to Excel
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>No Permintaan</th>
                                <th>Nama Barang</th>
                                <th>Unit</th>
                                <th>Jumlah</th>
                                <th>Keterangan</th>
                                <th>Bukti Retur</th>
                                <?php if ($role === 'gudang' || $role === 'dev') :  ?>
                                    <th>Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // tampilkan data barang retur berdasarkan filter
                            if (isset($_POST['filter_retur'])) {
                                $mulai = $_POST['tgl_mulai'];
                                $selesai = $_POST['tgl_selesai'];

                                if ($mulai != null || $selesai != null) {
                                    $ambilsemuadatastock = mysqli_query($conn, "select * from retur r, stock s where s.idbarang = r.idbarang 
                                        and tanggal BETWEEN '$mulai' and DATE_ADD('$selesai',INTERVAL 1 DAY) order by idretur DESC");
                                } else {
                                    $ambilsemuadatastock = mysqli_query($conn, "select * from retur r, stock s where s.idbarang = r.idbarang");
                                }
                            } else {
                                $ambilsemuadatastock = mysqli_query($conn, "select * from retur r, stock s where s.idbarang = r.idbarang");
                            }

                            // Tampilkan data barang keluar
                            while ($data = mysqli_fetch_array($ambilsemuadatastock)) {
                                $no = $data['no'];
                                $idretur = $data['idretur'];
                                $idb = $data['idbarang'];
                                $tanggal = $data['tanggal'];
                                $namabarang = $data['namabarang'];
                                $qty = $data['qtyretur'];
                                $unit = $data['unit'];
                                $keterangan = $data['keterangan'];
                                $gambar_base64 = $data['gambar_base64'];

                            ?>

                                <tr>
                                    <td><?= $no; ?></td>
                                    <td><?= $tanggal; ?></td>
                                    <td><?= $idretur; ?></td>
                                    <td><?= $namabarang; ?></td>
                                    <td><?= $unit; ?></td>
                                    <td><?= $qty; ?></td>
                                    <td><?= $keterangan; ?></td>
                                    <!-- Modal untuk menampilkan gambar penuh -->
                                    <div class="modal fade" id="gambarModal<?= $idretur; ?>" tabindex="-1" role="dialog" aria-labelledby="gambarModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="gambarModalLabel">Bukti Retur</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <img src="data:image/jpeg;base64,<?= $gambar_base64; ?>" class="img-fluid">
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="download_gambar_retur.php?id=<?= $idretur; ?>&type=retur" class="btn btn-primary" download>Download</a>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <td>
                                        <a href="#" class="gambar-mini-trigger" data-toggle="modal" data-target="#gambarModal<?= $idretur; ?>" data-id="<?= $idretur; ?>">
                                            <img src="data:image/jpeg;base64,<?= $gambar_base64; ?>" alt="Bukti retur" style="max-width: 100px; max-height: 100px;">
                                        </a>
                                    </td>
                                    <?php if ($role === 'gudang' || $role === 'dev') :  ?>
                                        <td>
                                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idretur; ?>">
                                                Edit
                                            </button>
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idretur; ?>">
                                                Delete
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>



                                <!-- Edit Modal -->
                                <div class="modal fade" id="edit<?= $idretur; ?>">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <!-- Modal Header -->
                                            <div class="modal-header">
                                                <h4 class="modal-title">Edit Barang Retur</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <!-- Modal body -->
                                            <form method="post" enctype="multipart/form-data">
                                                <div class="modal-body">
                                                    <input type="hidden" name="idbarang" value="<?= $idb; ?>">
                                                    <label for="idretur">ID Retur</label>
                                                    <input type="text" name="idretur" value="<?= $idretur; ?>" class="form-control" readonly>
                                                    <br>
                                                    <label for="qtyretur">Jumlah Retur</label>
                                                    <input type="text" name="qtyretur" value="<?= $qty; ?>" class="form-control" required>
                                                    <br>
                                                    <label for="keterangan">Keterangan:</label>
                                                    <textarea name="keterangan" class="form-control" required><?= $keterangan; ?></textarea>
                                                    <br>
                                                    <label for="update_gambar">Bukti Retur:</label>
                                                    <input type="file" name="update_gambar" class="form-control-file" accept="image/*">
                                                    <br>
                                                    <button type="submit" class="btn btn-primary" name="updatebarangretur">Submit</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>


                                <!-- Delete Modal -->
                                <div class="modal fade" id="delete<?= $idretur; ?>">
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
                                                    <input type="hidden" name="qtyretur" value="<?= $qtyretur; ?>">
                                                    <input type="hidden" name="idretur" value="<?= $idretur; ?>">
                                                    <br>
                                                    <br>
                                                    <button type="submit" class="btn btn-danger" name="hapusbarangretur">Hapus</button>
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
            $('.gambar-mini-trigger').click(function() {
                var id = $(this).data('id');
                $('#gambarModal' + id).modal('show');
            });
        });
    </script>

</body>

<!-- The Modal "Filter Barang retur"-->
<div class="modal fade" id="filter">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Filter Barang retur</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data" action="retur.php">
                <div class="modal-body">
                    <i><b>*Perhatikan tanggal yang akan di filter</b></i>
                    <br>
                    <br>
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="tgl_mulai" class="form-control">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="tgl_selesai" class="form-control">
                    <br>
                    <button type="submit" name="filter_retur" class="btn btn-info">Filter</button>
                </div>
            </form>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- The Modal -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Tambah Barang Retur</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <label for="permintaan">No Permintaan:</label>
                    <select name="permintaan" class="form-control">
                        <?php
                        $ambilsemuadatanya = mysqli_query($conn, "select * from permintaan");
                        while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                            $idpermintaan = $fetcharray['idpermintaan'];

                        ?>

                            <option value="<?= $idpermintaan; ?>"><?= $idpermintaan; ?></option>

                        <?php
                        }
                        ?>
                    </select>
                    <br>
                    <label for="barangnya">Nama Barang:</label>
                    <select name="barangnya" class="form-control">
                        <?php
                        $ambilsemuadatanya = mysqli_query($conn, "select * from stock");
                        while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                            $namabarangnya = $fetcharray['namabarang'];
                            $idbarang = $fetcharray['idbarang'];

                        ?>

                            <option value="<?= $idbarang; ?>"><?= $namabarangnya; ?></option>

                        <?php
                        }
                        ?>
                    </select>
                    <br>
                    <label for="qty">Jumlah:</label>
                    <input type="number" name="qtyretur" placeholder="qty retur" class="form-control" required>
                    <br>
                    <label for="keterangan">Keterangan:</label>
                    <textarea name="keterangan" class="form-control" placeholder="Keterangan" rows="3" required></textarea>
                    <br>
                    <label for="gambar_base64">Bukti retur:</label>
                    <input type="file" name="gambar_base64" class="form-control-file" required>
                    <br>
                    <button type="submit" class="btn btn-primary" name="addbarangretur">Submit</button>
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
<div class="modal fade" id="retur">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Export Data Barang retur</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    Apakah Anda Yakin Ingin Mengexport Data Barang retur
                    <br>
                    <br>
                    <button type="submit" class="btn btn-outline-success" name="export_retur">Export to Excel</button>
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