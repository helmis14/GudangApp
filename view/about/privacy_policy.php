<?php
require '../../helper/function.php';
require '../../helper/cek.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: ../../login.php');
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
    <link data-n-head="ssr" rel="icon" type="image/png" sizes="16x16" href="../../assets/img/icon.png">
    <title>Privacy Policy - IT Roheda Group</title>
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
    <?php
    require_once '../../layout/_footer.php';
    require_once '../../component/modalLogout.php';
    ?>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="../../js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/demo/chart-area-demo.js"></script>
    <script src="../../assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
    <script src="../../assets/demo/datatables-demo.js"></script>
</body>

</html>