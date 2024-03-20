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
    <title>Barang Keluar</title>
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
        <h1 class="mt-4">Barang Keluar </h1>
        <div class="card mb-4">
            <?php if ($role === 'gudang' || $role === 'dev') :  ?>
                <div class="card-header">
                    <!-- Button to Open the Modal -->
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                        Tambah Barang
                    </button>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filter">
                        Date
                    </button>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#keluar">
                        Export to Excel
                    </button>
                </div>
            <?php endif; ?>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center" id="dataTable" width="100%" cellspacing="0">
                        <?php
                        $query = "SELECT 
                                        keluar.idpermintaan, 
                                        permintaan_keluar.tanggal, 
                                        permintaan_keluar.gambar_base64,
                                        GROUP_CONCAT(CONCAT(stock.namabarang, '')) as nama_barang, 
                                        GROUP_CONCAT(stock.unit) as unit, 
                                        GROUP_CONCAT(keluar.qty) as qty, 
                                        GROUP_CONCAT(keluar.penerima) as penerima, 
                                        GROUP_CONCAT(keluar.keterangan) as keterangan 
                                    FROM 
                                        permintaan_keluar 
                                    INNER JOIN 
                                        keluar ON permintaan_keluar.idpermintaan = keluar.idpermintaan
                                    INNER JOIN 
                                        stock ON keluar.idbarang = stock.idbarang
                                    GROUP BY 
                                        keluar.idpermintaan 
                                    ORDER BY 
                                        keluar.idpermintaan DESC";

                        $result = mysqli_query($conn, $query);
                        ?>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Barang</th>
                                <th>Unit</th>
                                <th>Jumlah</th>
                                <th>Penerima</th>
                                <th>Keterangan</th>
                                <th>Bukti Keluar</th>
                                <?php if ($role === 'gudang' || $role === 'dev') :  ?>
                                    <th>Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // tampilkan data barang keluar berdasarkan filter

                            if (isset($_POST['filter_keluar'])) {
                                $mulai = $_POST['tgl_mulai'];
                                $selesai = $_POST['tgl_selesai'];

                                if (!empty($mulai) && !empty($selesai)) {
                                    // Gunakan parameterized query untuk mencegah serangan SQL Injection
                                    $query = "SELECT keluar.idpermintaan, permintaan_keluar.tanggal, permintaan_keluar.gambar_base64,
                                                            GROUP_CONCAT(CONCAT(stock.namabarang, '')) as nama_barang, 
                                                            GROUP_CONCAT(stock.unit) as unit, 
                                                            GROUP_CONCAT(keluar.qty) as qty, 
                                                            GROUP_CONCAT(keluar.penerima) as penerima, 
                                                            GROUP_CONCAT(keluar.keterangan) as keterangan 
                                                        FROM permintaan_keluar 
                                                        INNER JOIN keluar ON permintaan_keluar.idpermintaan = keluar.idpermintaan
                                                        INNER JOIN stock ON keluar.idbarang = stock.idbarang
                                                        WHERE permintaan_keluar.tanggal BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)
                                                        GROUP BY keluar.idpermintaan 
                                                        ORDER BY keluar.idpermintaan DESC";
                                    $stmt = mysqli_prepare($conn, $query);
                                    mysqli_stmt_bind_param($stmt, "ss", $mulai, $selesai);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                } else {
                                    $result = mysqli_query($conn, "SELECT keluar.idpermintaan, permintaan_keluar.tanggal, permintaan_keluar.gambar_base64,
                                                            GROUP_CONCAT(CONCAT(stock.namabarang, '')) as nama_barang, 
                                                            GROUP_CONCAT(stock.unit) as unit, 
                                                            GROUP_CONCAT(keluar.qty) as qty, 
                                                            GROUP_CONCAT(keluar.penerima) as penerima, 
                                                            GROUP_CONCAT(keluar.keterangan) as keterangan 
                                                        FROM permintaan_keluar 
                                                        INNER JOIN keluar ON permintaan_keluar.idpermintaan = keluar.idpermintaan
                                                        INNER JOIN stock ON keluar.idbarang = stock.idbarang
                                                        GROUP BY keluar.idpermintaan 
                                                        ORDER BY keluar.idpermintaan DESC");
                                }
                            } else {
                                $result = mysqli_query($conn, "SELECT keluar.idpermintaan, permintaan_keluar.tanggal, permintaan_keluar.gambar_base64,
                                                            GROUP_CONCAT(CONCAT(stock.namabarang, '')) as nama_barang, 
                                                            GROUP_CONCAT(stock.unit) as unit, 
                                                            GROUP_CONCAT(keluar.qty) as qty, 
                                                            GROUP_CONCAT(keluar.penerima) as penerima, 
                                                            GROUP_CONCAT(keluar.keterangan) as keterangan 
                                                        FROM permintaan_keluar 
                                                        INNER JOIN keluar ON permintaan_keluar.idpermintaan = keluar.idpermintaan
                                                        INNER JOIN stock ON keluar.idbarang = stock.idbarang
                                                        GROUP BY keluar.idpermintaan 
                                                        ORDER BY keluar.idpermintaan DESC");
                            }

                            // Talmpilkan data barang keluar
                            $total_rows = mysqli_num_rows($result);
                            $no = $total_rows;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $idpermintaan = $row['idpermintaan'];
                                $tanggal = $row['tanggal'];
                                $gambar_base64 = $row['gambar_base64'];

                                $nama_barang = explode(",", $row['nama_barang']);
                                $unit = explode(",", $row['unit']);
                                $qty = explode(",", $row['qty']);
                                $penerima = explode(",", $row['penerima']);
                                $keterangan = explode(",", $row['keterangan']);
                            ?>
                                <tr>
                                    <td><?= $tanggal; ?></td>
                                    <td>
                                        <?php
                                        foreach ($nama_barang as $key => $barang) {
                                            echo ($key + 1) . ". " . $barang . "<br>";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        foreach ($unit as $item) {
                                            echo $item . "<br>";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        foreach ($qty as $item) {
                                            echo $item . "<br>";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        foreach ($penerima as $item) {
                                            echo $item . "<br>";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        foreach ($keterangan as $item) {
                                            echo $item . "<br>";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="#" class="gambar-modal-trigger" data-idpermintaan="<?= $idpermintaan; ?>">
                                            <img src="data:image/jpeg;base64,<?= $row['gambar_base64']; ?>" alt="Bukti Permintaan" style="max-width: 100px; max-height: 100px;">
                                        </a>
                                    </td>

                                    <?php if ($role == 'dev') : ?>
                                        <td>
                                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                                                Edit
                                            </button>
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idpermintaan; ?>">
                                                Delete
                                            </button>
                                        </td>
                                    <?php elseif ($role === 'gudang') : ?>
                                        <td>
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idpermintaan; ?>">
                                                Delete
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <!-- Modal untuk menampilkan gambar penuh -->
                                <div class="modal fade" id="gambarModal<?= $idpermintaan; ?>" tabindex="-1" role="dialog" aria-labelledby="gambarModalLabel" aria-hidden="true">
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
                                                <a href="download_gambar_keluar.php?id=<?= $idpermintaan; ?>&type=keluar" class="btn btn-primary" download>Download</a>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="edit<?= $idpermintaan; ?>">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="post" enctype="multipart/form-data" action="process_form_barangkeluar.php">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Ubah Barang Keluar</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <label for="gambar_base64">Bukti Keluar:</label>
                                                    <input type="file" name="gambar_base64" class="form-control-file" accept="image/*">
                                                    <br>
                                                    <button type="submit" class="btn btn-warning" name="updatebarangkeluar">Ubah Bukti</button>
                                                    <br>
                                                    <br>
                                                    <?php
                                                    $query_barang = "SELECT * FROM keluar WHERE idpermintaan = $idpermintaan";
                                                    $result_barang = mysqli_query($conn, $query_barang);
                                                    $nomor_barang = 1;
                                                    while ($row_barang = mysqli_fetch_assoc($result_barang)) {
                                                        $idbarang = $row_barang['idbarang'];
                                                        $idkeluar = $row_barang['idkeluar'];
                                                        $query_stock = "SELECT * FROM stock WHERE idbarang = $idbarang";
                                                        $result_stock = mysqli_query($conn, $query_stock);
                                                        $row_stock = mysqli_fetch_assoc($result_stock);
                                                        $namabarang = $row_stock['namabarang'];
                                                        $unit = $row_stock['unit'];
                                                        $qty = $row_barang['qty'];
                                                        $keterangan = $row_barang['keterangan'];
                                                        $penerima = $row_barang['penerima'];
                                                    ?>


                                                        <div class="barang" id="barang<?= $idkeluar; ?>">
                                                            <input type="hidden" name="idkeluar[]" value="<?= $idkeluar; ?>">
                                                            <input type="hidden" name="idbarang[]" value="<?= $idbarang; ?>" class="form-control" id="idbarang<?= $idkeluar; ?>">
                                                            <br>
                                                            <label for="namabarang<?= $idkeluar; ?>">Nama Barang <?= $nomor_barang; ?>:</label>
                                                            <input type="text" name="namabarang[]" value="<?= $namabarang; ?>" class="form-control" id="namabarang<?= $idkeluar; ?> " disabled>
                                                            <br>
                                                            <label for="unit<?= $idkeluar; ?>">Unit:</label>
                                                            <input type="text" name="unit[]" value="<?= $unit; ?>" class="form-control" id="unit<?= $idkeluar; ?>" disabled>
                                                            <br>
                                                            <label for="qty<?= $idkeluar; ?>">Jumlah:</label>
                                                            <input type="number" name="qty[]" value="<?= $qty; ?>" class="form-control" id="qtypermintaan<?= $idkeluar; ?>">
                                                            <br>
                                                            <label for="penerima<?= $idkeluar; ?>">Penerima:</label>
                                                            <input type="text" name="penerima[]" value="<?= $penerima; ?>" class="form-control" id="ket<?= $idkeluar; ?>">
                                                            <br>
                                                            <label for="ket<?= $idkeluar; ?>">Keterangan:</label>
                                                            <input type="text" name="ket[]" value="<?= $keterangan; ?>" class="form-control" id="ket<?= $idkeluar; ?>">
                                                            <br>
                                                            <button type="submit" class="btn btn-danger" name="deletebarangkeluar" value="<?= $idkeluar; ?>">Hapus</button>
                                                            <button type="submit" class="btn btn-warning" name="updatebarangkeluar" value="<?= $idkeluar; ?>">Ubah</button>
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

                </div>
                <!-- Modal tambah barang keluar edit -->
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
                                <form action="process_form_barangkeluar.php" method="POST">
                                    <input type="hidden" name="idpermintaan" value="<?= $idpermintaan; ?>">
                                    <div class="form-group">
                                        <!-- Formulir utama -->
                                        <label for="barangnya">Nama Barang:</label>
                                        <select name="barangnya" class="form-control">
                                            <?php
                                            $ambilsemuadatanya = mysqli_query($conn, "select * from stock");
                                            while ($fetcharray = mysqli_fetch_array($ambilsemuadatanya)) {
                                                $namabarang = $fetcharray['namabarang'];
                                                $idbarang = $fetcharray['idbarang'];

                                            ?>

                                                <option value="<?= $idbarang; ?>"><?= $namabarang; ?></option>

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
                                        <hr>
                                        <br>
                                        <div>
                                        </div>
                                        <button type="submit" class="btn btn-primary" name="barangbarukeluar">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div class="modal fade" id="delete<?= $idpermintaan; ?>">
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
            var counter = 2; // Mulai dari nomor 2
            // Hapus tombol hapus barang saat formulir utama dimuat
            $("#hapusBarangBtn").hide();

            // Tambahkan barang baru
            $("#addBarangBtn").click(function() {
                var newBarang = `
            <div id="barang${counter}">
            <label for="barangnya${counter}">Nama Barang:</label>
                <select name="barangnya[]" class="form-control">
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
                <label for="qty${counter}">Jumlah:</label>
                <input type="number" name="qty[]" placeholder="qty" class="form-control" required>
                <br>
                <label for="penerima${counter}">Penerima:</label>
                <input type="text" name="penerima[]" placeholder="Penerima" class="form-control" required>
                <br>
                <label for="keterangan${counter}">Keterangan:</label>
                <textarea name="keterangan[]" class="form-control" placeholder="Keterangan" rows="3" required></textarea>
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



<!-- The Modal "Filter Barang Keluar"-->
<div class="modal fade" id="filter">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Filter Barang Keluar</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data" action="barang_keluar.php">
                <div class="modal-body">
                    <i><b>*Perhatikan tanggal yang akan di filter</b></i>
                    <br>
                    <br>
                    <label for="start_date">Start Date:</label>
                    <input type="date" name="tgl_mulai" class="form-control">
                    <label for="end_date">End Date:</label>
                    <input type="date" name="tgl_selesai" class="form-control">
                    <br>
                    <button type="submit" name="filter_keluar" class="btn btn-info">Filter</button>
                </div>
            </form>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- The Modal Tambah-->
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
                    <label for="gambar_base64">Bukti Keluar:</label>
                    <input type="file" name="gambar_base64" class="form-control-file" required>
                    <p style="font-size: small; padding-top: 7px">Ukuran bukti maksimal 5 mb </p>
                    <label for="barangnya[]">Nama Barang:</label>
                    <select name="barangnya[]" class="form-control">
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
                    <label for="qty[]">Jumlah:</label>
                    <input type="number" name="qty[]" placeholder="qty" class="form-control" required>
                    <br>
                    <label for="penerima[]">Penerima:</label>
                    <input type="text" name="penerima[]" placeholder="Penerima" class="form-control" required>
                    <br>
                    <label for="keterangan[]">Keterangan:</label>
                    <textarea name="keterangan[]" class="form-control" placeholder="Keterangan" rows="3" required></textarea>
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

<!-- The Modal "Export"-->
<div class="modal fade" id="keluar">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Export Data Barang Keluar</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    Apakah Anda Yakin Ingin Mengexport Data Barang Keluar
                    <br>
                    <br>
                    <button type="submit" class="btn btn-outline-success" name="export_keluar">Export to Excel</button>
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