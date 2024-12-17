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
    // Variations: LOAD "PAGE",8 or LOAD PAGE,8 or LOAD PAGE 8 or LOAD PAGE
    // We'll normalize by extracting the word after LOAD
    if (preg_match('/^load\s*"([^"]+)"\s*,?\s*8?$/i', $input, $m)) {
        $page = trim($m[1]);
        return ['type'=>'LOAD','target'=>$page];
    } elseif (preg_match('/^load\s*([^\s,]+)\s*,?\s*8?$/i', $input, $m)) {
        $page = trim($m[1], '"');
        $page = trim($page);
        return ['type'=>'LOAD','target'=>$page];
    }

    $upper = strtoupper($input);

    if ($upper === 'LIST') {
        return ['type'=>'LIST'];
    } elseif ($upper === 'RUN') {
        return ['type'=>'RUN'];
    } elseif ($upper === 'HELP') {
        return ['type'=>'HELP'];
    }

    return null;
}

// Call ChatGPT for interpretation
function callChatGPT($prompt, $key) {
    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "You simulate a Commodore BASIC environment. Valid commands: LIST, LOAD\"pagename\",8, RUN, HELP. PAGES: HOME, SHOP, GALLERY, ABOUT, EMU. If user input doesn't match exact commands, interpret. If they mention games, guess they want to load or shop for a game. If they mention a known command, show them how."],
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
        // wrap each line at 80 chars
        while (strlen($line) > 80) {
            $wrapped[] = substr($line,0,80);
            $line = substr($line,80);
        }
        $wrapped[] = $line;
    }
    return implode("\n", $wrapped);
}

// Check if target is page or game
function checkTarget($target, $availablePages, $playableGames, $shopGames) {
    $targetLower = strtolower($target);

    // Check pages
    if (in_array($targetLower, $availablePages)) {
        return ['type'=>'page','name'=>$targetLower];
    }

    // Check game
    return handleGameCheck($targetLower, $playableGames, $shopGames);
}

function handleGameCheck($target, $playableGames, $shopGames) {
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
        // Remove extension
        $gBase = preg_replace('/\..+$/','',$gLower);
        if ($gBase === $target) {
            return $g; // exact match
        }
    }
    return null;
}

function addBreadcrumb($cmd, $label) {
    // Only keep one breadcrumb for simplicity
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

// If user asks "what games" or "do you have games"
function detectGameInquiry($input) {
    $lower = strtolower($input);
    if (strpos($lower,'what game')!==false || strpos($lower,'list game')!==false || strpos($lower,'available game')!==false || strpos($lower,'do you have')!==false) {
        return true;
    }
    return false;
}

function showAllGames($playableGames, $shopGames) {
    $output = "AVAILABLE GAMES:\n";
    $all = array_unique(array_merge($playableGames, $shopGames));
    if (empty($all)) {
        $output .= "NO GAMES AVAILABLE.\n";
    } else {
        foreach ($all as $g) {
            $output .= strtoupper($g)."\n";
        }
    }
    return $output;
}


if (!isset($_SESSION['breadcrumbs'])) {
    $_SESSION['breadcrumbs'] = [];
}
if (!isset($_SESSION['loadedPage'])) {
    $_SESSION['loadedPage'] = null;
}

$output = "";
if (isset($_POST['command'])) {
    $input = $_POST['command'];
    $displayInput = strtoupper($input);

    $parsed = parseUserInput($input);

    $lowerIn = strtolower($input);

    if ($parsed) {
        switch($parsed['type']) {
            case 'LIST':
                $dirList = implode("\n", array_map('strtoupper',$availablePages));
                $output .= "DIRECTORY:\n".$dirList."\n";
                break;
            case 'HELP':
                $output .= "COMMANDS:\nLIST\nLOAD\"PAGENAME\",8\nRUN\nHELP\nAVAILABLE PAGES: HOME, SHOP, GALLERY, ABOUT, EMU\n";
                addBreadcrumb("LIST","LIST");
                break;
            case 'LOAD':
                $target = $parsed['target'];
                $info = checkTarget($target, $availablePages, $playableGames, $shopGames);
                if ($info['type'] === 'page') {
                    $output .= "LOADED \"{$info['name']}\"\nTYPE RUN TO START.\n";
                    $_SESSION['loadedPage'] = $info['name'];
                    addBreadcrumb("RUN","RUN");
                } elseif ($info['type'] === 'gameinfo') {
                    $output .= "PROGRAM \"".strtoupper($target)."\" FOUND:\n";
                    if ($info['playable'] && $info['forSale']) {
                        $output .= "THIS GAME IS BOTH PLAYABLE (EMU) AND FOR SALE (SHOP).\nTYPE RUN TO GO TO EMU OR USE LOAD\"SHOP\",8 TO VISIT SHOP.\n";
                        $_SESSION['loadedPage'] = 'emu';
                        addBreadcrumb("RUN","PLAY GAME");
                    } elseif ($info['playable']) {
                        $output .= "THIS GAME IS PLAYABLE IN EMU.\nTYPE RUN TO PLAY.\n";
                        $_SESSION['loadedPage'] = 'emu';
                        addBreadcrumb("RUN","PLAY GAME");
                    } elseif ($info['forSale']) {
                        $output .= "THIS GAME IS AVAILABLE IN SHOP.\nTYPE RUN TO VIEW SHOP.\n";
                        $_SESSION['loadedPage'] = 'shop';
                        addBreadcrumb("RUN","GO SHOP");
                    }
                } else {
                    // Not found as page or game
                    $output .= "FILE NOT FOUND.\n" . showAllGames($playableGames, $shopGames);
                    addBreadcrumb("HELP","HELP");
                }
                break;
            case 'RUN':
                if ($_SESSION['loadedPage']) {
                    $p = $_SESSION['loadedPage'];
                    $centered = "[ WELCOME TO THE ".strtoupper($p)." PAGE! ]";
                    // center it roughly
                    $centeredLine = str_pad($centered, 80, " ", STR_PAD_BOTH);
                    $output .= "RUNNING '$p' PAGE...\n".$centeredLine."\n";
                    addBreadcrumb("HELP","HELP");
                } else {
                    $output .= "NO PROGRAM LOADED. USE LOAD\"PAGENAME\",8 FIRST.\n";
                    addBreadcrumb("LIST","LIST");
                }
                break;
        }
    } else {
        // Not a known direct command
        // Check if user wants games
        if (detectGameInquiry($input)) {
            $output .= showAllGames($playableGames, $shopGames);
            addBreadcrumb("HELP","HELP");
        } else {
            // Call ChatGPT as fallback
            $chatResponse = callChatGPT($input, $openai_api_key);
            $output .= $chatResponse."\n";

            // If mention 'game' in the fallback
            if (strpos($lowerIn,'game')!==false) {
                if (!empty($playableGames) || !empty($shopGames)) {
                    $output .= "TRY LOAD\"GAMENAME\",8 THEN RUN.\n";
                    addBreadcrumb("LIST","LIST");
                } else {
                    $output .= "NO GAMES AVAILABLE.\n";
                    addBreadcrumb("HELP","HELP");
                }
            } elseif (strpos($lowerIn,'shop')!==false) {
                $output .= "TO VISIT SHOP: LOAD\"SHOP\",8 THEN RUN.\n";
                addBreadcrumb("LOAD\"SHOP\",8","GO SHOP");
            } else {
                addBreadcrumb("HELP","HELP");
            }
        }
    }

    // Show user input at top
    $output = "> $displayInput\n".$output;
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
    font-family: 'C64 Pro', monospace;
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
#hints {
    margin-top: 10px;
    font-size: 0.9em;
    color: #0f0;
    border: 1px solid #0f0;
    padding: 5px;
    position: relative;
    max-width: 600px;
}
#hint-content {
    display: none;
    margin-top:5px;
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

<div id="hints">
  <strong>HINTS & INSTRUCTIONS</strong>
  <button type="button" id="toggleHint">EXPAND</button>
  <div id="hint-content">
    <p>SAMPLE INQUIRIES:</p>
    <ul>
      <li>WHAT GAMES DO YOU HAVE?</li>
      <li>HOW DO I LOAD HOME?</li>
      <li>CAN I VISIT THE SHOP?</li>
      <li>I WANT TO PLAY A GAME NAMED 'PACMAN'</li>
      <li>SHOW ME ALL AVAILABLE PAGES</li>
      <li>HELP</li>
      <li>LIST</li>
      <li>LOAD MINE2049,8 THEN RUN</li>
    </ul>
  </div>
</div>

<script>
document.querySelectorAll('.breadcrumb-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const cmd = btn.getAttribute('data-cmd');
        const input = document.getElementById('cmdInput');
        input.value = cmd;
        document.getElementById('cmdForm').submit();
    });
});

const toggleBtn = document.getElementById('toggleHint');
const hintContent = document.getElementById('hint-content');
toggleBtn.addEventListener('click', () => {
  if (hintContent.style.display === 'none' || hintContent.style.display === '') {
    hintContent.style.display = 'block';
    toggleBtn.textContent = 'COLLAPSE';
  } else {
    hintContent.style.display = 'none';
    toggleBtn.textContent = 'EXPAND';
  }
});
</script>
</body>
</html>
