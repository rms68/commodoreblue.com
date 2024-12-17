<?php
$csvData = <<<CSV
type,product_name,PRICE,PLAYABLE,roms,MEDIA,product_description,product_category_1
product,ACEOFACES,25,TRUE,\emu\ROMS\,https://example.com/aces.jpg,TEMP DESCRIPTION,GAMES
product,BATMAN,25,TRUE,\emu\ROMS\,https://example.com/batman.jpg,TEMP DESCRIPTION,GAMES
CSV;

$products = parseCSV($csvData);

function parseCSV($csvData) {
    $lines = explode("\n", $csvData);
    $header = str_getcsv(array_shift($lines));
    $products = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $row = str_getcsv($line);
        $products[] = array_combine($header, $row);
    }
    return $products;
}
