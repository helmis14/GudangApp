<?php
include('../../helper/function.php');
require '../../vendor/autoload.php';

ini_set('memory_limit', '1024M'); // Tingkatkan menjadi 1GB atau lebih sesuai kebutuhan
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set default font
$spreadsheet->getDefaultStyle()
    ->getFont()
    ->setName('Arial')
    ->setSize(12);

// Setting column width
$columns = range('A', 'F');
foreach ($columns as $col) {
    $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
}

// Title merge cells
$spreadsheet->getActiveSheet()->setCellValue('A1', "Report Stock Barang Keluar Plaza Oleos");
$spreadsheet->getActiveSheet()->mergeCells("A1:F1");

// yyyy-mm-dd
$spreadsheet->getActiveSheet()->setCellValue('E3', "Tanggal :");

$spreadsheet->getActiveSheet()->setCellValue('F3', date('Y-m-d'));

// Header text
$sheet->setCellValue('A5', 'Tanggal');
$sheet->setCellValue('B5', 'Nama Barang');
$sheet->setCellValue('C5', 'Unit');
$sheet->setCellValue('D5', 'Jumlah');
$sheet->setCellValue('E5', 'Penerima');
$sheet->setCellValue('F5', 'Keterangan');

// Display from DB
$query2 = mysqli_query($conn, "SELECT keluar.idpermintaan, permintaan_keluar.tanggal, permintaan_keluar.gambar_base64,
                                stock.namabarang, stock.unit, keluar.qty, keluar.penerima, keluar.keterangan 
                                FROM permintaan_keluar 
                                INNER JOIN keluar ON permintaan_keluar.idpermintaan = keluar.idpermintaan
                                INNER JOIN stock ON keluar.idbarang = stock.idbarang
                                ORDER BY keluar.idpermintaan DESC");

$data = array();

while ($row = mysqli_fetch_assoc($query2)) {
    $data[] = $row;
}

$i = 6;
foreach ($data as $row) {
    $sheet->setCellValue('A' . $i, $row['tanggal']);
    $sheet->setCellValue('B' . $i, $row['namabarang']);
    $sheet->setCellValue('C' . $i, $row['unit']);
    $sheet->setCellValue('D' . $i, $row['qty']);
    $sheet->setCellValue('E' . $i, $row['penerima']);
    $sheet->setCellValue('F' . $i, $row['keterangan']);
    $i++;
}

// Style Title
$styleTitle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
    'font' => [
        'color' => ['rgb' => '000000'],
        'bold' => true,
        'size' => 14,
    ],
];

$sheet->getStyle('A1:F1')->applyFromArray($styleTitle);

// Style Tanggal
$styleTanggal = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_RIGHT,
    ],
    'font' => [
        'color' => ['rgb' => '000000'],
        'bold' => true,
        'italic' => true,
        'size' => 12,
    ],
];

$sheet->getStyle('F3')->applyFromArray($styleTanggal);

// Style heading text bold, size, warna, posisi
$styleHead = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ],
    ],
    'font' => [
        'color' => ['rgb' => '000000'],
        'bold' => true,
        'size' => 13,
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
];

$sheet->getStyle('A5:F5')->applyFromArray($styleHead);

// Style data dalam row
$styleIsi = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
];

$sheet->getStyle('A6:F' . ($i - 1))->applyFromArray($styleIsi);

$writer = new Xlsx($spreadsheet);
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=\"Report Barang Keluar.xlsx\"");
header("Cache-Control: max-age=0");
header("Expires: Fri, 11 Nov 2011 11:11:11 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: cache, must-revalidate");
header("Pragma: public");
$writer->save("php://output");
?>
