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
$finally_wrng_value = [];
$finally_unperformed_value = [];
$setting_correct_data = [];
$setting_wrng_data = [];
$setting_un_performed_data = [];
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

$cpc = $averages['N']*1.2;
$click = $averages['K'];
$conv = $averages['F']*0.30;

    $correct_value_array = [];
    $wrong_value_array = [];
    $un_performed_value_array = [];
for ($row = 1; $row <= $highestRow; $row++) {
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $cellValue = $worksheet->getCell("{$col}{$row}")->getValue();
        if($row == 1){
            $column_data[] = $cellValue;
        }else{
            if (($col == 'N' || $col == 'K' || $col == 'F')) {
                if ($cellValue < $cpc  && $col == 'N') {

                    if ($cellValue != '0' && $cellValue != '-' && $cellValue != '') {

                        if(!in_array($row,$correct_value_array)){
                            $correct_value_array[] = $row;

                        }

                    } else if ($cellValue == '0' || $cellValue == '-' || $cellValue == '') {

                        if(!in_array($row,$un_performed_value_array)){
                            $un_performed_value_array[] = $row;
                        }

                    }

                } else if ($cellValue > $click && $col == 'K') {

                   
                        if(!in_array($row,$correct_value_array)){
                            $correct_value_array[] = $row;
                        }
                   

                } else if ($cellValue > $conv && $col == 'F') {

                   

                        if(!in_array($row,$correct_value_array)){
                            $correct_value_array[] = $row;
                        }

                    
                }
            }
        }
    }
}

// $wrong_value_array = array_diff(range(min($correct_value_array, $un_performed_value_array), max($correct_value_array, $un_performed_value_array)), array_merge($correct_value_array, $un_performed_value_array));

$mergedArray = array_merge($correct_value_array, $un_performed_value_array);

// Sort the merged array
sort($mergedArray);

// Find the missing values
$missingValues = [];
for ($i = 1; $i < count($mergedArray); $i++) {
    if ($mergedArray[$i] - $mergedArray[$i - 1] > 1) {
        $missingValues[] = range($mergedArray[$i - 1] + 1, $mergedArray[$i] - 1);
    }
}

// Flatten the array
$wrong_value_array = array_merge(...$missingValues);

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
$error = 0;
$excel_error_desc='';
$correct_table = '<table border="1"><tr>';
$wrng_table = '<table border="1"><tr>';
$un_performed_table = '<table border="1"><tr>';

$setting_correct_data = $correct_value_array;
$setting_wrng_data = $wrong_value_array;
$setting_un_performed_data = $un_performed_value_array;


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

foreach ($setting_un_performed_data as $key => $value) {
    $rowData2 = [];
  for ($col = 'A'; $col <= $highestColumn; $col++) {
    $cellValue = $worksheet->getCell("{$col}{$value}")->getValue();
    $rowData2[] = $cellValue;
    if($key == 0){
        $un_performed_table .= '<th>'.$excel_col_data[$column_key3].'</th>';
        $column_key3++;
    }
  }
   $finally_un_performed_value[] = $rowData2;
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
    // $correct_table .= '<tr><td><input type="checkbox" class="crt_checkbox" name="crt_row[]" value="' . $setting_correct_data[$key] . '"  onclick="crt_data_check(this)"></td>';
    foreach ($row as $cell) {

        $correct_table .= '<td>' . htmlspecialchars($cell) . '</td>';
    }
    $correct_table .= '</tr>';
}
$correct_table .= '</table>';

$un_performed_table .= '</tr>';
foreach ($finally_un_performed_value as $key=> $row) {
    // $un_performed_table .= '<tr><td><input type="checkbox" class="crt_checkbox" name="crt_row[]" value="' . $setting_correct_data[$key] . '"  onclick="crt_data_check(this)"></td>';
    foreach ($row as $cell) {

        $un_performed_table .= '<td>' . htmlspecialchars($cell) . '</td>';
    }
    $un_performed_table .= '</tr>';
}
$un_performed_table .= '</table>';


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


$spreadsheet = new Spreadsheet();

// Get the active sheet
$sheet = $spreadsheet->getActiveSheet();

$sheet->fromArray([$excel_col_data], null, 'A1');
$row = 2;
foreach ($finally_un_performed_value as $rowData) {
    $sheet->fromArray($rowData, null, 'A'.$row);
    $row++;

}
$file_name = uniqid();
$tempFilePath_unperformed = 'uploads/'.$file_name.'_un_performed_'.$date.'.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($tempFilePath_unperformed); 


echo json_encode(["correct_table"=>$correct_table,"wrng_table"=>$wrng_table,"un_performed_table"=>$un_performed_table,"well_per_file"=>$tempFilePath,"un_per_file"=>$tempFilePath_unperformed,"low_per_file"=>$tempFilePath_lowperformed,"error"=>$error,"excel_error_desc"=>$excel_error_desc,"setting_correct_data"=>$setting_correct_data,"file_name_excel"=>$destination,"setting_wrng_data"=>$setting_wrng_data]);

?>

