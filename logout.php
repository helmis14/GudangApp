<?php
require './helper/function.php';


if (isset($_SESSION['iduser']) && isset($_SESSION['email'])) {

    session_destroy();
}

header('location:login.php');
