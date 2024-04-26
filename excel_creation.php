<?php 

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$crt_all_values = explode(',',$_POST['crt_all_values']);
$wrng_all_values = explode(',',$_POST['wrng_all_values']);
$destination = $_POST['file_path'];
$all_values = array_merge($crt_all_values, $wrng_all_values);

$spreadsheet = IOFactory::load($destination);
$worksheet = $spreadsheet->getActiveSheet();
$highestRow = $worksheet->getHighestRow();
$highestColumn = $worksheet->getHighestColumn();

$finally_all_values = [];
$excel_col_data = array(
    "Keyword status",
    "Keyword",
    "Match type",
    "Status",
    "Status reasons",
    "Conversions",
    "Currency code",
    "Cost / conv.",
    "Final URL",
    "Mobile final URL",
    "Clicks",
    "Impr.",
    "CTR",
    "Avg. CPC",
    "Cost",
    "Quality Score",
    "Ad relevance (hist.)",
    "Ad relevance",
    "Landing page exp. (hist.)",
    "Landing page exp.",
    "Quality Score (hist.)",
    "Conv. rate"
);
foreach ($all_values as $key => $value) {
    $rowData2 = [];
  for ($col = 'A'; $col <= $highestColumn; $col++) {
    $cellValue = $worksheet->getCell("{$col}{$value}")->getValue();
    if($cellValue == '' || $cellValue == 0){
        $rowData2[] = '-';
    }else{
        $rowData2[] = $cellValue;
    }
  }
   $finally_all_values[] = $rowData2;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->fromArray([$excel_col_data], null, 'A1');
$row = 2;
foreach ($finally_all_values as $rowData) {
    $sheet->fromArray($rowData, null, 'A'.$row);
    $row++;

}
$file_name = uniqid();
$tempFilePath = 'uploads/'.$file_name.'.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($tempFilePath); 
echo json_encode(["file_name"=>$tempFilePath]);

?>