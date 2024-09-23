<?php
require '../../helper/function.php';
require '../../helper/cek.php';
require '../../vendor/autoload.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: ../../view/auth/login.php');
    exit();
}

if (!in_array($_SESSION['role'], ['superadmin', 'dev', 'gudang', 'user', 'supervisor', 'supervisoradmin', 'supervisorgudang'])) {
    header('Location: ../../access_denied.php');
    exit();
}

$year = isset($_POST['tahun']) ? $_POST['tahun'] : date('Y');

// Query for total per quarter
$query = "SELECT 
    SUM(CASE WHEN QUARTER(m2.tanggal) = 1 THEN m2.qty ELSE 0 END) AS Q1,
    SUM(CASE WHEN QUARTER(m2.tanggal) = 2 THEN m2.qty ELSE 0 END) AS Q2,
    SUM(CASE WHEN QUARTER(m2.tanggal) = 3 THEN m2.qty ELSE 0 END) AS Q3,
    SUM(CASE WHEN QUARTER(m2.tanggal) = 4 THEN m2.qty ELSE 0 END) AS Q4
FROM masuk m2
WHERE m2.status = 1 AND YEAR(m2.tanggal) = $year";

$barangMasuk = mysqli_query($conn, $query);
$dataMasuk = mysqli_fetch_assoc($barangMasuk);

$query = "SELECT 
    SUM(CASE WHEN QUARTER(k2.tanggal) = 1 THEN k2.qty ELSE 0 END) AS Q1,
    SUM(CASE WHEN QUARTER(k2.tanggal) = 2 THEN k2.qty ELSE 0 END) AS Q2,
    SUM(CASE WHEN QUARTER(k2.tanggal) = 3 THEN k2.qty ELSE 0 END) AS Q3,
    SUM(CASE WHEN QUARTER(k2.tanggal) = 4 THEN k2.qty ELSE 0 END) AS Q4
FROM keluar k2
INNER JOIN permintaan_keluar pk ON k2.idpermintaan = pk.idpermintaan
WHERE pk.status = 1 AND pk.status2 = 1 AND YEAR(pk.tanggal) = $year";

$barangKeluar = mysqli_query($conn, $query);
$dataKeluar = mysqli_fetch_assoc($barangKeluar);

$query = "SELECT 
    SUM(CASE WHEN QUARTER(pk.tanggal) = 1 THEN k2.qty ELSE 0 END) AS Q1,
    SUM(CASE WHEN QUARTER(pk.tanggal) = 2 THEN k2.qty ELSE 0 END) AS Q2,
    SUM(CASE WHEN QUARTER(pk.tanggal) = 3 THEN k2.qty ELSE 0 END) AS Q3,
    SUM(CASE WHEN QUARTER(pk.tanggal) = 4 THEN k2.qty ELSE 0 END) AS Q4
FROM keluar k2
INNER JOIN permintaan_keluar pk ON k2.idpermintaan = pk.idpermintaan
WHERE pk.status = 1 AND pk.status2 = 1 AND YEAR(pk.tanggal) = $year";


$permintaan = mysqli_query($conn, $query);
$dataPermintaan = mysqli_fetch_assoc($permintaan);

// Prepare data for charts
$response = [
    'barangMasuk' => array_values($dataMasuk),
    'barangKeluar' => array_values($dataKeluar),
    'permintaan' => array_values($dataPermintaan),
];

echo json_encode($response);
