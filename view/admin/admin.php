<?php
require '../../helper/function.php';
require '../../helper/cek.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: ../../login.php');
    exit();
}

if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev') {
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
    <title>Kelola Admin</title>
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
        <h1 class="mt-4">Kelola Admin</h1>
        <div class="card mb-4">
            <?php if ($role === 'dev') :  ?>
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                        Tambah Admin
                    </button>
                </div>
            <?php endif; ?>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Role</th>
                                <th>Email</th>
                                <th>Password</th>
                                <?php if ($_SESSION['role'] === 'dev') { ?>
                                    <th>Aksi</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ambilsemuadataadmin = mysqli_query($conn, "select * from login");
                            $i = 1;
                            while ($data = mysqli_fetch_array($ambilsemuadataadmin)) {
                                $em = $data['email'];
                                $iduser = $data['iduser'];
                                $pass = $data['password'];
                                $role = $data['role'];
                            ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= $role; ?></td>
                                    <td><?= $em; ?></td>
                                    <td>
                                        <?php
                                        // Periksa peran pengguna
                                        if ($_SESSION['role'] === 'dev') {
                                            echo $pass;
                                        } else {
                                            echo '••••••••••';
                                        }
                                        ?>
                                    </td>
                                    <?php if ($_SESSION['role'] === 'dev') { ?>

                                        <td>
                                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $iduser; ?>">
                                                Edit
                                            </button>
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $iduser; ?>">
                                                Delete
                                            </button>
                                        </td>
                                    <?php } ?>
                                </tr>
                                <!-- Edit Modal -->
                                <div class="modal fade" id="edit<?= $iduser; ?>">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <!-- Modal Header -->
                                            <div class="modal-header">
                                                <h4 class="modal-title">Edit Admin</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <!-- Modal body -->
                                            <form method="post">
                                                <div class="modal-body">
                                                    <!-- Input hidden untuk menyimpan iduser -->
                                                    <input type="hidden" name="iduser" value="<?= $iduser; ?>">
                                                    <label for="email">Email</label>
                                                    <input type="text" name="email" value="<?= $em; ?>" class="form-control" required>
                                                    <br>
                                                    <label for="password">Password</label>
                                                    <input type="password" name="password" value="<?= $pass; ?>" class="form-control" required>
                                                    <br>
                                                    <label for="role">Role</label>
                                                    <input type="role" name="role" value="<?= $role; ?>" class="form-control" required>
                                                    <br>
                                                    <button type="submit" class="btn btn-primary" name="updateadmin">Submit</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Delete Modal -->
                                <div class="modal fade" id="delete<?= $iduser; ?>">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <!-- Modal Header -->
                                            <div class="modal-header">
                                                <h4 class="modal-title">Hapus Admin</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <!-- Modal body -->
                                            <form method="post">
                                                <div class="modal-body">
                                                    Apakah Anda Yakin Ingin Menghapus <?= $em; ?>?
                                                    <input type="hidden" name="iduser" value="<?= $iduser; ?>">
                                                    <br>
                                                    <br>
                                                    <input type="hidden" name="email" value="<?= $em; ?>">
                                                    <button type="submit" class="btn btn-danger" name="hapusadmin">Hapus</button>
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
</body>

<!-- The Modal "Tambah admin"-->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Tambah Admin</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <!-- Modal body -->
            <form method="post">
                <div class="modal-body">
                    <label for="email">Email</label>
                    <input type="text" name="email" placeholder="email" class="form-control" required>
                    <br>
                    <label for="password">Password</label>
                    <input type="password" name="password" placeholder="password" class="form-control" required>
                    <br>
                    <label for="role">Role</label>
                    <input type="text" name="role" placeholder="role" class="form-control" required>
                    <br>
                    <button type="submit" class="btn btn-primary" name="addnewadmin">Submit</button>
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