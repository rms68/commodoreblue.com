<?php
session_start();

// **************** CONFIGURATION ****************
// Set your OpenAI API key (assuming it works)
$openai_api_key = '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';

// Available pages (for navigation)
$availablePages = ['home', 'shop', 'gallery', 'about', 'emu'];

// Load local games from games.txt
$gamesFile = __DIR__ . '/emu/ROMS/games.txt';
$playableGames = [];
if (file_exists($gamesFile)) {
    $playableGames = file($gamesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $playableGames = array_map('trim', $playableGames);
    $playableGames = array_filter($playableGames);
}

// Load shop items from datalog.csv
$shopFile = __DIR__ . '/datalog.csv';
$shopGames = [];
if (file_exists($shopFile)) {
    $shopGames = file($shopFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $shopGames = array_map('trim', $shopGames);
    $shopGames = array_filter($shopGames);
}

// Known commands
$validCommands = ['LIST','LOAD','RUN','HELP'];

// Parse user input command
function parseUserInput($input) {
    $input = trim($input);
    $upper = strtoupper($input);

    if ($upper === 'LIST') {
        return ['type' => 'LIST'];
    } elseif ($upper === 'RUN') {
        return ['type' => 'RUN'];
    } elseif ($upper === 'HELP') {
        return ['type' => 'HELP'];
    } elseif (preg_match('/^LOAD\s*"([^"]+)",8$/i', $input, $matches)) {
        $page = trim($matches[1]);
        return ['type' => 'LOAD', 'target' => $page];
    }

    return null;
}

// Call ChatGPT for interpretation
function callChatGPT($prompt, $key) {
    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "You simulate a Commodore BASIC environment. Valid commands: LIST, LOAD\"pagename\",8 then RUN. HELP for info. Pages: home, shop, gallery, about, emu. If user input not match, interpret request and suggest correct BASIC command or a breadcrumb link. If game requested not found, show what we have."],
            ["role" => "user", "content" => $prompt]
        ],
        "temperature" => 0.7
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $key
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return "ERROR: " . curl_error($ch);
    }
    curl_close($ch);

    $res = json_decode($response, true);
    if (isset($res['choices'][0]['message']['content'])) {
        return $res['choices'][0]['message']['content'];
    } else {
        return "NO RESPONSE FROM AI.";
    }
}

// Convert text to uppercase and wrap lines at ~80 chars
function formatOutput($text) {
    $text = strtoupper($text);
    // Wrap text at 80 characters
    $lines = explode("\n", $text);
    $wrapped = [];
    foreach ($lines as $line) {
        $wrapped = array_merge($wrapped, str_split($line, 80));
        $wrapped[] = ""; // line break
    }
    return implode("\n", array_filter($wrapped));
}

// Check if target is a known page, game for shop, or playable game
function checkTarget($target, $availablePages, $playableGames, $shopGames) {
    $targetLower = strtolower($target);

    // Check pages
    if (in_array($targetLower, $availablePages)) {
        return ['type'=>'page', 'name'=>$targetLower];
    }

    // Check playable games (no extension needed)
    // We'll match if the game name (lowercased and stripped extension) contains target
    // For simplicity, exact match ignoring extension:
    $playMatch = findGameMatch($targetLower, $playableGames);
    $shopMatch = findGameMatch($targetLower, $shopGames);

    if (!$playMatch && !$shopMatch) {
        return ['type'=>'notfound','target'=>$targetLower];
    }

    return [
        'type'=>'gameinfo',
        'playable'=>$playMatch ? $playMatch : null,
        'forSale'=>$shopMatch ? $shopMatch : null,
        'requested'=>$targetLower
    ];
}

// Attempt to match user target with games in a list by base name
function findGameMatch($target, $gameList) {
    // We'll say a match if the game line lowercased contains target fully
    // or equals ignoring extension.
    foreach ($gameList as $g) {
        $gLower = strtolower($g);
        // Remove extension from g if any:
        $gBase = preg_replace('/\..+$/','',$gLower);
        if ($gBase === $target) {
            return $g; // exact match
        }
    }
    return null;
}

$output = "";
if (!isset($_SESSION['breadcrumbs'])) {
    $_SESSION['breadcrumbs'] = [];
}
if (!isset($_SESSION['loadedPage'])) {
    $_SESSION['loadedPage'] = null;
}

// Handle user input
if (isset($_POST['command'])) {
    $input = $_POST['command'];
    $inputUpper = strtoupper($input);
    // Force uppercase internally
    // Parse command
    $parsed = parseUserInput($inputUpper);

    if ($parsed) {
        switch($parsed['type']) {
            case 'LIST':
                // List directory
                $dirList = implode("\n", array_map('strtoupper',$availablePages));
                $output .= "DIRECTORY:\n".$dirList."\n";
                break;
            case 'HELP':
                $output .= "COMMANDS:\nLIST\nLOAD\"PAGENAME\",8\nRUN\nHELP\nAVAILABLE PAGES: HOME, SHOP, GALLERY, ABOUT, EMU\n";
                break;
            case 'LOAD':
                $target = $parsed['target'];
                $info = checkTarget($target, $availablePages, $playableGames, $shopGames);
                if ($info['type'] === 'page') {
                    $output .= "LOADED \"{$info['name']}\"\nTYPE RUN TO START.\n";
                    $_SESSION['loadedPage'] = $info['name'];
                    // Breadcrumb: none needed, user just load and run
                } elseif ($info['type'] === 'gameinfo') {
                    $output .= "PROGRAM \"".strtoupper($parsed['target'])."\" FOUND:\n";
                    if ($info['playable'] && $info['forSale']) {
                        $output .= "THIS GAME IS PLAYABLE IN EMU AND ALSO FOR SALE IN SHOP.\n";
                        addBreadcrumb("LOAD\"EMU\",8", "PLAY GAME");
                        addBreadcrumb("LOAD\"SHOP\",8", "BUY GAME");
                    } elseif ($info['playable']) {
                        $output .= "THIS GAME IS PLAYABLE IN THE EMULATOR.\n";
                        addBreadcrumb("LOAD\"EMU\",8","PLAY GAME");
                    } elseif ($info['forSale']) {
                        $output .= "THIS GAME IS AVAILABLE IN OUR SHOP.\n";
                        addBreadcrumb("LOAD\"SHOP\",8","BUY GAME");
                    }
                    $output .= "TYPE RUN TO START.\n";
                    $_SESSION['loadedPage'] = $info['playable'] ? 'emu' : 'shop';
                } else {
                    // Not found
                    $output .= "FILE NOT FOUND.\nHERE IS WHAT WE HAVE:\n";
                    $allGames = array_merge($availablePages, $playableGames, $shopGames);
                    $uniqueList = array_unique($allGames);
                    foreach ($uniqueList as $u) {
                        $output .= strtoupper($u)."\n";
                    }
                    // breadcrumb: maybe HELP
                    addBreadcrumb("HELP","SEE HELP");
                }
                break;
            case 'RUN':
                if ($_SESSION['loadedPage']) {
                    $p = $_SESSION['loadedPage'];
                    // Center welcome message
                    $centered = "[ WELCOME TO THE ".strtoupper($p)." PAGE! ]";
                    // We'll pad the line to ~80 chars and center it
                    $len = strlen($centered);
                    $space = (80 - $len) / 2;
                    if ($space < 0) $space = 0;
                    $centeredLine = str_repeat(" ", (int)$space).$centered;
                    $output .= "RUNNING '$p' PAGE...\n".$centeredLine."\n";
                } else {
                    $output .= "NO PROGRAM LOADED. USE LOAD\"PAGENAME\",8 FIRST.\n";
                }
                break;
        }
    } else {
        // Not a known command, call GPT
        $chatResponse = callChatGPT($input, $openai_api_key);
        $output .= $chatResponse."\n";
        // Decide breadcrumb: If user typed something not recognized, guess:
        // If they mentioned 'game', show EMU or SHOP
        $in = strtolower($input);
        if (strpos($in,'game')!==false) {
            addBreadcrumb("LOAD\"EMU\",8","EMU");
        } elseif (strpos($in,'shop')!==false) {
            addBreadcrumb("LOAD\"SHOP\",8","SHOP");
        } else {
            addBreadcrumb("HELP","HELP");
        }
    }
}

// Functions to handle breadcrumbs
function addBreadcrumb($cmd, $label) {
    // Store one breadcrumb only (the most likely)
    $_SESSION['breadcrumbs'] = [ [$cmd,$label] ];
}

function renderBreadcrumbs() {
    if (empty($_SESSION['breadcrumbs'])) return "";
    $html = "";
    foreach ($_SESSION['breadcrumbs'] as $b) {
        $cmd = htmlspecialchars($b[0]);
        $label = htmlspecialchars($b[1]);
        // A button that when clicked, fills the command input and submits
        $html .= "<button type=\"button\" class=\"breadcrumb-btn\" data-cmd=\"$cmd\">$label</button> ";
    }
    return $html;
}

// Format output uppercase and wrapped
$output = formatOutput($output);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Commodore BASIC Navigation (Test)</title>
<style>
body {
    background: #000;
    color: #0f0;
    font-family: 'Courier New', monospace;
    padding: 20px;
}
#display {
    white-space: pre-wrap;
    border: 2px solid #0f0;
    padding: 10px;
    height: 300px;
    overflow-y: auto;
    background: #000;
    font-family: 'C64 Pro', monospace; /* using commodore font if available */
    font-size: 14px;
    line-height: 1.2em;
}
input[type="text"] {
    width: 80%;
    background: #111;
    color: #0f0;
    border: 1px solid #0f0;
    font-family: 'Courier New', monospace;
    padding: 5px;
    text-transform: uppercase;
}
button {
    background: #222;
    color: #0f0;
    border: 1px solid #0f0;
    padding: 5px 10px;
    cursor: pointer;
    font-family: 'Courier New', monospace;
}
.breadcrumb-btn {
    margin-left: 5px;
}
</style>
</head>
<body>
<h1>COMMODORE BASIC NAVIGATION (TEST)</h1>
<p>COMMANDS: LIST, LOAD"PAGENAME",8, RUN, HELP<br>AVAILABLE PAGES: HOME, SHOP, GALLERY, ABOUT, EMU</p>
<div id="display"><?php echo $output; ?></div>
<form method="post" id="cmdForm">
    <input type="text" name="command" id="cmdInput" autofocus>
    <button type="submit">ENTER</button>
    <?php echo renderBreadcrumbs(); ?>
</form>

<script>
document.querySelectorAll('.breadcrumb-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const cmd = btn.getAttribute('data-cmd');
        const input = document.getElementById('cmdInput');
        input.value = cmd;
        document.getElementById('cmdForm').submit();
    });
});
</script>
</body>
</html>
