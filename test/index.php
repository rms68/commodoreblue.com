<?php
session_start();
include 'includes/session.php';
include 'includes/functions.php';
include 'includes/commands.php';
include 'includes/products.php';

// Initialize output
$output = "";

// Handle actions and commands
if (isset($_POST['action']) && $_POST['action'] === 'toggleView') {
    $_SESSION['viewMode'] = ($_SESSION['viewMode'] === 'LIST') ? 'THUMBNAIL' : 'LIST';
}

if (isset($_POST['action']) && $_POST['action'] === 'autorun') {
    $_POST['command'] = 'RUN';
}

if (isset($_POST['action']) && $_POST['action'] === 'autolist') {
    $_POST['command'] = 'LIST';
}

if (isset($_POST['command'])) {
    $inputCmd = strtoupper(trim($_POST['command']));
    $output = handleCommand($inputCmd, $pages, $products);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>COMMODORE BASIC NAVIGATION TEST</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>COMMODORE BASIC NAVIGATION TEST</h1>
    <div id="display"><?php echo $output; ?></div>

    <form method="post">
        <input type="text" name="command" autofocus>
        <button type="submit">ENTER</button>
    </form>
    <form method="post">
        <input type="hidden" name="action" value="toggleView">
        <button type="submit">TOGGLE VIEW MODE (CURRENT: <?php echo $_SESSION['viewMode']; ?>)</button>
    </form>

    <p>HINTS: TRY 'LIST', 'LOAD HOME', 'LOAD EMU', 'HELP'.</p>
</body>
</html>
