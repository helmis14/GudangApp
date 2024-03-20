<div id="layoutSidenav">
    <div id="layoutSidenav_content">
        <main>
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user' || $_SESSION['role'] === 'supervisor') { ?>
                                <a class="nav-link" href="../permintaan/permintaan.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                                    Permintaan Barang
                                </a>
                            <?php } ?>

                            <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user' || $_SESSION['role'] === 'gudang') { ?>
                                <a class="nav-link" href="../stock/stock.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div>
                                    Stock Barang
                                </a>
                            <?php } ?>

                            <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user' || $_SESSION['role'] === 'gudang') { ?>
                                <a class="nav-link" href="../masuk/barang_masuk.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-cart-plus"></i></div>
                                    Barang Masuk
                                </a>
                            <?php } ?>

                            <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user' || $_SESSION['role'] === 'gudang') { ?>
                                <a class="nav-link" href="../retur/retur.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-cart-arrow-down"></i></div>
                                    Retur Barang
                                </a>
                            <?php } ?>

                            <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user' || $_SESSION['role'] === 'gudang') { ?>
                                <a class="nav-link" href="../keluar/barang_keluar.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-box-open"></i></div>
                                    Barang Keluar
                                </a>
                            <?php } ?>

                            <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user') { ?>
                                <a class="nav-link" href="../admin/admin.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                                    Kelola Admin
                                </a>
                            <?php } ?>

                            <?php if ($_SESSION['role'] === 'superadmin' || $_SESSION['role'] === 'dev' || $_SESSION['role'] === 'user') { ?>
                                <a class="nav-link" href="../log/log.php">
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