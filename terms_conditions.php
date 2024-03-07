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
    <meta name="description" content="About Us - IT Roheda Group" />
    <meta name="author" content="" />
    <title>About Us - IT Roheda Group</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
    <style>
        .team-section {

            padding: 40px 0;
        }

        .team-section h1 {
            text-align: center;
            margin-bottom: 50px;
            font-size: 36px;
        }

        .team-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .about-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .team-card img {
            margin-bottom: 20px;
        }

        .team-card h3 {
            margin-bottom: 10px;
            font-size: 24px;
        }
    </style>
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
                <div class="container team-section">
                    <h1>Meet Our Team Developer</h1>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="team-card">
                                <img src="./assets/img/team.jpg" alt="Team Member 1" class="img-fluid">
                                <h3>Windu Dwima Putra</h3>
                                <p>Fullstack Developer</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="team-card">
                                <img src="./assets/img/team.jpg" alt="Team Member 2" class="img-fluid">
                                <h3>Christian Andrea</h3>
                                <p>Project Manager</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="team-card">
                                <img src="./assets/img/team.jpg" alt="Team Member 3" class="img-fluid">
                                <h3>Helmi Sulaeman</h3>
                                <p>Fullstack Developer</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container team-section">
                    <h1>About Us</h1>
                    <div class="row">
                        <div class="about-card">
                            <p>Divisi IT Roheda Group adalah sebuah tim divisi yang bergerak di bidang teknologi informasi. Kami didirikan dengan visi untuk memberikan solusi teknologi terbaik bagi perusahaan kami. Kami memiliki tim yang terdiri dari profesional ahli dalam berbagai bidang teknologi, siap membantu perusahaan mencapai tujuan bisnis dengan solusi yang inovatif dan efektif. Kami sangat menjunjung tinggi nilai kekeluargaan dan kerja keras. Bersama-sama, kami berkomitmen untuk memberikan layanan terbaik kepada perusahaan kami.</p>
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