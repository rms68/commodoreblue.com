<?php
// test/includes/commands.php

function generateListItems($pages, $products) {
    $current = $_SESSION['currentPage'];
    $viewMode = $_SESSION['viewMode'];
    $output = "";

    if ($current === 'emu') {
        $output .= "GAMES:\n";
        foreach ($products as $p) {
            if (strtoupper($p['name']) && $p['sku']) {
                $output .= c64_upper($p['name']) . "\n";
            }
        }
    } elseif ($current === 'shop') {
        $output .= "PRODUCTS:\n";
        foreach ($products as $p) {
            $output .= c64_upper($p['name']) . " (PRICE: {$p['price']})\n";
        }
    } else {
        $output .= "PAGES:\n";
        foreach ($pages as $pg) {
            $output .= c64_upper($pg) . "\n";
        }
    }
    return $output;
}

function runItem($products) {
    if (!isset($_SESSION['loadedItem'])) {
        return "NO PROGRAM LOADED. USE LOAD ITEM FIRST.\n";
    }

    $item = $_SESSION['loadedItem'];
    if (is_array($item)) {
        return "RUNNING GAME: " . c64_upper($item['name']) . "\nREADY.";
    } else {
        $_SESSION['currentPage'] = $item;
        return "RUNNING PAGE: " . c64_upper($item);
    }
}
?>
