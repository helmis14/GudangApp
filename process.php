<?php
include('function.php');
require 'vendor/autoload.php';

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

//header text
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'Nama Barang');
$sheet->setCellValue('C1', 'Unit');
$sheet->setCellValue('D1', 'Stock');
$sheet->setCellValue('E1', 'Lokasi/rak');

//display from DB
$query = mysqli_query($conn,"select * from stock");
$i = 2;
while($row = mysqli_fetch_array($query))
{
	$sheet->setCellValue('A'.$i, $row['idbarang']);
	$sheet->setCellValue('B'.$i, $row['namabarang']);
	$sheet->setCellValue('C'.$i, $row['deskripsi']);
	$sheet->setCellValue('D'.$i, $row['stock']);
    $sheet->setCellValue('E'.$i, $row['lokasi']);	
	$i++;
}

//style heading
$styleHead = [
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],

			'font'=>[
				'color'=>[
					'rgb'=>'#000000'
				],
				'bold'=>true,
				'size'=>13
			],
		];
$sheet->getStyle('A1:E1')->applyFromArray($styleHead);

//style database
$styleIsi = [
	'borders' => [
		'allBorders' => [
			'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
		],
	],
];
$i = $i - 1;
$sheet->getStyle('A2:E'.$i)->applyFromArray($styleIsi);


$writer = new Xlsx($spreadsheet);
$writer->save('Report.xlsx');
?>