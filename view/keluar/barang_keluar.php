<?php
require '../../helper/function.php';
require '../../helper/cek.php';


if (!isset($_SESSION['iduser'])) {
    header('Location: ../../login.php');
    exit();
}

if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev'  && $_SESSION['role'] !== 'gudang' && $_SESSION['role'] !== 'user' && $_SESSION['role'] !== 'supervisorgudang' && $_SESSION['role'] !== 'supervisor' && $_SESSION['role'] !== 'supervisoradmin') {
    header('Location: ../../access_denied.php');
    exit();
}
if (isset($_SESSION['error_message'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
    unset($_SESSION['error_message']);
}

$iduser = $_SESSION['iduser'];
$role = $_SESSION['role'];
$email_logged = $_SESSION['email'];
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
            <div class="card-header">
                <?php if ($role === 'gudang' || $role === 'dev' || $role === 'supervisorgudang' || $role === 'supervisor') :  ?>
                    <!-- Button to Open the Modal -->
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                        Tambah Barang
                    </button>
                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filter">
                        Cari Permintaan Keluar
                    </button>
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#keluar">
                        Export to Excel
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#barang">
                    Cari Barang
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-left" id="dataTable" width="100%" cellspacing="0">
                        <?php
                        $query = "SELECT 
                                        keluar.idpermintaan, 
                                        permintaan_keluar.tanggal, 
                                        permintaan_keluar.status,
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
                            <tr style="background-color:#F0EEED">
                                <th style="text-align:center">Tanggal</th>
                                <th style="text-align:center">Nama Barang</th>
                                <th style="text-align:center">Unit</th>
                                <th style="text-align:center">Jumlah</th>
                                <th style="text-align:center">Penerima</th>
                                <th style="text-align:center">Keterangan</th>
                                <th style="text-align:center; width:100px;">Bukti WO</th>
                                <th style="text-align:center; width:150px;">Bukti Keluar</th>
                                <th style="text-align:center">Status</th>
                                <?php if ($role === 'gudang' || $role === 'dev' || $role === 'supervisorgudang' || $role === 'supervisor' || $role === 'superadmin' || $role === 'supervisoradmin') :  ?>
                                    <th style="text-align:center">Aksi</th>
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
                                    $query = "SELECT keluar.idpermintaan, permintaan_keluar.tanggal, permintaan_keluar.status, permintaan_keluar.status2, permintaan_keluar.gambar_base64, permintaan_keluar.bukti_wo,
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
                                    $result = mysqli_query($conn, "SELECT keluar.idpermintaan, permintaan_keluar.tanggal, permintaan_keluar.status,  permintaan_keluar.status2, permintaan_keluar.gambar_base64, permintaan_keluar.bukti_wo,
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
                                $result = mysqli_query($conn, "SELECT keluar.idpermintaan, permintaan_keluar.tanggal, permintaan_keluar.status, permintaan_keluar.status2, permintaan_keluar.gambar_base64, permintaan_keluar.bukti_wo,
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
                                $bukti_wo = $row['bukti_wo'];
                                $status = $row['status'];
                                $status2 = $row['status2'];

                                $nama_barang = explode(",", $row['nama_barang']);
                                $unit = explode(",", $row['unit']);
                                $qty = explode(",", $row['qty']);
                                $penerima = explode(",", $row['penerima']);
                                $keterangan = explode(",", $row['keterangan']);
                            ?>
                                <tr>
                                    <td style="text-align:center; width:130px"><?= $tanggal; ?></td>
                                    <td style="width:300px">
                                        <?php
                                        foreach ($nama_barang as $key => $barang) {
                                            echo ($key + 1) . ". " . $barang . "<br>";
                                        }
                                        ?>
                                    </td>
                                    <td style="text-align:center">
                                        <?php
                                        foreach ($unit as $item) {
                                            echo $item . "<br>";
                                        }
                                        ?>
                                    </td>
                                    <td style="text-align:center">
                                        <?php
                                        foreach ($qty as $item) {
                                            echo $item . "<br>";
                                        }
                                        ?>
                                    </td>
                                    <td style="text-align:center">
                                        <?php
                                        foreach ($penerima as $item) {
                                            echo $item . "<br>";
                                        }
                                        ?>
                                    </td>

                                    <td style="width:300px">
                                        <?php
                                        foreach ($keterangan as $item) {
                                            echo $item . "<br>";
                                        }
                                        ?>
                                    </td>

                                    <td style="text-align:center">
                                        <button type="button" class="btn btn-success gambar-modal-trigger" data-idpermintaan="<?= $idpermintaan; ?>" data-toggle="modal" data-target="#gambarModal<?= $idpermintaan; ?>">
                                            Lihat
                                        </button>

                                    </td>
                                    <td style="text-align:center">
                                        <button type="button" class="btn btn-success wo-modal-trigger" data-idpermintaan="<?= $idpermintaan; ?>" data-toggle="modal" data-target="#WoModal<?= $idpermintaan; ?>">
                                            Lihat
                                        </button>
                                    </td>

                                    <td>
                                        <?php
                                        $allApproved = ($status == 1 && $status2 == 1);
                                        $allRejected = ($status == 2 && $status2 == 2);
                                        ?>
                                        <?php if ($allApproved) : ?>
                                            Disetujui
                                        <?php elseif ($allRejected) : ?>
                                            Ditolak
                                        <?php else : ?>
                                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#viewStatusModal<?= $idpermintaan; ?>">
                                                Lihat Status
                                            </button>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($role === 'superadmin') : ?>
                                            <?php if ($status == 0) : ?>
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idpermintaan; ?>">
                                                    Ubah Status
                                                </button>
                                            <?php else : ?>
                                                Ditanggapi
                                            <?php endif; ?>
                                        <?php elseif ($role === 'gudang') : ?>
                                            <?php if ($status == 1) : ?>
                                                <?php if ($status2 == 1) : ?>
                                                    Ditanggapi
                                                <?php else : ?>
                                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idpermintaan; ?>">
                                                        Ubah Status
                                                    </button>
                                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                                                        Edit
                                                    </button>
                                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idpermintaan; ?>">
                                                        Delete
                                                    </button>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <p>Belum Ditanggapi</p>
                                                <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                                                    Edit
                                                </button>
                                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idpermintaan; ?>">
                                                    Delete
                                                </button>
                                            <?php endif; ?>
                                        <?php elseif ($role === 'dev') : ?>
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idpermintaan; ?>">
                                                Ubah Status
                                            </button>
                                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idpermintaan; ?>">
                                                Edit
                                            </button>
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idpermintaan; ?>">
                                                Delete
                                            </button>
                                        <?php else : ?>
                                            Belum Ditanggapi
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Modal untuk menampilkan gambar penuh WO -->
                                <div class="modal fade" id="gambarModal<?= $idpermintaan; ?>" tabindex="-1" role="dialog" aria-labelledby="gambarModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="gambarModalLabel">Bukti WO</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <img src="data:image/jpeg;base64,<?= $gambar_base64; ?>" class="img-fluid" alt="Bukti WO Belum Di Upload">
                                            </div>
                                            <div class="modal-footer">
                                                <a href="download_gambar_keluar.php?id=<?= $idpermintaan; ?>&type=wo" class="btn btn-primary" download>Download</a>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal untuk menampilkan gambar penuh Keluar-->
                                <div class="modal fade" id="WoModal<?= $idpermintaan; ?>" tabindex="-1" role="dialog" aria-labelledby="gambarModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="gambarModalLabel">Bukti Keluar</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <img src="data:image/jpeg;base64,<?= $bukti_wo; ?>" class="img-fluid" alt="Bukti Keluar Belum Di Upload">
                                            </div>
                                            <div class="modal-footer">
                                                <a href="download_bukti_wo.php?id=<?= $idpermintaan; ?>&type=keluar" class="btn btn-primary" download>Download</a>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal untuk melihat status permintaan -->
                                <div class="modal fade" id="viewStatusModal<?= $idpermintaan; ?>" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="viewStatusModalLabel">Status Permintaan</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <p>BM atau CE
                                                    <span class="<?= ($status == 0) ? 'text-warning' : ($status == 1 ? 'text-success' : 'text-danger'); ?>">
                                                        <?= ($status == 0) ? 'belum menanggapi' : ($status == 1 ? 'menyetujui' : 'tidak menyetujui'); ?>
                                                    </span>
                                                </p>
                                                <hr>
                                                <p>Gudang
                                                    <span class="<?= ($status2 == 0) ? 'text-warning' : ($status2 == 1 ? 'text-success' : 'text-danger'); ?>">
                                                        <?= ($status2 == 0) ? 'belum menanggapi' : ($status2 == 1 ? 'menyetujui' : 'tidak menyetujui'); ?>
                                                    </span>
                                                </p>
                                                <hr>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if (isset($_SESSION['error_message'])) : ?>
                                    <div class="alert alert-danger" role="alert">
                                        <?= $_SESSION['error_message']; ?>
                                    </div>
                                    <?php unset($_SESSION['error_message']); // Hapus pesan kesalahan setelah ditampilkan 
                                    ?>
                                <?php endif; ?>

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
                                                    <?php if ($role === 'superadmin') : ?>
                                                        <div class="form-group">
                                                            <label for="status">Status:</label>
                                                            <select class="form-control" id="status" name="status">
                                                                <option value="1">Disetujui</option>
                                                                <option value="2">Tidak Disetujui</option>
                                                            </select>
                                                        </div>
                                                    <?php elseif ($role === 'gudang') : ?>
                                                        <div class="form-group">
                                                            <label for="status2">Status Gudang:</label>
                                                            <select class="form-control" id="status2" name="status2">
                                                                <option value="1">Disetujui</option>
                                                                <option value="2">Tidak Disetujui</option>
                                                            </select>
                                                        </div>
                                                    <?php endif; ?>
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
                                            <form method="post" enctype="multipart/form-data" action="process_form_barangkeluar.php">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Ubah Barang Keluar</h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <label for="gambar_base64">Bukti WO:</label>
                                                    <input type="file" name="gambar_base64" class="form-control-file" accept="image/*">
                                                    <br>
                                                    <label for="bukti_wo">Bukti Keluar:</label>
                                                    <input type="file" name="bukti_wo" class="form-control-file" accept="image/*">
                                                    <br>
                                                    <button type="submit" class="btn btn-warning" name="updatebarangkeluar">Upload</button>
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

                                            $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM stock WHERE stock > 0");
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

                        $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM stock WHERE stock > 0");
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
    <script>
        $(document).ready(function() {
            $('.wo-modal-trigger').click(function() {
                var idPermintaan = $(this).data('idpermintaan');
                $('#WoModal' + idPermintaan).modal('show');
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
                    <label for="gambar_base64">Bukti WO:</label>
                    <input type="file" name="gambar_base64" class="form-control-file" required>
                    <p style="font-size: small; padding-top: 7px">Ukuran bukti maksimal 5 mb </p>
                    <label for="search-barang">Cari Nama Barang:</label>
                    <input type="text" id="search-barang" class="form-control" placeholder="Cari Nama Barang">
                    <label for="barangnya[]">Pilih Nama Barang:</label>
                    <select id="barang-select" name="barangnya[]" class="form-control">
                        <?php
                        $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM stock WHERE stock > 0");
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

<!-- The Modal Search Baru-->
<div class="modal fade" id="barang">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <form id="filterForm">
                    <div class="form-row">
                        <div class="col">
                            <label for="tgl_mulai">Start Date:</label>
                            <input type="date" name="tgl_mulai" class="form-control" id="tgl_mulai">
                        </div>
                        <div class="col">
                            <label for="tgl_selesai">End Date:</label>
                            <input type="date" name="tgl_selesai" class="form-control" id="tgl_selesai">
                        </div>
                        <div class="col">
                            <label for="nama_barang">Nama Barang:</label>
                            <input type="text" name="nama_barang" class="form-control" id="nama_barang">
                        </div>
                        <div class="col">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-info btn-block">Filter</button>
                        </div>
                    </div>
                </form>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Filter Form -->

            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center" id="dataTableSearch" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah</th>
                                    <th>Unit</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="dataTableBody">
                                <!-- Data akan dimuat di sini menggunakan AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.getElementById('search-barang').addEventListener('input', function() {
            var searchTerm = this.value.toLowerCase();
            var selects = document.querySelectorAll('select[name="barangnya[]"]');

            selects.forEach(select => {
                var options = select.options;

                for (var i = 0; i < options.length; i++) {
                    var optionText = options[i].text.toLowerCase();
                    if (optionText.includes(searchTerm)) {
                        options[i].style.display = 'block';
                    } else {
                        options[i].style.display = 'none';
                    }
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#dataTableSearch').DataTable();
        });
    </script>

    <script>
        $(document).ready(function() {
            function loadData(tgl_mulai = '', tgl_selesai = '', nama_barang = '') {
                $.ajax({
                    url: 'filter_barang.php',
                    method: 'POST',
                    data: {
                        tgl_mulai: tgl_mulai,
                        tgl_selesai: tgl_selesai,
                        nama_barang: nama_barang
                    },
                    success: function(response) {
                       
                        $('#dataTableBody').html(response);
                        $('#dataTableSearch').DataTable();
                    }
                });
            }

            // Load data from localStorage if exists
            var savedFilter = JSON.parse(localStorage.getItem('filterData'));
            if (savedFilter) {
                $('#tgl_mulai').val(savedFilter.tgl_mulai);
                $('#tgl_selesai').val(savedFilter.tgl_selesai);
                $('#nama_barang').val(savedFilter.nama_barang);
                loadData(savedFilter.tgl_mulai, savedFilter.tgl_selesai, savedFilter.nama_barang);
            } else {
                loadData();
            }

            // Filter data on form submission
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();

                var tgl_mulai = $('#tgl_mulai').val();
                var tgl_selesai = $('#tgl_selesai').val();
                var nama_barang = $('#nama_barang').val();

            

                // Save filter data to localStorage
                var filterData = {
                    tgl_mulai: tgl_mulai,
                    tgl_selesai: tgl_selesai,
                    nama_barang: nama_barang
                };
                localStorage.setItem('filterData', JSON.stringify(filterData));

                loadData(tgl_mulai, tgl_selesai, nama_barang);
            });

            // Clear filter data on modal close
            $('#barang').on('hidden.bs.modal', function() {
                localStorage.removeItem('filterData');
            });

            // Clear filter data on close button click
            $('.close').on('click', function() {
                localStorage.removeItem('filterData');
            });
        });
    </script>


</html>