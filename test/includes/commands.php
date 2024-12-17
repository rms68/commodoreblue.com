<?php
function handleCommand($command, $pages, $products) {
    if ($command === 'LIST') {
        return generateListItems($pages, $products);
    } elseif ($command === 'HELP') {
        return "COMMANDS:\nLIST\nLOAD ITEM\nRUN\nHELP";
    } elseif ($command === 'RUN') {
        return runItem($pages, $products);
    } else {
        $itemName = parseLoadCommand($command);
        if ($itemName) {
            return loadItem($itemName, $pages, $products);
        }
        return "SYNTAX ERROR";
    }
}

function parseLoadCommand($input) {
    $parts = preg_split('/\bLOAD\b/i', $input);
    if (count($parts) < 2) return null;
    return strtolower(trim($parts[1], ',8 "'));
}

function generateListItems($pages, $products) {
    $output = "DIRECTORY:\n";
    foreach ($products as $p) {
        $output .= c64_upper($p['product_name']) . "\n";
    }
    foreach ($pages as $page) {
        $output .= c64_upper($page) . "\n";
    }
    return $output;
}

function loadItem($itemName, $pages, $products) {
    foreach ($products as $p) {
        if (strtolower($p['product_name']) === $itemName) {
            $_SESSION['loadedItem'] = $p;
            return "LOADED \"{$p['product_name']}\". TYPE RUN TO START.";
        }
    }
    if (in_array($itemName, $pages)) {
        $_SESSION['loadedItem'] = $itemName;
        return "LOADED \"$itemName\". TYPE RUN TO START.";
    }
    return "ITEM NOT FOUND.";
}

function runItem($pages, $products) {
    if (!isset($_SESSION['loadedItem'])) {
        return "NO ITEM LOADED.";
    }
    $item = $_SESSION['loadedItem'];
    if (is_array($item)) {
        return "RUNNING \"{$item['product_name']}\"...";
    } else {
        $_SESSION['currentPage'] = $item;
        return "WELCOME TO THE " . c64_upper($item) . " PAGE!";
    }
}
