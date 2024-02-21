<?php
require 'function.php';

// cek login, terdaftar atau tidak
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Gunakan prepared statement untuk mencegah SQL Injection
    $stmt = mysqli_prepare($conn, "SELECT iduser, role FROM login WHERE email=? AND password=?");
    mysqli_stmt_bind_param($stmt, "ss", $email, $password);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    // Binding hasil query
    mysqli_stmt_bind_result($stmt, $iduser, $role);
    mysqli_stmt_fetch($stmt);

    // Verifikasi hasil query
    if (mysqli_stmt_num_rows($stmt) > 0) {
        // Jika login berhasil, simpan informasi session login dan catat log aktivitas
        session_start();
        $_SESSION['iduser'] = $iduser;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['log'] = true;

        // Catat log aktivitas
        $activity = "Login berhasil: $email";
        catatLog($conn, $activity, $iduser);

        // Periksa peran pengguna dan arahkan ke halaman yang sesuai
        if ($role === 'superadmin' || $role === 'gudang' || $role === 'dev') {
            header('Location: index.php');
            exit();
        } else if ($role === 'supervisor') {
            header('Location: permintaan.php');
            exit();
        } else {
            header('Location: access_denied.php');
            exit();
        }
    } else {
        header('Location: login.php');
    }

    // Tutup statement
    mysqli_stmt_close($stmt);
}

if (isset($_SESSION['log'])) {
    header('Location: index.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Gudang - Login</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
</head>

<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">Login</h3>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label class="small mb-1" for="inputEmailAddress">Email</label>
                                            <input class="form-control py-4" name="email" id="inputEmailAddress" type="email" placeholder="Enter email address" />
                                        </div>
                                        <div class="form-group">
                                            <label class="small mb-1" for="inputPassword">Password</label>
                                            <input class="form-control py-4" name="password" id="inputPassword" type="password" placeholder="Enter password" />
                                        </div>
                                        <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <button class="btn btn-primary" name="login">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        </footer>
    </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>

</html>