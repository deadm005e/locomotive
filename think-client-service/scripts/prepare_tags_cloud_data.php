<?php
// в файле xlsx группа тега должна быть в первом столбце, сам тег - во втором, урл тега - в третьем
//composer require phpoffice/phpspreadsheet
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

require $_SERVER["DOCUMENT_ROOT"].'/think-client-service/config.php';

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
$spreadsheet = $reader->load(THINK_CLIENT_DATA_PATH."/tagsCloud/xlsx/main.xlsx");
$worksheet = $spreadsheet->getSheet(0)->toArray();
foreach ($worksheet as $row) {
  if(!empty($row[0]))
    $data[$row[0]][$row[1]] = $row[2]; 
}

file_put_contents(THINK_CLIENT_DATA_PATH.'/tagsCloud/tags_cloud_data.php', serialize($data));