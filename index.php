<?php
session_start();
if (isset($_SESSION['login'])) {
    header('Location: ./view/stock/stock.php');
} else {
    header('Location: ./view/auth/login.php');
}
