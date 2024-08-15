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
$spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);;

//title merge cells
$spreadsheet->getActiveSheet()->setCellValue('A1', "Report Permintaan Barang Plaza Oleos");
$spreadsheet->getActiveSheet()->mergeCells("A1:G1");

// yyyy-mm-dd
$spreadsheet->getActiveSheet()->setCellValue('F3', "Tanggal :");

use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Date as DateWizard;

$spreadsheet->getActiveSheet()->setCellValue('G3', '=NOW()');
$dateFormat = new DateWizard(
	DateWizard::SEPARATOR_DASH,
	DateWizard::YEAR_FULL,
	DateWizard::MONTH_NUMBER_LONG,
	DateWizard::DAY_NUMBER_LONG
);
$spreadsheet->getActiveSheet()->getStyle('G3')
	->getNumberFormat()
	->setFormatCode($dateFormat);


//header text
$sheet->setCellValue('A5', 'NO BP');
$sheet->setCellValue('B5', 'Tanggal');
$sheet->setCellValue('C5', 'Namabarang');
$sheet->setCellValue('D5', 'Unit');
$sheet->setCellValue('E5', 'Jumlah');
$sheet->setCellValue('F5', 'Keterangan');
$sheet->setCellValue('G5', 'Status');

//display from DB
$query = mysqli_query($conn, "select * from permintaan p, barang_permintaan b where b.idpermintaan = p.idpermintaan");
$i = 6;
while ($row = mysqli_fetch_array($query)) {
	$sheet->setCellValue('A' . $i, $row['idpermintaan']);
	$sheet->setCellValue('B' . $i, $row['tanggal']);
	$sheet->setCellValue('C' . $i, $row['namabarang']);
	$sheet->setCellValue('D' . $i, $row['unit']);
	$sheet->setCellValue('E' . $i, $row['qtypermintaan']);
	$sheet->setCellValue('F' . $i, $row['keterangan']);

	// Perbaikan disini value = status
	switch ($row['status_barang']) {
		case 0:
			$status = 'Pending';
			break;
		case 1:
			$status = 'Diterima';
			break;
		case 2:
			$status = 'Tidak Disetujui';
			break;
		default:
			$status = 'Status tidak valid';
			break;
	}
	$sheet->setCellValue('G' . $i, $status);

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


$sheet->getStyle('A1:G1')->applyFromArray($styleTitle);

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

$sheet->getStyle('A5:G5')->applyFromArray($styleHead);

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

$i = $i - 1;
$sheet->getStyle('A6:G' . $i)->applyFromArray($styleIsi);


$writer = new Xlsx($spreadsheet);
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=\"Report Permintaan Barang.xlsx\"");
header("Cache-Control: max-age=0");
header("Expires: Fri, 11 Nov 2011 11:11:11 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: cache, must-revalidate");
header("Pragma: public");
$writer->save("php://output");
