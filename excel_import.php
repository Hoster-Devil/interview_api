<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// echo "<pre>";print_r($_FILES['file']);exit();

        $file_name = $_FILES["file"]["name"];
        $file_tmp = $_FILES["file"]["tmp_name"];
        $file_type = $_FILES["file"]["type"];
        $file_size = $_FILES["file"]["size"];
        $upload_directory = 'uploads/';
        if (!file_exists($upload_directory) && !is_dir($upload_directory)) {
            mkdir($upload_directory, 0755, true);
        }
        $destination = $upload_directory . $file_name;
        if (move_uploaded_file($file_tmp, $destination)) {
            // echo "File uploaded successfully.";
        } else {
            echo "Error uploading file.";
        }

// Load the Excel file
$spreadsheet = IOFactory::load($destination);

// Select the first worksheet
$worksheet = $spreadsheet->getActiveSheet();

// Get the highest row and column
$highestRow = $worksheet->getHighestRow();
$highestColumn = $worksheet->getHighestColumn();

// Initialize an empty array to store the data and sums
$data = [];
$data1 = [];
$sums = ['N' => 0, 'K' => 0, 'F' => 0];
$count = ['N' => 0, 'K' => 0, 'F' => 0];
$column_data = [];
// Iterate through each row and column to fetch the data and calculate sums
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

// Calculate averages
$averages = [];
foreach (['N', 'K', 'F'] as $column) {
    $averages[$column] =  $sums[$column] / $count[$column];
}

$cpc = $averages['N'];
$click = $averages['K'];
$conv = $averages['F'];
 $cpc_data = false;
 $click_data = false;
 $conv_data = false;
for ($row = 1; $row <= $highestRow; $row++) {
    $rowData1 = [];
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $cellValue = $worksheet->getCell("{$col}{$row}")->getValue();
        if($row == 1){
            $column_data[] = $cellValue;
        }else{
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
            if(!in_array($row,$rowData1)){
                $rowData1[] = $row;
            }

        }
        }
        $cpc_data = false;
 $click_data = false;
 $conv_data = false;
    }
    array_push( $data1,...$rowData1);
    // $data[] = $rowData1;
}
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
$html = '<table border="1"><tr>';
$column_key = 0;
$error = 0;
$excel_error_desc='';
foreach ($data1 as $key => $value) {
    $rowData2 = [];
  for ($col = 'A'; $col <= $highestColumn; $col++) {
    $cellValue = $worksheet->getCell("{$col}{$value}")->getValue();
    if($cellValue == '' || $cellValue == 0){
        $rowData2[] = '-';
    }else{
        $rowData2[] = $cellValue;
    }
    if($key == 0){
        if(!in_array($excel_col_data[$column_key],$excel_col_data)){
           $excel_error_desc='Please Upload Correct Excel';
           $error = 1;
        }
        $html .= '<th>'.$excel_col_data[$column_key].'</th>';
        $column_key++;
    }
  }
   $data[] = $rowData2;
}



$html .= '</tr>';
foreach ($data as $row) {
    $html .= '<tr>';
    foreach ($row as $cell) {

        $html .= '<td>' . htmlspecialchars($cell) . '</td>';
    }
    $html .= '</tr>';
}
$html .= '</table>';
unlink($destination);
$tempFilePath='';
if($error == 0){
   $spreadsheet = new Spreadsheet();

// Get the active sheet
$sheet = $spreadsheet->getActiveSheet();

$sheet->fromArray([$excel_col_data], null, 'A1');
$row = 2;
foreach ($data as $rowData) {
    $sheet->fromArray($rowData, null, 'A'.$row);
    $row++;

}
$file_name = uniqid();
$tempFilePath = 'uploads/'.$file_name.'.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($tempFilePath); 
}

echo json_encode(["table_data"=>$html,"file_name"=>$tempFilePath,"error"=>$error,"excel_error_desc"=>$excel_error_desc]);

?>

