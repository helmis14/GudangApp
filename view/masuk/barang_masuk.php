<?php
require '../../helper/function.php';
require '../../helper/cek.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: ../../login.php');
    exit();
}

if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'dev' && $_SESSION['role'] !== 'gudang' && $_SESSION['role'] !== 'user' && $_SESSION['role'] !== 'supervisoradmin') {
    header('Location: ../../access_denied.php');
    exit();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalDataQuery = "SELECT COUNT(*) AS total FROM masuk m 
                    JOIN stock s ON s.idbarang = m.idbarang";
$totalDataResult = mysqli_query($conn, $totalDataQuery);
$totalData = mysqli_fetch_assoc($totalDataResult)['total'];
$totalPages = ceil($totalData / $limit);

$ambilsemuadatastock = mysqli_query($conn, "SELECT * FROM masuk m 
                            JOIN stock s ON s.idbarang = m.idbarang 
                            ORDER BY m.tanggal DESC
                            LIMIT $limit OFFSET $offset");
$currentRange = 2;

$startRange = max(1, $page - $currentRange);
$endRange = min($totalPages, $page + $currentRange);

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
    <title>Barang Masuk</title>
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
        <h1 class="mt-4">Barang Masuk </h1>
        <div class="card mb-4">
            <?php if ($role === 'gudang' || $role === 'dev') :  ?>
                <div class="card-header d-flex align-items-center">
                    <div class="p-2">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                            Tambah Barang
                        </button>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#filtermasuk">
                            Cari Permintaan
                        </button>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#masuk">
                            Export to Excel
                        </button>
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#barang">
                            Cari Barang
                        </button>
                    </div>

                     <div class="p-2 ml-auto">
                        <div class="input-group">
                            <input class="form-control" type="text" id="search-input" placeholder="Cari Barang" aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-danger" id="cancel-search" type="button" style="display: none;">
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
                                <div id="loading" style="display:none;text-align:center;">
                                    <p> <img src="../../assets/gif/loading.gif" alt="Loading..." /></p>
                                </div>
                                <th>Tanggal</th>
                                <th>Nama Barang</th>
                                <th>Unit</th>
                                <th>Jumlah</th>
                                <th>distributor</th>
                                <th>Penerima</th>
                                <th>Keterangan</th>
                                <th>Bukti</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($data = mysqli_fetch_array($ambilsemuadatastock)) {
                                $idb = $data['idbarang'];
                                $idm = $data['idmasuk'];
                                $tanggal = $data['tanggal'];
                                $namabarang = $data['namabarang'];
                                $qty = $data['qty'];
                                $keterangan = $data['keterangan'];
                                $penerima = $data['penerima'];
                                $unit = $data['unit'];
                                $distributor = $data['distributor'];
                                $bukti_masuk_base64 = $data['bukti_masuk_base64'];
                                $status = $data['status'];

                            ?>
                                <tr>
                                    <td><?= $tanggal; ?></td>
                                    <td><?= $namabarang; ?></td>
                                    <td><?= $unit; ?></td>
                                    <td><?= $qty; ?></td>
                                    <td><?= $distributor; ?></td>
                                    <td><?= $penerima; ?></td>
                                    <td><?= $keterangan; ?></td>
                                    <td>
                                        <a href="#" class="gambar-mini-trigger" data-toggle="modal" data-target="#gambarModal<?= $idm; ?>" data-id="<?= $idm; ?>">
                                            <button type="button" class="btn btn-success"">Lihat Bukti</button>
                                        </a>
                                    </td>
                                    <td><?= ($status == 0) ? 'Dalam Pengiriman' : ($status == 1 ? 'Diterima' : 'Tidak Diterima'); ?></td>

                                    <?php if ($_SESSION['role'] === 'dev') { ?>
                                        <td>
                                            <button type=" button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idm; ?>">
                                                Edit
                                            </button>
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#delete<?= $idb; ?>">
                                                Delete
                                            </button>
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idm; ?>">
                                                Status
                                            </button>
                                    </td>
                                <?php } elseif ($_SESSION['role'] === 'gudang' && $status == 0) { ?>
                                    <td>
                                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#edit<?= $idm; ?>">
                                            Edit
                                        </button>
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#statusModal<?= $idm; ?>">
                                            Status
                                        </button>
                                    </td>
                                <?php } elseif ($status == 1 || $status == 2) { ?>
                                    <td>Ditanggapi</td>
                                <?php } else { ?>
                                    <td>Belum Ditanggapi</td>
                                <?php } ?>

                                </td>
                                </tr>

                                <!-- Modal untuk mengubah status barang-->
                                <div class="modal fade" id="statusModal<?= $idm; ?>" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="statusModalLabel">Ubah Status Permintaan</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="update_status_masuk.php" method="POST">
                                                    <input type="hidden" name="idm" value="<?= $idm; ?>">
                                                    <div class="form-group">
                                                        <label for="status">Status Barang:</label>
                                                        <select class="form-control" id="status" name="status">
                                                            <option value="0">Dalam Pengiriman</option>
                                                            <option value="1">Diterima</option>
                                                            <option value="2">Tidak Diterima</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal untuk menampilkan gambar penuh -->
                                <div class="modal fade" id="gambarModal<?= $idm; ?>" tabindex="-1" role="dialog" aria-labelledby="gambarModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="gambarModalLabel">Bukti Masuk</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <img src="data:image/jpeg;base64,<?= $bukti_masuk_base64; ?>" class="img-fluid" alt="Bukti Masuk Belum Di Upload">
                                            </div>
                                            <div class="modal-footer">
                                                <a href="download_gambar_masuk.php?id=<?= $idm; ?>&type=keluar" class="btn btn-primary" download>Download</a>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <!-- Edit Modal Baru -->
                                <div class="modal fade" id="edit<?= $idm; ?>">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">

                                            <!-- Modal Header -->
                                            <div class="modal-header">
                                                <h4 class="modal-title">Edit Barang</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>

                                            <!-- Modal body -->
                                            <form method="post" enctype="multipart/form-data">
                                                <div class="modal-body">
                                                    <label for="penerima">Penerima</label>
                                                    <input type="text" name="penerima" value="<?= $penerima; ?>" class="form-control">
                                                    <br>
                                                    <label for="qty">Jumlah:</label>
                                                    <input type="number" name="qty" value="<?= $qty; ?>" class="form-control">
                                                    <label for="keterangan">keterangan:</label>
                                                    <input type="text" name="keterangan" value="<?= $keterangan; ?>" class="form-control">
                                                    <br>
                                                    <label for="distributor">Distributor:</label>
                                                    <input type="text" name="distributor" value="<?= $distributor; ?>" class="form-control">
                                                    <br>
                                                    <label for="update_bukti_masuk">Bukti Masuk:</label>
                                                    <input type="file" name="update_bukti_masuk" class="form-control-file" accept="image/*">
                                                    <br>
                                                    <input type="hidden" name="idb" value="<?= $idb; ?>">
                                                    <input type="hidden" name="idm" value="<?= $idm; ?>">
                                                    <button type="submit" class="btn btn-primary" name="updatebarangmasuk">Submit</button>
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
                                    <input type="hidden" name="qty" value="<?= $qty; ?>">
                                    <input type="hidden" name="idm" value="<?= $idm; ?>">
                                    <br>
                                    <br>
                                    <button type="submit" class="btn btn-danger" name="hapusbarangmasuk">Hapus</button>
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
        <div style="text-align:center" id="loadingSpinner" style="display: none;">
            <img src="../../assets/gif/loading.gif" alt="Loading..." />
        </div>
        <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <!-- Tombol First -->
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=1" aria-label="First">
                            <span aria-hidden="true">« Awal</span>
                        </a>
                    </li>

                    <!-- Tombol Previous -->
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= max(1, $page - 1); ?>" aria-label="Previous">
                            <span aria-hidden="true">Sebelumnya</span>
                        </a>
                    </li>

                    <!-- Halaman yang ditampilkan dalam rentang -->
                    <?php for ($i = $startRange; $i <= $endRange; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Tombol Next -->
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= min($totalPages, $page + 1); ?>" aria-label="Next">
                            <span aria-hidden="true">Selanjutnya ›</span>
                        </a>
                    </li>

                    <!-- Tombol Last -->
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
    </div>
    </div>
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

                // Jika ada pencarian, sembunyikan pagination dan tampilkan tombol cancel
                if (search !== '') {
                    $('#cancel-search').show();
                    $('.pagination').hide(); // Sembunyikan pagination saat pencarian
                } else {
                    $('#cancel-search').hide();
                    $('.pagination').show(); // Tampilkan pagination jika tidak ada pencarian
                }

                $.ajax({
                    url: 'search_datamasuk.php',
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

            // Fungsi untuk cancel search: kembali ke halaman pertama tanpa filter pencarian
            $('#cancel-search').on('click', function() {
                $('#search-input').val(''); // Kosongkan input
                $(this).hide(); // Sembunyikan tombol cancel
                $('.pagination').show(); // Tampilkan kembali pagination

                // Arahkan ke halaman 1 tanpa filter pencarian
                window.location.href = "?page=1";
            });

            // Debounce untuk pencarian agar tidak menembak server terus menerus
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


    <script>
        $(document).ready(function() {
            $('.gambar-mini-trigger').click(function() {
                var id = $(this).data('id');
                $('#gambarModal' + id).modal('show');
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#filterBtn').click(function() {
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();

                $.ajax({
                    url: 'filter_permintaan_masuk.php', // Ganti sesuai dengan file PHP Anda
                    type: 'GET',
                    data: {
                        'start_date': start_date,
                        'end_date': end_date
                    },
                    success: function(response) {
                        // Replace the content of the table with the filtered data
                        $('table tbody').html(response);
                        $('#filtermasuk').modal('hide'); // Close the modal
                    },
                });
            });
        });
    </script>

</body>

<!-- The Modal -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Tambah Barang Masuk</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <label for="bukti_masuk_base64">Bukti Masuk:</label>
                    <input type="file" name="bukti_masuk_base64" class="form-control-file" required>
                    <br>
                    <label for="search-barang">Cari Nama Barang:</label>
                    <input type="text" id="search-barang" class="form-control" placeholder="Cari Nama Barang">
                    <label for="barangnya">Pilih Nama Barang:</label>
                    <select name="barangnya" class="form-control">
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
                    <label for="jumlah">Jumlah:</label>
                    <input type="number" name="qty" placeholder="Quantity" class="form-control" required>
                    </br>
                    <label for="distributor">Distributor:</label>
                    <input type="text" name="distributor" placeholder="Distributor" class="form-control" required>
                    </br>
                    <label for="penerima">Penerima</label>
                    <input type="text" name="penerima" placeholder="Penerima" class="form-control" required>
                    </br>
                    <label for="keterangan">Keterangan</label>
                    <input type="text" name="keterangan" placeholder="Keterangan" class="form-control" required>
                    <input type="hidden" name="status" value="1">
                    <br>

                    <button type="submit" class="btn btn-primary" name="barangmasuk">Submit</button>
                </div>
            </form>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('search-barang').addEventListener('input', function() {
        var searchTerm = this.value.toLowerCase();
        var selects = document.querySelectorAll('select[name="barangnya"]');

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


<!-- The Modal "Filter Barang Masuk"-->
<div class="modal fade" id="filtermasuk">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Filter Barang Masuk</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <i><b>*Perhatikan tanggal yang akan di filter</b></i>
                <br><br>
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="tgl_mulai" class="form-control">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="tgl_selesai" class="form-control">
                <br>
                <button type="button" id="filterBtn" class="btn btn-info">Filter</button>
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>



<!-- The Modal "Export"-->
<div class="modal fade" id="masuk">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Export Data Barang Masuk</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Modal body -->
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    Apakah Anda Yakin Ingin Mengexport Data Barang Masuk
                    <br>
                    <br>
                    <button type="submit" class="btn btn-outline-success" name="export_masuk">Export to Excel</button>
                </div>
            </form>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div

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
                                    <th>Unit</th>
                                    <th>Jumlah</th>
                                    <th>Distributor</th>
                                    <th>Penerima</th>
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
                    url: 'filter_barang_masuk.php',
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