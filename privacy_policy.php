<?php
require 'function.php';
require 'cek.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
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
    <meta name="description" content="Privacy Policy - IT Roheda Group" />
    <meta name="author" content="" />
    <title>Privacy Policy - IT Roheda Group</title>
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

                    </div>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid">
                    <h1 class="mt-4">Privacy Policy</h1>
                    <div class="card mb-4">
                        <div class="card-header">
                            Kebijakan Privasi
                        </div>
                        <div class="card-body">
                            <p>Kebijakan Privasi IT Roheda Group menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi pribadi yang Anda berikan kepada kami saat menggunakan layanan kami.</p>
                            <p>Informasi pribadi yang kami kumpulkan mungkin termasuk, namun tidak terbatas pada, nama, alamat email, alamat pos, nomor telepon, dan informasi lainnya yang diperlukan untuk memberikan layanan kami.</p>
                            <p>Kami menggunakan informasi pribadi Anda hanya untuk menyediakan dan meningkatkan layanan kami. Dengan menggunakan layanan kami, Anda menyetujui pengumpulan dan penggunaan informasi ini sesuai dengan kebijakan privasi kami.</p>
                            <p>Kami tidak akan menyewakan atau menjual informasi pribadi Anda kepada pihak ketiga tanpa izin Anda, kecuali diperlukan oleh hukum.</p>
                            <p>Kebijakan Privasi ini dapat berubah dari waktu ke waktu. Kami akan menginformasikan kepada Anda tentang perubahan dengan memperbarui halaman ini. Anda disarankan untuk meninjau Kebijakan Privasi kami secara berkala untuk memahami bagaimana kami melindungi informasi pribadi Anda.</p>
                        </div>
                    </div>
                </div>
            </main>
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

</html>