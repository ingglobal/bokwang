<?php
/* 서버에 파일 저장하기 */

require 'vendor/autoload.php';

// 문서 생성용
use PhpOffice\PhpSpreadsheet\Spreadsheet;
// 문서 저장 및 불러오기용
use PhpOffice\PhpSpreadsheet\IOFactory;

// 엑셀 문서 생성
$spreadsheet = new Spreadsheet();
// 현재 활성화된 Sheet 가져오기
$sheet = $spreadsheet->getActiveSheet();
// Sheet의 특정 셀에 값 입력
$sheet->setCellValue('A1', 'Hello World !');

// 저장을 위한 파일작성자 생성
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
// 서버에 파일 저장
$writer->save('hello world.xlsx');