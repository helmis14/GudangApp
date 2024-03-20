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
    <meta name="description" content="About Us - IT Roheda Group" />
    <meta name="author" content="" />
    <link data-n-head="ssr" rel="icon" type="image/png" sizes="16x16" href="../../assets/img/icon.png">
    <title>About Us - IT Roheda Group</title>
    <link href="../../css/styles.css" rel="stylesheet" />
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
    <?php
    require_once '../../layout/_nav.php';
    require_once '../../layout/_sidenav.php';
    ?>
    <div class="container team-section">
        <h1>Meet Our Team Developer</h1>
        <div class="row">
            <div class="col-lg-4">
                <div class="team-card">
                    <img src="../../assets/img/pakwindu.png" alt="Team Member 1" class="img-fluid">
                    <h3>Windu Dwima Putra</h3>
                    <p>Fullstack Developer</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="team-card">
                    <img src="../../assets/img/pakandre.png" alt="Team Member 2" height="430" width="270">
                    <h3>Christian Andrea</h3>
                    <p>Tester</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="team-card">
                    <img src="../../assets/img/helmi.png" alt="Team Member 3" class="img-fluid">
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
    <script src="..././assets/demo/datatables-demo.js"></script>
</body>

</html>