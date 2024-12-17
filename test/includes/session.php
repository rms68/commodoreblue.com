<?php
// test/includes/session.php
session_start();

// Initialize session variables
if (!isset($_SESSION['viewMode'])) {
    $_SESSION['viewMode'] = 'LIST';
}
if (!isset($_SESSION['currentPage'])) {
    $_SESSION['currentPage'] = 'home';
}
if (!isset($_SESSION['loadedItem'])) {
    $_SESSION['loadedItem'] = null;
}
?>
