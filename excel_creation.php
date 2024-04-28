<?php 

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$crt_all_values = explode(',',$_POST['crt_all_values']);
$wrng_all_values = explode(',',$_POST['wrng_all_values']);
$destination = $_POST['file_path'];
$wrng_all_values = array_unique($wrng_all_values);
$wrng_all_values = array_diff($wrng_all_values, $crt_all_values);
$wrng_all_values = array_values($wrng_all_values);


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

$excel_col_data_wrng = array(
    "Select Data",
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


$column_key = 0;
$column_key1 = 0;
$column_key3 = 0;

$correct_table = '<table border="1"><tr>';
$wrng_table = '<table border="1"><tr>';


$setting_correct_data = $crt_all_values;
$setting_wrng_data = $wrng_all_values;



foreach ($setting_correct_data as $key => $value) {
    $rowData2 = [];
  for ($col = 'A'; $col <= $highestColumn; $col++) {
    $cellValue = $worksheet->getCell("{$col}{$value}")->getValue();
    if($cellValue == '' || $cellValue == 0){
        $rowData2[] = '-';
    }else{
        $rowData2[] = $cellValue;
    }

    if($key == 0){
        $correct_table .= '<th>'.$excel_col_data[$column_key].'</th>';
        $column_key++;
    }
  }
   $finally_correct_value[] = $rowData2;
}

foreach ($setting_wrng_data as $key => $value) {
    $rowData2 = [];
  for ($col = 'A'; $col <= $highestColumn; $col++) {
    $cellValue = $worksheet->getCell("{$col}{$value}")->getValue();
    if($cellValue == '' || $cellValue == 0){
        $rowData2[] = '-';
    }else{
        $rowData2[] = $cellValue;
    }
    if($key == 0){
        $wrng_table .= '<th>'.$excel_col_data_wrng[$column_key1].'</th>';
        $column_key1++;
    }
  }
   $finally_wrng_value[] = $rowData2;
}


$wrng_table .= '</tr>';
foreach ($finally_wrng_value as $key=> $row) {
    $wrng_table .= '<tr><td><input type="checkbox" class="wrng_checkbox" name="wrng_row[]" value="' . $setting_wrng_data[$key] . '" onclick="wrng_data_check(this)"></td>';
    foreach ($row as $cell) {

        $wrng_table .= '<td>' . htmlspecialchars($cell) . '</td>';
    }
    $wrng_table .= '</tr>';
}
$wrng_table .= '</table>';

$correct_table .= '</tr>';
foreach ($finally_correct_value as $key=> $row) {
    foreach ($row as $cell) {

        $correct_table .= '<td>' . htmlspecialchars($cell) . '</td>';
    }
    $correct_table .= '</tr>';
}
$correct_table .= '</table>';

$date = new DateTime();
$date = $date->format('Y-m-d');

$spreadsheet = new Spreadsheet();

// Get the active sheet
$sheet = $spreadsheet->getActiveSheet();

$sheet->fromArray([$excel_col_data], null, 'A1');
$row = 2;
foreach ($finally_correct_value as $rowData) {
    $sheet->fromArray($rowData, null, 'A'.$row);
    $row++;

}
$file_name = uniqid();
$tempFilePath = 'uploads/'.$file_name.'_well_performed_'.$date.'.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($tempFilePath);


$spreadsheet = new Spreadsheet();

// Get the active sheet
$sheet = $spreadsheet->getActiveSheet();

$sheet->fromArray([$excel_col_data], null, 'A1');
$row = 2;
foreach ($finally_wrng_value as $rowData) {
    $sheet->fromArray($rowData, null, 'A'.$row);
    $row++;

}
$file_name = uniqid();
$tempFilePath_lowperformed = 'uploads/'.$file_name.'_low_performed_'.$date.'.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($tempFilePath_lowperformed); 

echo json_encode(["correct_table"=>$correct_table,"wrng_table"=>$wrng_table,"well_per_file"=>$tempFilePath,"low_per_file"=>$tempFilePath_lowperformed]);

?>