<?php
require 'function.php';
require 'cek.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev' && $_SESSION['role'] !== 'supervisor' && $_SESSION['role'] !== 'user') {
    header('Location: access_denied.php');
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
    <title>Kelola Admin</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>


</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">Plaza Oleos</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
        <ul class="navbar-nav ml-auto mr-0 mr-md-3 my-2 my-md-0">
            <li class="nav-item dropdown">
            </li>
            <li class="nav-item">
                <span class="nav-link">
                    <div class="navbar-brand"></div>
                    Selamat datang, <?= $role; ?>
                </span>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user' || $_SESSION['role'] === 'supervisor') { ?>
                            <a class="nav-link" href="permintaan.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                                Permintaan Barang
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user' || $_SESSION['role'] === 'gudang') { ?>
                            <a class="nav-link" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div>
                                Stock Barang
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user' || $_SESSION['role'] === 'gudang') { ?>
                            <a class="nav-link" href="barang_masuk.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-cart-plus"></i></div>
                                Barang Masuk
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user' || $_SESSION['role'] === 'gudang') { ?>
                            <a class="nav-link" href="barang_keluar.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-box-open"></i></div>
                                Barang Keluar
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user') { ?>
                            <a class="nav-link" href="admin.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                                Kelola Admin
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user') { ?>
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
                    <h1 class="mt-4">Permintaan Barang</h1>
                    <div class="card mb-4">
                        <!-- Button to Open the Modal "Tambah Barang"-->
                        <?php if ($role === 'supervisor' || $role === 'dev') :  ?>
                            <div class="card-header">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                    Tambah Permintaan
                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <?php
                            $conn = mysqli_connect("localhost", "root", "", "stokbarangs");
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
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <?php
                                    $query = "SELECT permintaan.idpermintaan, permintaan.tanggal, permintaan.status, GROUP_CONCAT(CONCAT(barang_permintaan.namabarang, '')) as detail_barang, GROUP_CONCAT(barang_permintaan.qtypermintaan) as qtypermintaan, GROUP_CONCAT(barang_permintaan.unit) as unit, GROUP_CONCAT(barang_permintaan.keterangan) as keterangan, permintaan.bukti_base64, permintaan.status 
                                        FROM permintaan
                                        INNER JOIN barang_permintaan ON permintaan.idpermintaan = barang_permintaan.idpermintaan
                                        GROUP BY permintaan.idpermintaan
                                        ORDER BY permintaan.idpermintaan DESC";

                                    $result = mysqli_query($conn, $query);
                                    ?>
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
                                            <?php if ($role !== 'user') : ?>
                                                <th>Aksi</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $total_rows = mysqli_num_rows($result);
                                        $no = $total_rows;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $idpermintaan = $row['idpermintaan'];
                                            $tanggal = $row['tanggal'];
                                            $status_permintaan = $row['status'];
                                        ?>
                                            <tr>
                                                <td><?= $no--; ?></td>
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
                                                <td><?= ($status_permintaan == 0) ? 'Pending' : ($status_permintaan == 1 ? 'Diterima' : 'Ditolak'); ?></td>
                                                <td>
                                                    <?php if ($_SESSION['role'] === 'supervisor'  && $status_permintaan == 0) { ?>

                                                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                                                            Edit
                                                        </button>
                                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deletModal<?= $idpermintaan; ?>">
                                                            Delete
                                                        </button>

                                                    <?php } elseif ($_SESSION['role'] === 'superadmin'  && $status_permintaan == 0) { ?>

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

                                                    <?php } else { ?>
                                                        Ditanggapi
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
                                                                        <option value="Pcs">PCS</option>
                                                                        <option value="Pack">Pack</option>
                                                                        <option value="Kg">KG</option>
                                                                        <option value="Ball">BALL</option>
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
                                                                        <option value="1" disabled>Diterima</option>
                                                                        <option value="2" disabled>Ditolak</option>
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
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; PT. Rohedagroup 2024</div>
                        <div>
                            <a href="privacy_policy.php">Privacy Policy</a>
                            &middot;
                            <a href="terms_conditions.php">Terms &amp; Conditions</a>
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
                            Apakah anda yakin ingin keluar <?= $role; ?>?
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
                        <option value="Pcs">PCS</option>
                        <option value="Pack">Pack</option>
                        <option value="Kg">KG</option>
                        <option value="Ball">BALL</option>
                    </select>
                    <br>
                    <label for="qtypermintaan${counter}">Jumlah:</label>
                    <input type="Number" name="qtypermintaan[]" placeholder="Quantity" class="form-control" required>
                    <br>
                    <label for="keterangan${counter}">Keterangan:</label>
                    <input type="text" name="keterangan[]" placeholder="Keterangan" class="form-control" required>
                    <br>

                    <label for="status${counter}">Status:</label>
                    <select name="status[]" class="form-control">
                        <option value="0">Pending</option>
                        <option value="1" disabled>Diterima</option>
                        <option value="2" disabled>Ditolak</option>
                    </select>
                    <br>
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
                    <br>
                    <label for="namabarang[]">Nama Barang:</label>
                    <input type="text" name="namabarang[]" placeholder="Nama Barang" class="form-control" required>
                    <br>
                    <label for="unit[]">Unit:</label>
                    <select name="unit[]" class="form-control">
                        <option value="Pcs">PCS</option>
                        <option value="Pack">Pack</option>
                        <option value="Kg">KG</option>
                        <option value="Ball">BALL</option>
                    </select>
                    <br>
                    <label for="qtypermintaan[]">Jumlah:</label>
                    <input type="Number" name="qtypermintaan[]" placeholder="Quantity" class="form-control" required>
                    <br>
                    <label for="keterangan[]">Keterangan:</label>
                    <input type="text" name="keterangan[]" placeholder="Keterangan" class="form-control" required>
                    <br>

                    <label for="status[]">Status:</label>
                    <select name="status[]" class="form-control">
                        <option value="0">Pending</option>
                        <option value="1" disabled>Diterima</option>
                        <option value="2" disabled>Ditolak</option>
                    </select>
                    <hr>
                    <br>
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
                        <option value="Pcs">PCS</option>
                        <option value="Pack">Pack</option>
                        <option value="Kg">KG</option>
                        <option value="Ball">BALL</option>
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



</html>