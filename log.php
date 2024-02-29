<?php
require 'function.php';
require 'cek.php';


// Periksa apakah pengguna sudah login
if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

// Periksa peran pengguna
if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev' && $_SESSION['role'] !== 'user') {
    header('Location: access_denied.php');
    exit();
}


// Ambil user ID dari sesi
$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];
// Query untuk mendapatkan log beserta nama pengguna
$query = "SELECT log.id, log.activity, log.timestamp, login.email 
          FROM log 
          LEFT JOIN login ON log.iduser = login.iduser
          ORDER BY log.timestamp DESC";
$result = mysqli_query($conn, $query);
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Log Activity</title>
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
                    <h1 class="mt-4">Log Activity</h1>
                    <div class="card mb-4">
                        <?php if ($role === 'dev') :  ?>
                            <div class="card-header">
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#export">
                                    Export Data
                                </button>
                                <button type="button" class="btn btn-danger" id="hapusLogBtn">Hapus Log</button>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#backup">
                                    Backup Data
                                </button>
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#restoreModal">
                                    Restore Data
                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Activity</th>
                                            <th>Timestamp</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['activity']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";

                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                        <!-- backup data -->
                        <div class="modal fade" id="backup" tabindex="-1" role="dialog" aria-labelledby="exportLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exportLabel">Export Database</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Apakah Anda yakin ingin melakukan backup database?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                        <button type="button" class="btn btn-primary" id="backupBtn">Backup Database</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal untuk restore data -->
                        <div class="modal fade" id="restoreModal" tabindex="-1" role="dialog" aria-labelledby="restoreModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="restoreModalLabel">Restore Data</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="restoreForm" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label for="fileToRestore">Pilih File Backup:</label>
                                                <input type="file" class="form-control-file" id="fileToRestore" name="fileToRestore">
                                            </div>
                                            <button type="submit" class="btn btn-primary">Restore</button>
                                        </form>
                                    </div>
                                </div>
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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
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
            $('#backupBtn').click(function() {
                $.ajax({
                    url: 'backup_database.php',
                    method: 'GET',
                    success: function(response) {
                        $('#backup').modal('hide');
                        alert("Backup database berhasil disimpan.");
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert("Backup database gagal. Terjadi kesalahan: " + xhr.responseText);
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#restoreForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: 'restore_database.php',
                    type: 'POST',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        alert(response);
                        $('#restoreModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert("Restore database gagal. Terjadi kesalahan: " + xhr.responseText);
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#hapusLogBtn').click(function() {
                if (confirm("Apakah Anda yakin ingin menghapus semua data log?")) {
                    $.ajax({
                        url: 'hapus_log.php',
                        method: 'GET',
                        success: function(response) {
                            alert(response);
                            location.reload();
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                            alert("Gagal menghapus data log. Terjadi kesalahan: " + xhr.responseText);
                        }
                    });
                }
            });
        });
    </script>




</body>

</html>