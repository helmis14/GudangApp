<div id="layoutSidenav">
    <div id="layoutSidenav_content">
        <main>
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <?php
                            $role = $_SESSION['role'];
                            $menuItems = [
                                'Dashboard' => [
                                    'roles' => ['superadmin', 'dev', 'user', 'gudang', 'supervisorgudang', 'supervisor', 'supervisoradmin'],
                                    'url' => '../dashboard/dashboard.php',
                                    'icon' => 'fas fa-chart-line',
                                    'label' => 'Dashboard'
                                ],
                                'permintaan' => [
                                    'roles' => ['superadmin', 'dev', 'user', 'supervisor', 'supervisoradmin', 'gudang'],
                                    'url' => '../permintaan/permintaan.php',
                                    'icon' => 'fas fa-clipboard-list',
                                    'label' => 'Permintaan Barang'
                                ],
                                'stock' => [
                                    'roles' => ['superadmin', 'dev', 'user', 'gudang', 'supervisor', 'supervisorgudang', 'supervisoradmin'],
                                    'url' => '../stock/stock.php',
                                    'icon' => 'fas fa-boxes',
                                    'label' => 'Stock Barang'
                                ],
                                'barang_masuk' => [
                                    'roles' => ['superadmin', 'dev', 'user', 'gudang', 'supervisoradmin'],
                                    'url' => '../masuk/barang_masuk.php',
                                    'icon' => 'fas fa-cart-plus',
                                    'label' => 'Barang Masuk'
                                ],
                                'barang_keluar' => [
                                    'roles' => ['superadmin', 'dev', 'user', 'gudang', 'supervisorgudang', 'supervisor', 'supervisoradmin'],
                                    'url' => '../keluar/barang_keluar.php',
                                    'icon' => 'fas fa-box-open',
                                    'label' => 'Barang Keluar'
                                ],
                                'laporan' => [
                                    'roles' => ['superadmin', 'dev', 'user', 'gudang', 'supervisorgudang', 'supervisor', 'supervisoradmin'],
                                    'url' => '../laporan/mutasi.php',
                                    'icon' => 'fas fa-scroll',
                                    'label' => 'Laporan'
                                ],
                                'kelola_admin' => [
                                    'roles' => ['superadmin', 'dev', 'user'],
                                    'url' => '../admin/admin.php',
                                    'icon' => 'fas fa-users',
                                    'label' => 'Kelola Admin'
                                ],
                                'log_aktivitas' => [
                                    'roles' => ['superadmin', 'dev', 'user', 'supervisoradmin'],
                                    'url' => '../log/log.php',
                                    'icon' => 'fas fa-walking',
                                    'label' => 'Log Aktivitas'
                                ]
                            ];

                            foreach ($menuItems as $item) {
                                if (in_array($role, $item['roles'])) {
                                    echo '<a class="nav-link" href="' . $item['url'] . '">
                                            <div class="sb-nav-link-icon"><i class="' . $item['icon'] . '"></i></div>
                                            ' . $item['label'] . '
                                          </a>';
                                }
                            }
                            ?>
                            <a class="nav-link" href="#" data-toggle="modal" data-target="#logoutModal">
                                <div class="sb-nav-link-icon"><i class="fas fa-power-off"></i></div>
                                Logout
                            </a>
                        </div>
                    </div>
                </nav>
            </div>