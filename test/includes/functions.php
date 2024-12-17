<?php
function c64_upper($text) {
    return strtoupper($text);
}

function centerText($text, $width) {
    $len = strlen($text);
    if ($len >= $width) return $text;
    $spaces = floor(($width - $len) / 2);
    return str_repeat(" ", $spaces) . $text;
}

function currentPage() {
    return isset($_SESSION['currentPage']) ? $_SESSION['currentPage'] : 'home';
}
