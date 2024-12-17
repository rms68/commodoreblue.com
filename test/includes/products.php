<?php
// test/includes/products.php

// Static CSV data as in the original code
$csvData = <<<CSV
type,product_name,PRICE,PLAYABLE,roms,MEDIA,product_description,product_category_1
product,ACEOFACES,25,TRUE,emu/ROMS/ACEOFACES.d64,gallery/ACEOFACES.jpg,TEMP DESCRIPTION,GAMES
product,AFTERBURNER,25,TRUE,emu/ROMS/AFTERBURNER.d64,gallery/AFTERBURNER.jpg,TEMP DESCRIPTION,GAMES
product,ARCHON,25,TRUE,emu/ROMS/ARCHON.d64,gallery/ARCHON.jpg,TEMP DESCRIPTION,GAMES
product,ARKANOID,25,TRUE,emu/ROMS/ARKANOID.d64,gallery/ARKANOID.jpg,TEMP DESCRIPTION,GAMES
product,BATMAN,25,TRUE,emu/ROMS/BATMAN.d64,gallery/BATMAN.jpg,TEMP DESCRIPTION,GAMES
product,BATMAN CAPED CRUSADER,25,TRUE,emu/ROMS/BATMANCAPED.d64,gallery/BATMANCAPED.jpg,TEMP DESCRIPTION,GAMES
product,BLUE commodore64,2000,FALSE,FALSE,images/Top_BLUE.png,TEMP DESCRIPTION,BUNDLES
product,BOARD,2000,FALSE,FALSE,images/PRODUCT_BASE.jpg,TEMP DESCRIPTION,UPGRADES
product,ORANGE commodore64,2000,FALSE,FALSE,images/AngleOrange.jpg,TEMP DESCRIPTION,BUNDLES
product,RED commodore64,2000,FALSE,FALSE,images/AngleRED.jpg,TEMP DESCRIPTION,BUNDLES
product,SD2IEC,2000,FALSE,FALSE,images/PLA_onBoard.jpg,TEMP DESCRIPTION,UPGRADES
CSV;

// Parse the CSV data
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

$products = parseCSV($csvData);
$pages = ['home', 'gallery', 'about', 'emu', 'shop'];
?>
