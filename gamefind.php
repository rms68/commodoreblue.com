<?php
/* ***********************************************************************
   Compare ECWID "Games" products with local games by base name.
   Requirements:
   - No hard-coded ECWID data: we rely on $ecwidProducts being set externally.
   - Local games come from `emu/ROMS/games.txt`.
   - Show games present in both ECWID and local on the first column.
   - Show games present locally but not on ECWID on the second column.
   - Handle case-insensitive base name comparison.
   - Add debugging and error checks to avoid 500 errors.
*********************************************************************** */

// Enable debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if $ecwidProducts is defined and is an array
if (!isset($ecwidProducts) || !is_array($ecwidProducts)) {
    $ecwidProducts = [];
}

// Read local games from file
$gameListFile = __DIR__ . '/emu/ROMS/games.txt';
$localGames = [];
if (file_exists($gameListFile) && is_readable($gameListFile)) {
    $lines = file($gameListFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $localGames[] = $line;
            }
        }
    } else {
        // Could not read lines
        // We'll just have $localGames empty
    }
} else {
    // File not found or not readable
    // $localGames will be empty
}

// Function to extract base name without extension, case-insensitive
function getBaseNameNoExt($filename) {
    $parts = explode('.', $filename);
    $base = $parts[0]; 
    return strtolower($base);
}

// Convert both to base names
$productBaseNames = array_map('getBaseNameNoExt', $ecwidProducts);
$localGameBaseNames = array_map('getBaseNameNoExt', $localGames);

// Find intersect: games in both ECWID and local
$inBoth = [];
// Find local only: games in local but not in ECWID
$localOnly = [];

foreach ($localGames as $lg) {
    $lgBase = getBaseNameNoExt($lg);
    if (in_array($lgBase, $productBaseNames, true)) {
        $inBoth[] = $lg; 
    } else {
        $localOnly[] = $lg;
    }
}

// For debugging, you can uncomment these lines to see the arrays:
// echo "<pre>"; print_r($ecwidProducts); print_r($localGames); print_r($inBoth); print_r($localOnly); echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ECWID vs Local Games Comparison</title>
<style>
    body {
        background: #1a1a1a;
        color: #ccc;
        font-family: "Trebuchet MS", sans-serif;
        margin: 20px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    th, td {
        border: 1px solid #444;
        padding: 10px;
        vertical-align: top;
    }
    th {
        background: #000044;
        color: #6f8fff;
        font-size: 18px;
        text-align: left;
    }
    td {
        background: #000033;
        font-size: 14px;
    }
    h1 {
        color: #6f8fff;
        margin-bottom: 20px;
    }
    .no-data {
        color: #ff0000;
    }
</style>
</head>
<body>

<h1>ECWID Games vs Local Games (By Base Name)</h1>

<?php if (empty($ecwidProducts) && empty($localGames)): ?>
    <p class="no-data">No ECWID products and no local games found.</p>
<?php elseif (empty($ecwidProducts)): ?>
    <p class="no-data">No ECWID products found. Cannot compare.</p>
<?php elseif (empty($localGames)): ?>
    <p class="no-data">No local games found. Cannot compare.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Games in Both ECWID and Local</th>
            <th>Games Local Only (Not in ECWID)</th>
        </tr>
        <tr>
            <td>
                <?php if (empty($inBoth)): ?>
                    <p>No games appear in both lists.</p>
                <?php else: ?>
                    <ul>
                    <?php
                    // Print inBoth using their base names for consistency
                    foreach ($inBoth as $game) {
                        echo "<li>" . htmlspecialchars(getBaseNameNoExt($game)) . "</li>";
                    }
                    ?>
                    </ul>
                <?php endif; ?>
            </td>
            <td>
                <?php if (empty($localOnly)): ?>
                    <p>No games appear locally only.</p>
                <?php else: ?>
                    <ul>
                    <?php
                    foreach ($localOnly as $game) {
                        echo "<li>" . htmlspecialchars(getBaseNameNoExt($game)) . "</li>";
                    }
                    ?>
                    </ul>
                <?php endif; ?>
            </td>
        </tr>
    </table>
<?php endif; ?>

</body>
</html>
