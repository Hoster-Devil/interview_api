<?php
$upload_directory = 'uploads/';
if (is_dir($upload_directory)) {
    $files = array_diff(scandir($upload_directory), array('.', '..'));
    foreach ($files as $file) {
        $path = $upload_directory . DIRECTORY_SEPARATOR . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($upload_directory);
}

    
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
$file_name = $_FILES["file"]["name"];
$file_tmp = $_FILES["file"]["tmp_name"];
$file_type = $_FILES["file"]["type"];
$file_size = $_FILES["file"]["size"];
if (!file_exists($upload_directory) && !is_dir($upload_directory)) {
    mkdir($upload_directory, 0755, true);
}
$destination = $upload_directory . $file_name;
if (move_uploaded_file($file_tmp, $destination)) {

} else {
    echo "Error uploading file.";
}

$spreadsheet = IOFactory::load($destination);
$worksheet = $spreadsheet->getActiveSheet();
$highestRow = $worksheet->getHighestRow();
$highestColumn = $worksheet->getHighestColumn();

$finally_correct_value = [];
$setting_correct_data = [];
$setting_wrng_data = [];
$sums = ['N' => 0, 'K' => 0, 'F' => 0];
$count = ['N' => 0, 'K' => 0, 'F' => 0];
$column_data = [];

for ($row = 1; $row <= $highestRow; $row++) {
    foreach (['N', 'K', 'F'] as $column) {
        $cellValue = $worksheet->getCell("{$column}{$row}")->getCalculatedValue();
        if (!is_numeric($cellValue)) {
            continue;
        }
        $sums[$column] += (int)$cellValue;
        if($cellValue != 0){
            $count[$column]++;
        }
    }
}

$averages = [];
foreach (['N', 'K', 'F'] as $column) {
    if($count[$column] != 0){
        $averages[$column] =  $sums[$column] / $count[$column];
    }else{
        $averages[$column] = 0;
    }
}

$cpc = $averages['N'];
$click = $averages['K'];
$conv = $averages['F'];
$cpc_data = false;
$click_data = false;
$conv_data = false;
for ($row = 1; $row <= $highestRow; $row++) {
    $correct_value_array = [];
    $wrong_value_array = [];
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $cellValue = $worksheet->getCell("{$col}{$row}")->getValue();
        if($row == 1){
            $column_data[] = $cellValue;
        }else{
            $cpc_data = false;
            $click_data = false;
            $conv_data = false;
            if($col == 'N' || $col == 'K' || $col == 'F'){
                if($cellValue < $cpc && $col == 'N'){
                    $cpc_data = true;
                }else if($cellValue > $click && $col == 'K'){
                    $click_data = true;
                }else if($cellValue > $conv && $col == 'F'){
                    $conv_data = true;
                }
            }
            if($cpc_data || $click_data || $conv_data){
                if(!in_array($row,$correct_value_array)){
                    $correct_value_array[] = $row;
                }
            }else{

             // if($cpc_data == false && $click_data == false && $conv_data == false){
                if(!in_array($row,$wrong_value_array)){
                    $wrong_value_array[] = $row;
                }
            }
        }
    }
    array_push( $setting_correct_data,...$correct_value_array);
    array_push( $setting_wrng_data,...$wrong_value_array);
}
$setting_wrng_data = array_values(array_diff($setting_wrng_data, $setting_correct_data));

$excel_col_data = array(
    "Check Data",
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
$error = 0;
$excel_error_desc='';
$correct_table = '<table border="1"><tr>';
$wrng_table = '<table border="1"><tr>';

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
        $wrng_table .= '<th>'.$excel_col_data[$column_key1].'</th>';
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
    $correct_table .= '<tr><td><input type="checkbox" class="crt_checkbox" name="crt_row[]" value="' . $setting_correct_data[$key] . '"  onclick="crt_data_check(this)"></td>';
    foreach ($row as $cell) {

        $correct_table .= '<td>' . htmlspecialchars($cell) . '</td>';
    }
    $correct_table .= '</tr>';
}
$correct_table .= '</table>';

// $tempFilePath='';
// if($error == 0){
//    $spreadsheet = new Spreadsheet();

// // Get the active sheet
// $sheet = $spreadsheet->getActiveSheet();

// $sheet->fromArray([$excel_col_data], null, 'A1');
// $row = 2;
// foreach ($finally_correct_value as $rowData) {
//     $sheet->fromArray($rowData, null, 'A'.$row);
//     $row++;

// }
// $file_name = uniqid();
// $tempFilePath = 'uploads/'.$file_name.'.xlsx';
// $writer = new Xlsx($spreadsheet);
// $writer->save($tempFilePath); 
// }

echo json_encode(["correct_table"=>$correct_table,"wrng_table"=>$wrng_table,"file_name"=>$destination,"error"=>$error,"excel_error_desc"=>$excel_error_desc]);

?>

