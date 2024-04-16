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

// Iterate through each row and column to fetch the data and calculate sums
for ($row = 2; $row <= $highestRow; $row++) {
    $rowData = [];
    foreach (['N', 'K', 'F'] as $column) {
        $cellValue = $worksheet->getCell("{$column}{$row}")->getCalculatedValue();
        if (!is_numeric($cellValue)) {
            // Skip non-numeric or rich text values
            continue;
        }
        $sums[$column] += (int)$cellValue; // Increment sum for the column
        if($cellValue != 0){

        $count[$column]++; // Increment count for the column
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
for ($row = 2; $row <= $highestRow; $row++) {
    $rowData1 = [];
    for ($col = 'A'; $col <= $highestColumn; $col++) {
        $cellValue = $worksheet->getCell("{$col}{$row}")->getValue();
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
        $cpc_data = false;
 $click_data = false;
 $conv_data = false;
    }
    array_push( $data1,...$rowData1);
    // $data[] = $rowData1;
}
$html = '<table border="1"><tr>';
foreach ($data1 as $key => $value) {
    $rowData2 = [];
  for ($col = 'A'; $col <= $highestColumn; $col++) {
    $cellValue = $worksheet->getCell("{$col}{$value}")->getValue();
    $rowData2[] = $cellValue;
    if($key == 0){

    $html .= '<th>'.$col.'</th>';
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
$spreadsheet = new Spreadsheet();

// Get the active sheet
$sheet = $spreadsheet->getActiveSheet();

// Add data to the sheet
$headers = [];
for ($column = 'A'; $column <= 'V'; $column++) {
    $headers[] = $column; // Dynamically generate header names
}

$sheet->fromArray([$headers], null, 'A1');
$row = 2;
foreach ($data as $rowData) {
    $sheet->fromArray($rowData, null, 'A'.$row);
    $row++;

}

$tempFilePath = 'uploads/data.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($tempFilePath);
echo $html;

?>

