<?php
// Pages available
$pages = ['home', 'gallery', 'about', 'emu', 'shop'];

// Default view mode
if (!isset($_SESSION['viewMode'])) {
    $_SESSION['viewMode'] = 'LIST';
}

// Default current page
if (!isset($_SESSION['currentPage'])) {
    $_SESSION['currentPage'] = 'home';
}
