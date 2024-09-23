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

    $year = isset($_POST['tahun']) ? $_POST['tahun'] : date('Y');
    $month = isset($_POST['bulan']) && $_POST['bulan'] != '' ? $_POST['bulan'] : null;

    $query = "SELECT 
            SUM(COALESCE(
                (SELECT SUM(m2.qty) 
                FROM masuk m2 
                WHERE m2.idbarang = s.idbarang
                AND m2.status = 1
                AND YEAR(m2.tanggal) = $year" . ($month ? " AND MONTH(m2.tanggal) = $month" : "") . "
                ), 0)) AS total_masuk,
            SUM(COALESCE(
                (SELECT SUM(k2.qty) 
                FROM keluar k2
                INNER JOIN permintaan_keluar pk ON k2.idpermintaan = pk.idpermintaan
                WHERE k2.idbarang = s.idbarang
                AND pk.status = 1
                AND pk.status2 = 1
                AND YEAR(pk.tanggal) = $year" . ($month ? " AND MONTH(pk.tanggal) = $month" : "") . "
                ), 0)) AS total_keluar,
            SUM(COALESCE(
                (SELECT SUM(bp.qtypermintaan) 
                FROM barang_permintaan bp 
                INNER JOIN permintaan p ON bp.idpermintaan = p.idpermintaan
                WHERE bp.namabarang = s.namabarang
                AND p.status = 1
                AND p.status2 = 1
                AND YEAR(p.tanggal) = $year" . ($month ? " AND MONTH(p.tanggal) = $month" : "") . "
                ), 0)) AS total_permintaan,
            SUM(s.stock) AS total_stock_gudang
        FROM stock s";

    $barangKeluarBelumDitanggapi = "
        SELECT COUNT(*) 
        FROM keluar k 
        INNER JOIN permintaan_keluar pk ON k.idpermintaan = pk.idpermintaan 
        WHERE pk.status = 0
    ";
    $resultKeluarBelumDitanggapi = mysqli_query($conn, $barangKeluarBelumDitanggapi);
    $keluarBelumDitanggapiCount = mysqli_fetch_assoc($resultKeluarBelumDitanggapi)['COUNT(*)'];

    $barangDalamPengiriman = "
        SELECT COUNT(*) 
        FROM permintaan 
        WHERE status = 1 AND status2 = 0
    ";
    $resultDalamPengiriman = mysqli_query($conn, $barangDalamPengiriman);
    $dalamPengirimanCount = mysqli_fetch_assoc($resultDalamPengiriman)['COUNT(*)'];

    $permintaanBelumDitanggapi = "
        SELECT COUNT(*) 
        FROM permintaan 
        WHERE status = 0
    ";
    $resultBelumDitanggapi = mysqli_query($conn, $permintaanBelumDitanggapi);
    $belumDitanggapiCount = mysqli_fetch_assoc($resultBelumDitanggapi)['COUNT(*)'];


    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    // Assign the data to variables
    $totalPermintaan = $data['total_permintaan'];
    $totalMasuk = $data['total_masuk'];
    $totalKeluar = $data['total_keluar'];
    $totalStockGudang = $data['total_stock_gudang'];


    $iduser = $_SESSION['iduser'];
    $role = $_SESSION['role'];
    $tanggalHariIni = date('l, d F Y');

    // Mengambil data untuk barang masuk
    $queryBarangMasuk = "
    SELECT MONTH(m2.tanggal) AS bulan, SUM(m2.qty) AS total_masuk 
    FROM masuk m2 
    WHERE m2.status = 1 AND YEAR(m2.tanggal) = $year" . ($month ? " AND MONTH(m2.tanggal) = $month" : "") . "
    GROUP BY MONTH(m2.tanggal)
";
    $resultBarangMasuk = mysqli_query($conn, $queryBarangMasuk);
    $dataBarangMasuk = [];
    while ($row = mysqli_fetch_assoc($resultBarangMasuk)) {
        $dataBarangMasuk[$row['bulan'] - 1] = $row['total_masuk']; // -1 untuk indexing bulan di Chart.js
    }

    // Mengambil data untuk barang keluar
    $queryBarangKeluar = "
    SELECT MONTH(pk.tanggal) AS bulan, SUM(k2.qty) AS total_keluar
    FROM keluar k2 
    INNER JOIN permintaan_keluar pk ON k2.idpermintaan = pk.idpermintaan 
    WHERE pk.status = 1 AND pk.status2 = 1 AND YEAR(pk.tanggal) = $year
    GROUP BY MONTH(pk.tanggal)
";
    $resultBarangKeluar = mysqli_query($conn, $queryBarangKeluar);
    $dataBarangKeluar = [];
    while ($row = mysqli_fetch_assoc($resultBarangKeluar)) {
        $dataBarangKeluar[$row['bulan'] - 1] = $row['total_keluar'];
    }

    // Mengambil data untuk permintaan
    $queryPermintaan = "
    SELECT MONTH(p.tanggal) AS bulan, SUM(bp.qtypermintaan) AS total_permintaan 
    FROM barang_permintaan bp 
    INNER JOIN permintaan p ON bp.idpermintaan = p.idpermintaan 
    WHERE p.status = 1 AND p.status2 = 1 AND YEAR(p.tanggal) = $year
    GROUP BY MONTH(p.tanggal)
";
    $resultPermintaan = mysqli_query($conn, $queryPermintaan);
    $dataPermintaan = [];
    while ($row = mysqli_fetch_assoc($resultPermintaan)) {
        $dataPermintaan[$row['bulan'] - 1] = $row['total_permintaan'];
    }

    // Mengatur agar semua bulan terisi 0 jika tidak ada data
    $finalDataBarangMasuk = array_fill(0, 12, 0);
    $finalDataBarangKeluar = array_fill(0, 12, 0);
    $finalDataPermintaan = array_fill(0, 12, 0);

    foreach ($dataBarangMasuk as $bulan => $total) {
        $finalDataBarangMasuk[$bulan] = $total;
    }
    foreach ($dataBarangKeluar as $bulan => $total) {
        $finalDataBarangKeluar[$bulan] = $total;
    }
    foreach ($dataPermintaan as $bulan => $total) {
        $finalDataPermintaan[$bulan] = $total;
    }

    ?>


    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Dashboard Gudang</title>
        <link rel="icon" type="image/png" sizes="16x16" href="../../assets/img/icon.png">
        <link href="../../css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script> <!-- Load jQuery here -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>

    <body class="sb-nav-fixed">
        <?php require_once '../../layout/_nav.php'; ?>
        <?php require_once '../../layout/_sidenav.php'; ?>

        <div class="container-fluid">
            <!-- Header dan Tanggal -->
            <div class="d-flex justify-content-between mt-4">
                <h1 class="mt-4">Dashboard Gudang</h1>
                <div class="align-self-center">
                    <span><?= $tanggalHariIni ?></span>
                </div>
            </div>

            <!-- Statistik Utama -->
            <div class="row mt-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Permintaan Barang</h5>
                            <h3><?= number_format($totalPermintaan) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Barang Masuk</h5>
                            <h3><?= number_format($totalMasuk) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-danger mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Barang Keluar</h5>
                            <h3><?= number_format($totalKeluar) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Total Barang di Gudang</h5>
                            <h3><?= number_format($totalStockGudang) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Permintaan Belum Di Tanggapi</h5>
                            <h3><?= number_format($belumDitanggapiCount) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-secondary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Barang Dalam Pengiriman</h5>
                            <h3><?= number_format($dalamPengirimanCount) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Barang Keluar Belum Ditanggapi</h5>
                            <h3><?= number_format($keluarBelumDitanggapiCount) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-white bg-dark mb-3">
                        <div class="card-body">
                            <form method="POST" action="" class="mb-4">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="tahun">Tahun</label>
                                        <select id="tahun" name="tahun" class="form-control">
                                            <?php
                                            $currentYear = date('Y');
                                            for ($i = 2020; $i <= $currentYear; $i++) {
                                                echo "<option value='$i'" . ($year == $i ? ' selected' : '') . ">$i</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="bulan">Bulan (Optional)</label>
                                        <select id="bulan" name="bulan" class="form-control">
                                            <option value="">Semua Bulan</option>
                                            <?php
                                            $months = [
                                                1 => 'Januari',
                                                2 => 'Februari',
                                                3 => 'Maret',
                                                4 => 'April',
                                                5 => 'Mei',
                                                6 => 'Juni',
                                                7 => 'Juli',
                                                8 => 'Agustus',
                                                9 => 'September',
                                                10 => 'Oktober',
                                                11 => 'November',
                                                12 => 'Desember'
                                            ];
                                            foreach ($months as $key => $monthName) {
                                                echo "<option value='$key'" . ($month == $key ? ' selected' : '') . ">$monthName</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>


            <!-- Chart Barang -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">Total Barang Masuk</div>
                        <div class="card-body">
                            <canvas id="chartBarangMasuk"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">Total Barang Keluar</div>
                        <div class="card-body">
                            <canvas id="chartBarangKeluar"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">Total Permintaan</div>
                        <div class="card-body">
                            <canvas id="chartTotalPermintaan"></canvas>
                        </div>
                    </div>
                </div>
            </div>




        </div>


        <script>
            // Data dari PHP
            const dataMasuk = <?= json_encode(array_values($finalDataBarangMasuk)) ?>;
            const dataKeluar = <?= json_encode(array_values($finalDataBarangKeluar)) ?>;
            const dataPermintaan = <?= json_encode(array_values($finalDataPermintaan)) ?>;

            const labels = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            // Membuat chart secara manual
            const ctxBarangMasuk = document.getElementById('chartBarangMasuk').getContext('2d');
            const chartBarangMasuk = new Chart(ctxBarangMasuk, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Barang Masuk',
                        data: dataMasuk,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            const ctxBarangKeluar = document.getElementById('chartBarangKeluar').getContext('2d');
            const chartBarangKeluar = new Chart(ctxBarangKeluar, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Barang Keluar',
                        data: dataKeluar,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            const ctxPermintaan = document.getElementById('chartTotalPermintaan').getContext('2d');
            const chartPermintaan = new Chart(ctxPermintaan, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Permintaan',
                        data: dataPermintaan,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../../js/scripts.js"></script>
    </body>

    </html>