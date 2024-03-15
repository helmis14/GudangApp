<?php
require '../../helper/function.php';
require '../../helper/cek.php';


// Periksa apakah pengguna sudah login
if (!isset($_SESSION['iduser'])) {
    header('Location: ../../login.php');
    exit();
}

// Periksa peran pengguna
if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev' && $_SESSION['role'] !== 'user') {
    header('Location: ../../access_denied.php');
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
        <h1 class="mt-4">Log Activity</h1>
        <div class="card mb-4">
            <?php if ($role === 'dev') :  ?>
                <div class="card-header">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exportlog">
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
    <?php
    require_once '../../layout/_footer.php';
    require_once '../../component/modalLogout.php';
    ?>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
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
            $('#backupBtn').click(function() {
                $.ajax({
                    url: '../../backup_database.php',
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
                    url: '../../restore_database.php',
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

    <!-- The Modal "Export Log"-->
    <div class="modal fade" id="exportlog">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Export Data Aktivitas User</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        Apakah Anda Yakin Ingin Mengexport Data Aktivitas User
                        <br>
                        <br>
                        <button type="submit" class="btn btn-outline-success" name="exportlog">Export to Excel</button>
                    </div>
                </form>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>


</body>

</html>