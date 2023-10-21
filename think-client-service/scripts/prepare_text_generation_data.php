<?php

//composer require phpoffice/phpspreadsheet
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

require $_SERVER["DOCUMENT_ROOT"].'/think-client-service/config.php';

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
$spreadsheet = $reader->load(THINK_CLIENT_DATA_PATH."/textGeneration/xlsx/main.xlsx");
$worksheet = $spreadsheet->getActiveSheet()->toArray();
foreach ($worksheet as $row) {
  foreach ($row as $cell_key => $cell_value) {
    if (empty($cell_value)){
      continue;
    }
    $data_tmp[$cell_key][] = $cell_value;
  }
}
ksort($data_tmp);
$data['main'] = $data_tmp;
unset($data_tmp);

$spreadsheet = $reader->load(THINK_CLIENT_DATA_PATH."/textGeneration/xlsx/tags.xlsx");
$worksheet = $spreadsheet->getActiveSheet()->toArray();
$i = 0;
foreach ($worksheet as $row) {
  if($i == 0){
    $i++;
    continue;
  }
  if (empty($row[0])){
    continue;
  }
  $data[$row[2]][] = array('text' => $row[0], 'url' => $row[1] ? $row[1] : '' );
}

file_put_contents(THINK_CLIENT_DATA_PATH.'/textGeneration/generation_data.php', serialize($data));