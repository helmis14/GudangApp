<?php
require '../../helper/function.php';
require '../../helper/cek.php';
require '../../vendor/autoload.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: ../../view/auth/login.php');
    exit();
}

if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev'  && $_SESSION['role'] !== 'gudang' && $_SESSION['role'] !== 'user' && $_SESSION['role'] !== 'supervisor' && $_SESSION['role'] !== 'supervisoradmin' && $_SESSION['role'] !== 'supervisorgudang') {
    header('Location: ../../access_denied.php');
    exit();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalDataQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM stock");
$totalData = mysqli_fetch_assoc($totalDataQuery)['total'];
$totalPages = ceil($totalData / $limit);

$ambilsemuadatastock = mysqli_query($conn, "SELECT * FROM stock LIMIT $limit OFFSET $offset");

$currentRange = 2;

$startRange = max(1, $page - $currentRange);
$endRange = min($totalPages, $page + $currentRange);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

// Function to establish database connection
function connectToDatabase()
{

    $servername = "localhost";
    $username = "u6939598_whpl4zaole0s";
    $password = "IT@RG2024!Plaza0leos";
    $dbname = "u6939598_whplazaoleos";

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
        $stmt = $conn->prepare("INSERT INTO stock (idbarang, namabarang, kategori, unit, stock, lokasi) VALUES (?, ?, ?, ?, ?, ?)");

        // Check if 'unit' is empty, provide a default value if needed
        $unitValue = !empty($rowData['Unit']) ? $rowData['Unit'] : 'Default unit';

        $stmt->bind_param("isssss", $rowData['No'], $rowData['Nama Barang'], $rowData['Kategori'], $unitValue, $rowData['Stock'], $rowData['Lokasi/Rak']);

        if (!$stmt->execute()) {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Check if the form is submitted
if (isset($_POST['import']) && isset($_FILES["excel_file"])) {
    $conn = connectToDatabase();
    $target_dir = "../../uploads/";
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

            $ambilsemuadatastock = mysqli_query($conn, "select * from stock");
            $i = 1;
            while ($data = mysqli_fetch_array($ambilsemuadatastock)) {
                // ...
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
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
    <link data-n-head="ssr" rel="icon" type="image/png" sizes="16x16" href="../../assets/img/icon.png">
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
        <h1 class="mt-4">Laporan Barang</h1>
        <div class="card mb-4">
            <?php
            $buttons = [];

            if (in_array($role, ['dev', 'gudang'])) {
                $buttons[] = '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">Tambah Barang</button>';
            }

            if (in_array($role, ['supervisor', 'supervisoradmin', 'superadmin', 'dev', 'gudang'])) {
                $buttons[] = '<button type="button" class="btn btn-success" data-toggle="modal" data-target="#export">Export</button>';
            }


            if ($role === 'dev') {
                $buttons[] = '<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#import">Import</button>';
            }


            if (!empty($buttons)) :
            ?>
                <div class="card-header d-flex align-items-center">
                    <div class="p-2">
                        <?php echo implode(' ', $buttons); ?>
                    </div>
                    <div class="p-2 ml-auto">
                        <div class="input-group">
                            <input class="form-control" type="text" id="search-input" placeholder="Cari Barang" aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-secondary" id="cancel-search" type="button" style="display: none;">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>


            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Unit</th>
                                <?php if ($role === 'gudang' || $role == 'dev') : ?>
                                    <th>Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $i = $offset + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatastock)) {
                                $idbarang = $data['idbarang'];
                                $namabarang = $data['namabarang'];
                                $kategori = $data['kategori'];
                                $unit = $data['unit'];
                                $idb = $data['idbarang'];
                            ?>

                                <tr>
                                    <div id="loading" style="display:none;text-align:center;">
                                        <p> <img src="../../assets/gif/loading.gif" alt="Loading..." /></p>
                                    </div>
                                    <td><?= $idbarang; ?></td>
                                    <td><?= $namabarang; ?></td>
                                    <td><?= $kategori; ?></td>
                                    <td><?= $unit; ?></td>
                                    <?php if ($role == 'dev') : ?>
                                        <td>
                                            <button type="button" class="btn btn-success" data-target="#barang<?= $idb; ?>">
                                                Lihat Barang
                                            </button>
                                        </td>
                                    <?php elseif ($role === 'gudang') : ?>
                                        <td>
                                            <button type="button" class="btn btn-success" data-target="#barang<?= $idb; ?>">
                                                Lihat Barang
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
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

                <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=1" aria-label="First">
                        <span aria-hidden="true">« Awal</span>
                    </a>
                </li>


                <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?= max(1, $page - 1); ?>" aria-label="Previous">
                        <span aria-hidden="true">‹ Sebelumnya</span>
                    </a>
                </li>


                <?php for ($i = $startRange; $i <= $endRange; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>


                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?= min($totalPages, $page + 1); ?>" aria-label="Next">
                        <span aria-hidden="true">Selanjutnya ›</span>
                    </a>
                </li>


                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?= $totalPages; ?>" aria-label="Last">
                        <span aria-hidden="true">Terakhir »</span>
                    </a>
                </li>
            </ul>
        </nav>

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
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            function bindEditButtons() {
                $('.btn-warning').off('click').on('click', function() {
                    var target = $(this).data('target');
                    $(target).modal('show');
                });
            }

            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func.apply(this, args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            function handleSearch() {
                $('#loading').show();
                var search = $('#search-input').val();

                if (search !== '') {
                    $('#cancel-search').show();
                    $('.pagination').hide();
                } else {
                    $('#cancel-search').hide();
                    $('.pagination').show();
                }

                $.ajax({
                    url: 'search_stock.php',
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
            }



            $('#cancel-search').on('click', function() {
                $('#search-input').val('');
                $(this).hide();
                $('.pagination').show();


                window.location.href = "?page=1";
            });



            $('#search-input').on('input', debounce(handleSearch, 500));

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
                        <option value="PACK">PACK</option>
                        <option value="KG">KG</option>
                        <option value="BALL">BALL</option>
                        <option value="BATANG">BATANG</option>
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
                    <!-- <input type="text" name="unit" placeholder="unit" class="form-control" required>
                                    <br> -->
                    <label for="kategori">Kategori:</label>
                    <input type="text" name="kategori" placeholder="Kategori" class="form-control" required>
                    <br>
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