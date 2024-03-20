<?php
include('../../helper/function.php');
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

//set default font
$spreadsheet->getDefaultStyle()
    ->getFont()
    ->setName('Arial')
    ->setSize(12);

//setting column width
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

//title merge cells
$spreadsheet->getActiveSheet()->setCellValue('A1', "Report Stock Barang Keluar Plaza Oleos");
$spreadsheet->getActiveSheet()->mergeCells("A1:F1");

// yyyy-mm-dd
$spreadsheet->getActiveSheet()->setCellValue('E3', "Tanggal :");

use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Date as DateWizard;

$spreadsheet->getActiveSheet()->setCellValue('F3', '=NOW()');
$dateFormat = new DateWizard(
    DateWizard::SEPARATOR_DASH,
    DateWizard::YEAR_FULL,
    DateWizard::MONTH_NUMBER_LONG,
    DateWizard::DAY_NUMBER_LONG
);
$spreadsheet->getActiveSheet()->getStyle('F3')
    ->getNumberFormat()
    ->setFormatCode($dateFormat);

//header text
$sheet->setCellValue('A5', 'Tanggal');
$sheet->setCellValue('B5', 'Nama Barang');
$sheet->setCellValue('C5', 'Unit');
$sheet->setCellValue('D5', 'Jumlah');
$sheet->setCellValue('E5', 'Penerima');
$sheet->setCellValue('F5', 'Keterangan');

//display from DB
$query2 = mysqli_query($conn, "SELECT keluar.idpermintaan, permintaan_keluar.tanggal, permintaan_keluar.gambar_base64,
                                GROUP_CONCAT(CONCAT(stock.namabarang, '')) as nama_barang, 
                                GROUP_CONCAT(stock.unit) as unit, 
                                GROUP_CONCAT(keluar.qty) as qty, 
                                GROUP_CONCAT(keluar.penerima) as penerima, 
                                GROUP_CONCAT(keluar.keterangan) as keterangan 
                                FROM permintaan_keluar 
                                INNER JOIN keluar ON permintaan_keluar.idpermintaan = keluar.idpermintaan
                                INNER JOIN stock ON keluar.idbarang = stock.idbarang
                                GROUP BY keluar.idpermintaan 
                                ORDER BY keluar.idpermintaan DESC");

$data = array();

while ($row = mysqli_fetch_assoc($query2)) {
    $data[] = $row;
}

$i = 6;
foreach ($data as $row) {
    $sheet->setCellValue('A' . $i, $row['tanggal']);
    $sheet->setCellValue('B' . $i, isset($row['namabarang']) ? $row['namabarang'] : $row['nama_barang']);
    $sheet->setCellValue('C' . $i, isset($row['unit']) ? $row['unit'] : $row['unit']);
    $sheet->setCellValue('D' . $i, isset($row['qty']) ? $row['qty'] : $row['qty']);
    $sheet->setCellValue('E' . $i, isset($row['penerima']) ? $row['penerima'] : $row['penerima']);
    $sheet->setCellValue('F' . $i, isset($row['keterangan']) ? $row['keterangan'] : $row['keterangan']);
    $i++;
}
//style Title
$styleTitle = [
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    ],
    'font' => [
        'color' => [
            'rgb' => '#000000'
        ],
        'bold' => true,
        'size' => 14,
    ],
];

$sheet->getStyle('A1:F1')->applyFromArray($styleTitle);

//style Tanggal
$styleTanggal = [
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
    ],
    'font' => [
        'color' => [
            'rgb' => '#000000'
        ],
        'bold' => true,
        'italic' => true,
        'size' => 12,
    ],
];

$sheet->getStyle('F3')->applyFromArray($styleTanggal);

//style Date
$styleValueTanggal = [
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    ],
    'font' => [
        'color' => [
            'rgb' => '#000000'
        ],
        'bold' => true,
        'italic' => true,
        'size' => 12,
    ],
];

$sheet->getStyle('G3')->applyFromArray($styleValueTanggal);

//style heading text bold, size, warna, posisi
$styleHead = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ],
    ],
    'font' => [
        'color' => [
            'rgb' => '#000000'
        ],
        'bold' => true,
        'size' => 13,
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
    ],
];

$sheet->getStyle('A5:F5')->applyFromArray($styleHead);

//style data dalam row
$styleIsi = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ],
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
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
