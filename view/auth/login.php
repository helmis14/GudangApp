<?php
require '../../helper/function.php';

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
        session_start();
        $_SESSION['iduser'] = $iduser;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['log'] = true;

        $activity = "Login berhasil: $email";

        if ($role === 'superadmin' || $role === 'gudang' || $role === 'dev' || $role === 'user'|| $role === 'supervisoradmin'|| $role === 'supervisorgudang') {
            header('Location: ../../view/stock/stock.php');
            exit();
        } else if ($role === 'supervisor') {
            header('Location: ../../view/permintaan/permintaan.php');
            exit();
        } else {
            header('Location: ../../access_denied.php');
            exit();
        }
    } else {
        $error = 'Username atau password salah!';
    }
    // Tutup statement
    mysqli_stmt_close($stmt);
}

if (isset($_SESSION['log'])) {
    header('Location: ../../view/stock/stock.php');
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
    <link data-n-head="ssr" rel="icon" type="image/png" sizes="16x16" href="../../assets/img/icon.png">
    <title>Gudang - Login</title>
    <link href="../../css/login.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</head>

<body>
    
    <div>
     <div class="wave"></div>
     <div class="wave"></div>
     <div class="wave"></div>
    </div>
    
    <div class="session animate__animated animate__backInDown">
        <div class="left">
            <svg enable-background="new 0 0 300 302.5" version="1.1" viewBox="0 0 300 302.5" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
                <style type="text/css">
                    .st0 {
                        fill: #fff;
                    }
                </style>
                <path class="st0" d="m126 302.2c-2.3 0.7-5.7 0.2-7.7-1.2l-105-71.6c-2-1.3-3.7-4.4-3.9-6.7l-9.4-126.7c-0.2-2.4 1.1-5.6 2.8-7.2l93.2-86.4c1.7-1.6 5.1-2.6 7.4-2.3l125.6 18.9c2.3 0.4 5.2 2.3 6.4 4.4l63.5 110.1c1.2 2 1.4 5.5 0.6 7.7l-46.4 118.3c-0.9 2.2-3.4 4.6-5.7 5.3l-121.4 37.4zm63.4-102.7c2.3-0.7 4.8-3.1 5.7-5.3l19.9-50.8c0.9-2.2 0.6-5.7-0.6-7.7l-27.3-47.3c-1.2-2-4.1-4-6.4-4.4l-53.9-8c-2.3-0.4-5.7 0.7-7.4 2.3l-40 37.1c-1.7 1.6-3 4.9-2.8 7.2l4.1 54.4c0.2 2.4 1.9 5.4 3.9 6.7l45.1 30.8c2 1.3 5.4 1.9 7.7 1.2l52-16.2z" />
            </svg>
        </div>
        <form method="post" class="log-in" autocomplete="off">
            <h4>Warehouse <span>Plaza Oleos</span></h4>
            <p>Welcome, Please Log in</p>
            <?php if (isset($error)) { ?>
                <div class="error-message"><?php echo $error; ?></div>
                <div id="capsLockAlert" class="caps-message" style="display: none;">Caps Lock aktif!</div>
            <?php } ?>
            <div class="floating-label">
                <input placeholder="Email" type="text" name="email" id="inputEmailAddress" autocomplete="off">
                <label for="inputEmailAddress">Email:</label>
            </div>
            <div class="floating-label">
                <input placeholder="Password" type="password" name="password" id="inputPassword" autocomplete="off">
                <label for="inputPassword">Password:</label>
                <span class="password-toggle" onclick="togglePassword()">
                    <i class="fas fa-eye" id="passwordToggleIcon"></i>
                </span>
            </div>
            <button type="submit" name="login">Log in</button>
            <a href="../../view/about/version.php" class="discrete" target="_blank">V.1</a>
        </form>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
<script>
    function togglePassword() {
        var passwordInput = document.getElementById("inputPassword");
        var passwordToggleIcon = document.getElementById("passwordToggleIcon");

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            passwordToggleIcon.classList.remove("fa-eye");
            passwordToggleIcon.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            passwordToggleIcon.classList.remove("fa-eye-slash");
            passwordToggleIcon.classList.add("fa-eye");
        }
    }
</script>
<script>
    document.getElementById("inputPassword").addEventListener("keyup", function(event) {
        var capsLockOn = event.getModifierState && event.getModifierState("CapsLock");
        var capsLockAlert = document.getElementById("capsLockAlert");
        if (capsLockOn) {
            capsLockAlert.style.display = "block";
        } else {
            capsLockAlert.style.display = "none";
        }
    });
</script>

</html>