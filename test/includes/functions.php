<?php
// test/includes/functions.php

function c64_upper($text) {
    return strtoupper($text);
}

function parseLoadCommand($input) {
    $parts = preg_split('/\bLOAD\b/i', $input);
    if (count($parts) < 2) return null;

    $rest = trim($parts[1]);
    $rest = preg_replace('/,8\s*$/i', '', $rest);
    return trim($rest, '" ');
}

function centerText($text, $width = 40) {
    $len = strlen($text);
    $spaces = max(0, floor(($width - $len) / 2));
    return str_repeat(" ", $spaces) . $text;
}
?>
