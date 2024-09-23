<?php
require '../../helper/function.php';
require '../../helper/cek.php';
require '../../vendor/autoload.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: ../../view/auth/login.php');
    exit();
}

if (!in_array($_SESSION['role'], ['superadmin', 'dev', 'gudang', 'user', 'supervisor', 'supervisoradmin', 'supervisorgudang'])) {
    header('Location: ../../access_denied.php');
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);


$laporanData = getLaporanData($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Laporan Gudang</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../../assets/img/icon.png">
    <link href="../../css/styles.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    <?php require_once '../../layout/_nav.php'; require_once '../../layout/_sidenav.php'; ?>
    <div class="container-fluid">
        <h1 class="mt-4">Laporan Gudang</h1>
        <div class="card mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID Barangg</th>
                                <th>Nama Barng</th>
                                <th>Jumlah Masuk</th>
                                <th>Jumlah Keluar</th>
                                <th>Stok</th>
                                <th>Tanggal Masuk Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($laporanData as $data) {
                                $idbarang = $data['idbarang'] ?? '';
                                $namabarang = $data['namabarang'] ?? '';
                                $qty_masuk = $data['qty_masuk'] ?? 0;
                                $qty_keluar = $data['qty_keluar'] ?? 0;
                                $stok = $data['stok'] ?? 0;
                                $tanggal_masuk = $data['tanggal_masuk'] ?? '';
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($idbarang, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($namabarang, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($qty_masuk, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($qty_keluar, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($stok, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?= htmlspecialchars($tanggal_masuk, ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="../../js/scripts.js"></script>
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script src="../../assets/demo/datatables-demo.js"></script>
</html>
