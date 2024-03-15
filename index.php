<?php
require './helper/function.php';
require './helper/cek.php';
require 'vendor/autoload.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev'  && $_SESSION['role'] !== 'gudang' && $_SESSION['role'] !== 'user') {
    header('Location: access_denied.php');
    exit();
}

$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

// Function to establish database connection
function connectToDatabase()
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "stokbarangs";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Function to import data from Excel to MySQL
function importDataFromExcel($excelFilePath, $conn)
{
    $reader = new Xlsx();
    $spreadsheet = $reader->load($excelFilePath);
    $sheet = $spreadsheet->getActiveSheet();

    // Assuming the first row contains column names
    $columns = [];
    $highestColumn = $sheet->getHighestColumn();
    foreach (range('A', $highestColumn) as $col) {
        $columns[] = $sheet->getCell($col . '1')->getValue();
    }

    for ($row = 2; $row <= $sheet->getHighestRow(); $row++) {
        $rowData = [];
        foreach ($columns as $col) {
            $colIndex = array_search($col, $columns) + 1;
            $rowData[$col] = $sheet->getCellByColumnAndRow($colIndex, $row)->getValue();
        }

        // Prepare and execute the MySQL insert query using prepared statements
        $stmt = $conn->prepare("INSERT INTO stock (idbarang, namabarang, unit, stock, lokasi)
                        VALUES (?, ?, ?, ?, ?)");

        // Check if 'unit' is empty, provide a default value if needed
        $unitValue = !empty($rowData['unit']) ? $rowData['unit'] : 'Default unit';

        $stmt->bind_param("sssss", $rowData['No'], $rowData['Nama Barang'], $unitValue, $rowData['Stock'], $rowData['Lokasi/rak']);

        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Check if the form is submitted
if (isset($_POST['import']) && isset($_FILES["excel_file"])) {
    $conn = connectToDatabase();
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["excel_file"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the file is an Excel file
    if ($fileType != "xlsx" && $fileType != "xls") {
        echo "Sorry, only Excel files are allowed.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["excel_file"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["excel_file"]["tmp_name"], $target_file)) {

            importDataFromExcel($target_file, $conn);
            echo "File uploaded and data imported successfully.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    $conn->close();
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Stock Barang</title>
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
                            <a class="nav-link" href="./view/permintaan/permintaan.php">
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
                            <a class="nav-link" href="./view/masuk/barang_masuk.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-cart-plus"></i></div>
                                Barang Masuk
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user' || $_SESSION['role'] === 'gudang') { ?>
                            <a class="nav-link" href="./view/retur/retur.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-cart-arrow-down"></i></div>
                                Retur Barang
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user' || $_SESSION['role'] === 'gudang') { ?>
                            <a class="nav-link" href="./view/keluar/barang_keluar.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-box-open"></i></div>
                                Barang Keluar
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user') { ?>
                            <a class="nav-link" href="./view/admin/admin.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                                Kelola Admin
                            </a>
                        <?php } ?>

                        <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user') { ?>
                            <a class="nav-link" href="./view/log/log.php">
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
                    <h1 class="mt-4">Stock Barang</h1>
                    <div class="card mb-4">
                        <?php if ($role === 'gudang' || $role === 'dev') :  ?>
                            <div class="card-header">
                                <!-- Button to Open the Modal "Tambah Barang"-->
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                    Tambah Barang
                                </button>
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#export">
                                    Export
                                </button>
                                </button>
                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#import">
                                    Import
                                </button>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">

                            <?php
                            $ambildatastock = mysqli_query($conn, "select * from stock where stock < 1");
                            while ($fetch = mysqli_fetch_array($ambildatastock)) {
                                $barang = $fetch['namabarang'];


                            ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <strong>Perhatian!</strong> Stock <?= $barang; ?> Telah Habis.
                                </div>
                            <?php
                            }
                            ?>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Unit</th>
                                            <th>Stock</th>
                                            <th>Lokasi/Rak</th>
                                            <?php if ($role === 'gudang' || $role == 'dev') : ?>
                                                <th>Aksi</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php
                                        $ambilsemuadatastock = mysqli_query($conn, "select * from stock");
                                        $i = 1;
                                        while ($data = mysqli_fetch_array($ambilsemuadatastock)) {
                                            $namabarang = $data['namabarang'];
                                            $unit = $data['unit'];
                                            $stock = $data['stock'];
                                            $lok = $data['lokasi'];
                                            $idb = $data['idbarang'];
                                        ?>

                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= $namabarang; ?></td>
                                                <td><?= $unit; ?></td>
                                                <td><?= $stock; ?></td>
                                                <td><?= $lok; ?></td>
                                                <?php if ($role == 'dev') : ?>
                                                    <td>
                                                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idb; ?>">
                                                            Edit
                                                        </button>
                                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idb; ?>">
                                                            Delete
                                                        </button>
                                                    </td>
                                                <?php elseif ($role === 'gudang') : ?>
                                                    <td>
                                                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idb; ?>">
                                                            Edit
                                                        </button>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="edit<?= $idb; ?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">

                                                        <!-- Modal Header -->
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Barang</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>

                                                        <!-- Modal body -->
                                                        <form method="post">
                                                            <div class="modal-body">
                                                                <label for="namabarang">Nama Barang</label>
                                                                <input type="text" name="namabarang" value="<?= $namabarang; ?>" class="form-control" required>
                                                                <br>
                                                                <label for="unit">Unit:</label>
                                                                <input type="text" name="unit" value="<?= $unit; ?>" class="form-control" required>
                                                                <br>
                                                                <label for="lokasi">Lokasi:</label>
                                                                <input type="text" name="lokasi" value="<?= $lok; ?>" class="form-control" required>
                                                                <br>
                                                                <input type="hidden" name="idb" value="<?= $idb; ?>">
                                                                <button type="submit" class="btn btn-primary" name="updatebarang">Submit</button>
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
                                                <br>
                                                <br>
                                                <button type="submit" class="btn btn-danger" name="hapusbarang">Hapus</button>
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
    </main>
    <footer class="py-4 bg-light mt-auto">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between small">
                <div class="text-muted">Copyright &copy; PT. Rohedagroup 2024</div>
                <div>
                    <a href="./view/about/privacy_policy.php">Privacy Policy</a>
                    &middot;
                    <a href="./view/about/terms_conditions.php">Terms &amp; Conditions</a>
                </div>
            </div>
        </div>
    </footer>
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
</body>

<!-- The Modal "Tambah Barang"-->
<div class="modal fade" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Tambah Barang</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post">
                <div class="modal-body">
                    <label for="namabarang">Nama Barang:</label>
                    <input type="text" name="namabarang" placeholder="Nama Barang" class="form-control" required>
                    <br>
                    <label for="unit">Unit:</label>
                    <select name="unit" class="form-control">
                        <option value="PCS">PCS</option>
                        <option value="Pack">Pack</option>
                        <option value="Kg">KG</option>
                        <option value="Ball">BALL</option>
                    </select>

                    <br>
                    <!-- <input type="text" name="unit" placeholder="unit" class="form-control" required>
                                    <br> -->
                    <label for="stock">Stock:</label>
                    <input type="number" name="stock" placeholder="Jumlah" class="form-control" required>
                    <br>
                    <label for="lokasi">Lokasi:</label>
                    <input type="text" name="lokasi" placeholder="Lokasi/Rak" class="form-control" required>
                    <br>
                    <button type="submit" class="btn btn-primary" name="addnewbarang">Submit</button>
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
<div class="modal fade" id="export">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Export Data Stock Barang</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    Apakah Anda Yakin Ingin Mengexport Data Stock Barang
                    <br>
                    <br>
                    <button type="submit" class="btn btn-outline-success" name="export">Export to Excel</button>
                </div>
            </form>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

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


<!-- The Modal "Import"-->
<div class="modal fade" id="import">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Import Data Stock Barang</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    Apakah Anda Yakin Ingin Mengimport Data Stock Barang
                    <br>
                    <br>
                    Select Excel file to upload:
                    <input type="file" name="excel_file" id="excel_file">
                    <input type="submit" value="Upload and Import" name="import">
            </form>
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