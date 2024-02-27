<?php
require 'function.php';
require 'cek.php';


if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev'  && $_SESSION['role'] !== 'gudang') {
    header('Location: access_denied.php');
    exit();
}

$iduser = $_SESSION['iduser'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Barang Keluar</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">Plaza Oleos</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>

    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'supervisor') { ?>
                            <a class="nav-link" href="permintaan.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                                Permintaan Barang
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'gudang') { ?>
                            <a class="nav-link" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div>
                                Stock Barang
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'gudang') { ?>
                            <a class="nav-link" href="barang_masuk.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-cart-plus"></i></div>
                                Barang Masuk
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'gudang') { ?>
                            <a class="nav-link" href="barang_keluar.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-box-open"></i></div>
                                Barang Keluar
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev') { ?>
                            <a class="nav-link" href="admin.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                                Kelola Admin
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev') { ?>
                            <a class="nav-link" href="log.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-walking"></i></div>
                                Log Aktivitas
                            </a>
                        <?php } ?>

                        <a class="nav-link" href="#" data-toggle="modal" data-target="#logoutModal">
                            <div class="sb-nav-link-icon"><i class="fas fa-power-off"></i></div>
                            Logout
                        </a>
                    </div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid">
                    <h1 class="mt-4">Barang Keluar </h1>


                    <div class="card mb-4">
                        <div class="card-header">
                            <!-- Button to Open the Modal -->
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                Tambah Barang
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nama Barang</th>
                                            <th>Unit</th>
                                            <th>Jumlah</th>
                                            <th>Penerima</th>
                                            <th>Keterangan</th>
                                            <th>Bukti Keluar</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $ambilsemuadatastock = mysqli_query($conn, "select * from keluar k, stock s where s.idbarang = k.idbarang");
                                        while ($data = mysqli_fetch_array($ambilsemuadatastock)) {
                                            $idk = $data['idkeluar'];
                                            $idb = $data['idbarang'];
                                            $tanggal = $data['tanggal'];
                                            $namabarang = $data['namabarang'];
                                            $qty = $data['qty'];
                                            $penerima = $data['penerima'];
                                            $unit = $data['unit'];
                                            $keterangan = $data['keterangan'];
                                            $gambar_base64 = $data['gambar_base64'];

                                        ?>

                                            <tr>
                                                <td><?= $tanggal; ?></td>
                                                <td><?= $namabarang; ?></td>
                                                <td><?= $unit; ?></td>
                                                <td><?= $qty; ?></td>
                                                <td><?= $penerima; ?></td>
                                                <td><?= $keterangan; ?></td>
                                                <!-- Modal untuk menampilkan gambar penuh -->
                                                <div class="modal fade" id="gambarModal<?= $idk; ?>" tabindex="-1" role="dialog" aria-labelledby="gambarModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="gambarModalLabel">Bukti Keluar</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <img src="data:image/jpeg;base64,<?= $gambar_base64; ?>" class="img-fluid">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a href="download_gambar_keluar.php?id=<?= $idk; ?>&type=keluar" class="btn btn-primary" download>Download</a>
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <td>
                                                    <a href="#" class="gambar-mini-trigger" data-toggle="modal" data-target="#gambarModal<?= $idk; ?>" data-id="<?= $idk; ?>">
                                                        <img src="data:image/jpeg;base64,<?= $gambar_base64; ?>" alt="Bukti Keluar" style="max-width: 100px; max-height: 100px;">
                                                    </a>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idk; ?>">
                                                        Edit
                                                    </button>
                                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idk; ?>">
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>



                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="edit<?= $idk; ?>">
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
                                                                <input type="text" name="penerima" value="<?= $penerima; ?>" class="form-control" required>
                                                                <br>
                                                                <label for="qty">Jumlah</label>
                                                                <input type="text" name="qty" value="<?= $qty; ?>" class="form-control" required>
                                                                <br>
                                                                <label for="keterangan">Keterangan:</label>
                                                                <textarea name="keterangan" class="form-control" required><?= $keterangan; ?></textarea>
                                                                <br>
                                                                <label for="update_gambar">Bukti Keluar:</label>
                                                                <input type="file" name="update_gambar" class="form-control-file" accept="image/*">
                                                                <br>
                                                                <input type="hidden" name="idb" value="<?= $idb; ?>">
                                                                <input type="hidden" name="idk" value="<?= $idk; ?>">
                                                                <button type="submit" class="btn btn-primary" name="updatebarangkeluar">Submit</button>
                                                            </div>
                                                        </form>

                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="delete<?= $idk; ?>">
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
                                                                <input type="hidden" name="idk" value="<?= $idk; ?>">
                                                                <br>
                                                                <br>
                                                                <button type="submit" class="btn btn-danger" name="hapusbarangkeluar">Hapus</button>
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
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; PT. Rohedagroup 2024</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
            <!-- Modal Logout-->
            <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Konfirmasi Logout</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Apakah Anda yakin ingin keluar?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <a class="btn btn-primary" href="logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/datatables-demo.js"></script>
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
                <h4 class="modal-title">Tambah Barang Keluar</h4>
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
                    <label for="qty">Jumlah:</label>
                    <input type="number" name="qty" placeholder="qty" class="form-control" required>
                    <br>
                    <label for="penerima">Penerima:</label>
                    <input type="text" name="penerima" placeholder="Penerima" class="form-control" required>
                    <br>
                    <label for="keterangan">Keterangan:</label>
                    <textarea name="keterangan" class="form-control" placeholder="Keterangan" rows="3" required></textarea>
                    <br>
                    <label for="gambar_base64">Bukti Keluar:</label>
                    <input type="file" name="gambar_base64" class="form-control-file" required>
                    <br>
                    <button type="submit" class="btn btn-primary" name="addbarangkeluar">Submit</button>
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