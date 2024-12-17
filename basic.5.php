<?php
session_start();

// **************** CONFIGURATION ****************
$openai_api_key = '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';
$availablePages = ['home', 'shop', 'gallery', 'about', 'emu'];

// Load local playable games from games.txt
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

// Parse user input command (flexible LOAD)
function parseUserInput($input) {
    $input = trim($input);
    $upper = strtoupper($input);

    // Check exact commands
    if ($upper === 'LIST') {
        return ['type' => 'LIST'];
    } elseif ($upper === 'RUN') {
        return ['type' => 'RUN'];
    } elseif ($upper === 'HELP') {
        return ['type' => 'HELP'];
    }

    // Check LOAD in flexible format:
    // Patterns we might accept:
    // LOAD something,8
    // LOAD something 8
    // LOAD something
    // We'll extract the word after LOAD and before ,8 or 8 or end of line.
    if (preg_match('/^LOAD\s+(.+)$/i', $input, $matches)) {
        $rest = trim($matches[1]);
        // Try to find ",8" or " 8" or just end
        $page = $rest;
        // Remove trailing ,8 or 8 if present
        $page = preg_replace('/,?\s*8$/i','',$page);
        $page = trim($page, '" ');
        // Now we have a page name
        // We'll return normalized form internally as LOAD"PAGE",8
        return ['type'=>'LOAD', 'target'=>$page];
    }

    return null;
}

// Call ChatGPT for interpretation
function callChatGPT($prompt, $key) {
    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "You simulate a Commodore BASIC environment. Valid commands: LIST, LOAD\"pagename\",8 then RUN. HELP for info. Pages: home, shop, gallery, about, emu. If user input not match, interpret request and guess what they mean. If they mention a game, try to guide them to LOAD and RUN it, or to SHOP if for sale."],
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
    $lines = explode("\n", $text);
    $wrapped = [];
    foreach ($lines as $line) {
        // wrap each line
        if (strlen($line) > 80) {
            $wrapped = array_merge($wrapped, str_split($line, 80));
        } else {
            $wrapped[] = $line;
        }
        $wrapped[] = "";
    }
    return implode("\n", array_filter($wrapped));
}

// Check if target is page or game
function checkTarget($target, $availablePages, $playableGames, $shopGames) {
    $targetLower = strtolower($target);

    // Check pages first
    if (in_array($targetLower, $availablePages)) {
        return ['type'=>'page', 'name'=>$targetLower];
    }

    // Check as game
    return handleGameCheck($targetLower, $playableGames, $shopGames);
}

function handleGameCheck($target, $playableGames, $shopGames) {
    // Attempt exact match ignoring extension for playable and shop
    $playMatch = findGameMatch($target, $playableGames);
    $shopMatch = findGameMatch($target, $shopGames);

    if (!$playMatch && !$shopMatch) {
        return ['type'=>'notfound','target'=>$target];
    }

    return [
        'type'=>'gameinfo',
        'playable'=>$playMatch ? $playMatch : null,
        'forSale'=>$shopMatch ? $shopMatch : null,
        'requested'=>$target
    ];
}

function findGameMatch($target, $gameList) {
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

function addBreadcrumb($cmd, $label) {
    $_SESSION['breadcrumbs'] = [ [$cmd,$label] ];
}

function renderBreadcrumbs() {
    if (empty($_SESSION['breadcrumbs'])) return "";
    $html = "";
    foreach ($_SESSION['breadcrumbs'] as $b) {
        $cmd = htmlspecialchars($b[0]);
        $label = htmlspecialchars($b[1]);
        $html .= "<button type=\"button\" class=\"breadcrumb-btn\" data-cmd=\"$cmd\">$label</button> ";
    }
    return $html;
}

// Main logic
$output = "";
if (!isset($_SESSION['breadcrumbs'])) {
    $_SESSION['breadcrumbs'] = [];
}
if (!isset($_SESSION['loadedPage'])) {
    $_SESSION['loadedPage'] = null;
}

if (isset($_POST['command'])) {
    $input = $_POST['command'];
    $input = trim($input);

    // Always display user input in uppercase
    $displayInput = strtoupper($input);

    $parsed = parseUserInput($input);

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
                    // single breadcrumb: maybe RUN
                    addBreadcrumb("RUN","RUN");
                } elseif ($info['type'] === 'gameinfo') {
                    $output .= "PROGRAM \"".strtoupper($target)."\" FOUND:\n";
                    if ($info['playable'] && $info['forSale']) {
                        $output .= "THIS GAME IS PLAYABLE (EMU) AND AVAILABLE IN SHOP.\nTYPE RUN TO PLAY.\n";
                        addBreadcrumb("RUN","PLAY GAME");
                    } elseif ($info['playable']) {
                        $output .= "THIS GAME IS PLAYABLE IN THE EMULATOR.\nTYPE RUN TO PLAY.\n";
                        addBreadcrumb("RUN","PLAY GAME");
                    } elseif ($info['forSale']) {
                        $output .= "THIS GAME IS AVAILABLE IN OUR SHOP.\nTYPE RUN TO VIEW SHOP.\n";
                        $_SESSION['loadedPage'] = 'shop';
                        addBreadcrumb("RUN","GO SHOP");
                    }
                    // If game both playable and for sale, still just run leads to either page?
                    // We'll default to EMU first if playable. If user wants shop, they must load shop explicitly.
                    if ($info['playable']) {
                        $_SESSION['loadedPage'] = 'emu';
                    } else {
                        $_SESSION['loadedPage'] = 'shop';
                    }

                } else {
                    // Not found
                    $output .= "FILE NOT FOUND.\nHERE IS WHAT WE HAVE:\n";
                    $allGames = array_merge($availablePages, $playableGames, $shopGames);
                    $uniqueList = array_unique($allGames);
                    foreach ($uniqueList as $u) {
                        $output .= strtoupper($u)."\n";
                    }
                    addBreadcrumb("HELP","HELP");
                }
                break;
            case 'RUN':
                if ($_SESSION['loadedPage']) {
                    $p = $_SESSION['loadedPage'];
                    $centered = "[ WELCOME TO THE ".strtoupper($p)." PAGE! ]";
                    $len = strlen($centered);
                    $space = (80 - $len)/2;
                    if ($space<0) $space=0;
                    $centeredLine = str_repeat(" ",(int)$space).$centered;
                    $output .= "RUNNING '$p' PAGE...\n".$centeredLine."\n";
                    // After run, maybe no breadcrumb or just help
                    addBreadcrumb("HELP","HELP");
                } else {
                    $output .= "NO PROGRAM LOADED. USE LOAD\"PAGENAME\",8 FIRST.\n";
                    addBreadcrumb("LIST","LIST");
                }
                break;
        }
    } else {
        // Not a known command, call GPT
        $chatResponse = callChatGPT($input, $openai_api_key);
        $output .= $chatResponse."\n";

        // Attempt to guess from user input if they meant a game or shop
        $lowerIn = strtolower($input);
        if (strpos($lowerIn,'game')!==false) {
            // They want a game. Show them how to load a known game or show emulator.
            // If we have any playableGames, we pick one example:
            if (!empty($playableGames)) {
                $example = preg_replace('/\..+$/','',$playableGames[0]);
                $output .= "TRY: LOAD\"$example\",8 THEN RUN TO PLAY A GAME.\n";
                addBreadcrumb("LOAD\"$example\",8","TRY EXAMPLE");
            } else {
                $output .= "NO GAMES AVAILABLE.\n";
                addBreadcrumb("HELP","HELP");
            }
        } elseif (strpos($lowerIn,'shop')!==false) {
            $output .= "TO VISIT SHOP: LOAD\"SHOP\",8 THEN RUN.\n";
            addBreadcrumb("LOAD\"SHOP\",8","GO SHOP");
        } else {
            // Default help
            addBreadcrumb("HELP","HELP");
        }
    }

    // Prepend user input
    $output = "> ".strtoupper($input)."\n".$output;
}

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
    font-family: 'C64 Pro', monospace; /* commodore font if available */
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
<p>COMMANDS: LIST, LOAD PAGENAME,8, RUN, HELP<br>AVAILABLE PAGES: HOME, SHOP, GALLERY, ABOUT, EMU</p>
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
