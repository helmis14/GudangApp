<?php
require '../../helper/function.php';
require '../../helper/cek.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: ../../login.php');
    exit();
}
if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev'  && $_SESSION['role'] !== 'gudang' && $_SESSION['role'] !== 'user' && $_SESSION['role'] !== 'supervisoradmin') {
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
    <title>Barang Masuk</title>
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
        <h1 class="mt-4">Barang Masuk </h1>


        <div class="card mb-4">
            <?php if ($role === 'gudang' || $role === 'dev') :  ?>
                <div class="card-header">
                    <!-- Button to Open the Modal -->
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                        Tambah Barang
                    </button>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filtermasuk">
                        Date
                    </button>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#masuk">
                        Export to Excel
                    </button>
                </div>
            <?php endif; ?>
            <div class="card-body">
                <?php
                $ambilsemuadatastock = mysqli_query($conn, "SELECT * FROM masuk m JOIN stock s ON s.idbarang = m.idbarang WHERE m.penerima = '' OR m.distributor = '' OR m.keterangan = '' OR m.bukti_masuk_base64 = ''");
                while ($data = mysqli_fetch_array($ambilsemuadatastock)) {
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

                    $kolom_kosong = array();

                    if (empty($penerima)) {
                        $kolom_kosong[] = 'Penerima';
                    }
                    if (empty($distributor)) {
                        $kolom_kosong[] = 'Distributor';
                    }
                    if (empty($keterangan)) {
                        $kolom_kosong[] = 'Keterangan';
                    }
                    if (empty($bukti_masuk_base64)) {
                        $kolom_kosong[] = 'Bukti Masuk';
                    }

                    if (!empty($kolom_kosong)) {
                ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong>Perhatian!</strong> Mohon isi kolom kosong data barang <?= $namabarang; ?> pada waktu <?= $tanggal; ?> berikut : <?= implode(', ', $kolom_kosong); ?>
                        </div>
                <?php
                    }
                }
                ?>


                <div class="table-responsive">
                    <table class="table table-bordered text-center" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Barang</th>
                                <th>Unit</th>
                                <th>Jumlah</th>
                                <th>distributor</th>
                                <th>Penerima</th>
                                <th>Keterangan</th>
                                <th>Bukti</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // talmpilkan data barang masuk berdasarkan filter
                            if (isset($_POST['filter_masuk'])) {
                                $mulai = $_POST['tgl_mulai'];
                                $selesai = $_POST['tgl_selesai'];
                                if ($mulai != null || $selesai != null) {
                                    $ambilsemuadatastock = mysqli_query($conn, "SELECT * FROM masuk m 
                                                    JOIN stock s ON s.idbarang = m.idbarang 
                                                    WHERE m.tanggal BETWEEN '$mulai' AND DATE_ADD('$selesai', INTERVAL 1 DAY) 
                                                    ORDER BY m.idmasuk DESC");
                                } else {
                                    $ambilsemuadatastock = mysqli_query($conn, "SELECT * FROM masuk m 
                                                    JOIN stock s ON s.idbarang = m.idbarang");
                                }
                            } else {
                                $ambilsemuadatastock = mysqli_query($conn, "SELECT * FROM masuk m 
                                                JOIN stock s ON s.idbarang = m.idbarang");
                            }
                            // talmpilkan barang masuk
                            while ($data = mysqli_fetch_array($ambilsemuadatastock)) {
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
                                            <img src="data:image/jpeg;base64,<?= $bukti_masuk_base64; ?>" alt="Bukti Masuk" style="max-width: 100px; max-height: 100px;">
                                        </a>
                                    </td>
                                    <td><?= ($status == 0) ? 'Dalam Pengiriman' : ($status == 1 ? 'Diterima' : 'Tidak Diterima'); ?></td>

                                    <?php if ($_SESSION['role'] === 'dev') { ?>
                                        <td>
                                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idm; ?>">
                                                Edit
                                            </button>
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idb; ?>">
                                                Delete
                                            </button>
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idm; ?>">
                                                Status
                                            </button>
                                        </td>
                                    <?php } elseif ($_SESSION['role'] === 'gudang' && $status == 0) { ?>
                                        <td>
                                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idm; ?>">
                                                Edit
                                            </button>
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idm; ?>">
                                                Status
                                            </button>
                                        </td>
                                    <?php } elseif ($status == 1 || $status == 2) { ?>
                                        <td>Ditanggapi</td>
                                    <?php } else { ?>
                                        <td>Belum Ditanggapi</td>
                                    <?php } ?>

                                    </td>
                                </tr>

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
                                                <img src="data:image/jpeg;base64,<?= $bukti_masuk_base64; ?>" class="img-fluid">
                                            </div>
                                            <div class="modal-footer">
                                                <a href="download_gambar_masuk.php?id=<?= $idm; ?>&type=keluar" class="btn btn-primary" download>Download</a>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Edit Modal lama-->
                                <!--<div class="modal fade" id="edit<?= $idm; ?>">-->
                                <!--    <div class="modal-dialog">-->
                                <!--        <div class="modal-content">-->

                                <!-- Modal Header -->
                                <!--            <div class="modal-header">-->
                                <!--                <h4 class="modal-title">Edit Barang</h4>-->
                                <!--                <button type="button" class="close" data-dismiss="modal">&times;</button>-->
                                <!--            </div>-->

                                <!-- Modal body -->
                                <!--            <form method="post" enctype="multipart/form-data">-->
                                <!--                <div class="modal-body">-->
                                <!--                    <label for="penerima">Penerima</label>-->
                                <!--                    <input type="text" name="penerima" value="<?= $penerima; ?>" class="form-control">-->
                                <!--                    <br>-->
                                <!--                    <label for="qty">Jumlah:</label>-->
                                <!--                    <input type="number" name="qty" value="<?= $qty; ?>" class="form-control">-->
                                <!--                    <label for="keterangan">keterangan:</label>-->
                                <!--                    <input type="text" name="keterangan" value="<?= $keterangan; ?>" class="form-control">-->
                                <!--                    <br>-->
                                <!--                    <label for="distributor">Distributor:</label>-->
                                <!--                    <input type="text" name="distributor" value="<?= $distributor; ?>" class="form-control">-->
                                <!--                    <br>-->
                                <!--                    <label for="update_bukti_masuk">Bukti Masuk:</label>-->
                                <!--                    <input type="file" name="update_bukti_masuk" class="form-control-file" accept="image/*">-->
                                <!--                    <br>-->
                                <!--                    <input type="hidden" name="idb" value="<?= $idb; ?>">-->
                                <!--                    <input type="hidden" name="idm" value="<?= $idm; ?>">-->
                                <!--                    <button type="submit" class="btn btn-primary" name="updatebarangmasuk">Submit</button>-->
                                <!--                </div>-->
                                <!--            </form>-->

                                <!--        </div>-->

                                <!--    </div>-->
                                <!--</div>-->

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
                                                    <label for="keterangan">keterangan:</label>
                                                    <input type="text" name="keterangan" value="<?= $keterangan; ?>" class="form-control">
                                                    <br>
                                                    <label for="distributor">Distributor:</label>
                                                    <input type="text" name="distributor" value="<?= $distributor; ?>" class="form-control">
                                                    <br>
                                                    <!--<label for="status">Status:</label>-->
                                                    <!--<select class="form-control" id="status" name="status">-->
                                                    <!--    <option value="0" <?= ($status == 0) ? 'selected' : ''; ?>>Dalam Pengiriman</option>-->
                                                    <!--    <option value="1" <?= ($status == 1) ? 'selected' : ''; ?>>Diterima</option>-->
                                                    <!--    <option value="2" <?= ($status == 2) ? 'selected' : ''; ?>>Tidak Diterima</option>-->
                                                    <!--</select>-->
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
    </div>
    </div>
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

<!-- The Modal -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Tambah Barang Masuk</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <label for="barangnya">Nama Barang:</label>
                    <select name="barangnya" class="form-control">
                        <?php
                        $ambilsemuadatanya = mysqli_query($conn, "select * from stock");
                        while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                            $namabarangnya = $fetcharray['namabarang'];
                            $idbarangnya = $fetcharray['idbarang'];

                        ?>

                            <option value="<?= $idbarangnya; ?>"><?= $namabarangnya; ?></option>

                        <?php
                        }
                        ?>
                    </select>
                    <br>
                    <label for="jumlah">Jumlah:</label>
                    <input type="number" name="qty" placeholder="Quantity" class="form-control" required>
                    </br>
                    <label for="distributor">Distributor:</label>
                    <input type="text" name="distributor" placeholder="Distributor" class="form-control" required>
                    </br>
                    <label for="penerima">Penerima</label>
                    <input type="text" name="penerima" placeholder="Penerima" class="form-control" required>
                    </br>
                    <label for="keterangan">Keterangan</label>
                    <input type="text" name="keterangan" placeholder="Keterangan" class="form-control" required>
                    </br>
                    <label for="status">Status:</label>
                    <select class="form-control" id="status" name="status">
                        <option value="0">Dalam Pengiriman</option>
                        <option value="1">Diterima</option>
                        <option value="2">Tidak Diterima</option>
                    </select>
                    <br>
                    <label for="bukti_masuk_base64">Bukti Masuk:</label>
                    <input type="file" name="bukti_masuk_base64" class="form-control-file" required>
                    <p style="font-size: small; padding-top: 7px">Ukuran bukti maksimal 5 mb </p>
                    <button type="submit" class="btn btn-primary" name="barangmasuk">Submit</button>
                </div>
            </form>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>


<!-- The Modal "Filter Barang Keluar"-->
<div class="modal fade" id="filtermasuk">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Filter Barang Masuk</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data" action="barang_masuk.php">
                <div class="modal-body">
                    <i><b>*Perhatikan tanggal yang akan di filter</b></i>
                    <br>
                    <br>
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="tgl_mulai" class="form-control">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="tgl_selesai" class="form-control">
                    <br>
                    <button type="submit" name="filter_masuk" class="btn btn-info">Filter</button>
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
<div class="modal fade" id="masuk">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Export Data Barang Masuk</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    Apakah Anda Yakin Ingin Mengexport Data Barang Masuk
                    <br>
                    <br>
                    <button type="submit" class="btn btn-outline-success" name="export_masuk">Export to Excel</button>
                </div>
            </form>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div </html>