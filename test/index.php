<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/products.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/commands.php';

$output = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputCmd = strtoupper(trim($_POST['command'] ?? ''));

    if ($inputCmd === 'LIST') {
        $output = generateListItems($pages, $products);
    } elseif ($inputCmd === 'RUN') {
        $output = runItem($products);
    } elseif (strpos($inputCmd, 'LOAD') === 0) {
        $item = parseLoadCommand($inputCmd);
        if (in_array($item, $pages)) {
            $_SESSION['loadedItem'] = $item;
            $output = "LOADED \"$item\". TYPE RUN TO START.";
        } else {
            foreach ($products as $p) {
                if (strtolower($p['name']) === strtolower($item)) {
                    $_SESSION['loadedItem'] = $p;
                    $output = "LOADED \"" . c64_upper($p['name']) . "\". TYPE RUN TO START.";
                    break;
                }
            }
        }
    } else {
        $output = "SYNTAX ERROR. TYPE HELP FOR OPTIONS.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>COMMODORE BASIC NAVIGATION TEST</title>
    <style>
        body { background: #000; color: #0f0; font-family: 'Courier New', monospaloce; padding: 20px; }
        pre { white-space: pre-wrap; }
        input, button { background: #222; color: #0f0; border: 1px solid #0f0; padding: 5px; }
        button:hover { background-color: #0f0; color: #000; }
    </style>
</head>
<body>
    <h1>COMMODORE BASIC NAVIGATION dfdfdTEST</h1>
    <pre><?php echo $output; ?></pre>
    <form method="post">
        <input type="text" name="command" autofocus placeholder="Type command...">
        <button type="submit">ENTER</button>
    </form>
    <p>HINTS: TRY 'LIST', 'LOAD HOME', 'LOAD SHOP', 'LOAD EMU', 'HELP'</p>
</body>
</html>
