<?php
// index.php
// Entry point for Commodoreblue.com TEST directory
// Purpose: This file handles the main homepage logic and outputs HTML content for testing.

///////////////////////////////////////////////
// 1. START SESSION
///////////////////////////////////////////////
session_start(); // Start a session to manage user data.

///////////////////////////////////////////////
// 2. INCLUDE CONFIGURATION FILES
///////////////////////////////////////////////
require_once '../config.php'; // Include site configuration.

///////////////////////////////////////////////
// 3. HANDLE USER AUTHENTICATION
///////////////////////////////////////////////
if (!isset($_SESSION['user'])) {
    // Redirect to login page if user is not authenticated
    header("Location: ../login.php");
    exit();
}

///////////////////////////////////////////////
// 4. PAGE CONTENT LOGIC
///////////////////////////////////////////////
// Load dynamic content for homepage
$page_title = "Commodoreblue TEST Environment";
$welcome_message = "Welcome to the test environment for Commodoreblue!";

///////////////////////////////////////////////
// 5. OUTPUT HTML
///////////////////////////////////////////////
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
</head>
<body>
    <h1><?php echo $page_title; ?></h1>
    <p><?php echo $welcome_message; ?></p>
</body>
</html>
