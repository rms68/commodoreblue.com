<?php
session_start();

$openai_api_key = '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';

// Available pages
$availablePages = ['home', 'shop', 'gallery', 'about', 'emu'];

// Load local playable games
$gamesFile = __DIR__ . '/emu/ROMS/games.txt';
$playableGames = [];
if (file_exists($gamesFile)) {
    $playableGames = file($gamesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $playableGames = array_map('trim', $playableGames);
    $playableGames = array_filter($playableGames);
}

// Current "context" page: We'll store where user currently "is" after RUN
// For simplicity, assume `$_SESSION['currentPage']` after RUN sets our context
if (!isset($_SESSION['currentPage'])) {
    $_SESSION['currentPage'] = null;
}

// Breadcrumbs
if (!isset($_SESSION['breadcrumbs'])) {
    $_SESSION['breadcrumbs'] = [];
}
if (!isset($_SESSION['loadedPage'])) {
    $_SESSION['loadedPage'] = null;
}

$validCommands = ['LIST','LOAD','RUN','HELP'];

function formatOutput($text) {
    $text = strtoupper($text);
    $lines = explode("\n", $text);
    $wrapped = [];
    foreach ($lines as $line) {
        while (strlen($line) > 80) {
            $wrapped[] = substr($line,0,80);
            $line = substr($line,80);
        }
        $wrapped[] = $line;
    }
    return implode("\n", $wrapped);
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

function parseUserInput($input) {
    $input = trim($input);
    $upper = strtoupper($input);

    // LOAD variants
    // Allow LOAD PAGENAME,8 or LOAD PAGENAME or LOAD "PAGENAME",8 etc.
    $loadPatterns = [
        '/^LOAD\s*"([^"]+)"\s*,?\s*8?$/i',
        '/^LOAD\s+([^\s,]+)\s*,?\s*8?$/i'
    ];
    foreach ($loadPatterns as $pat) {
        if (preg_match($pat, $input, $m)) {
            return ['type'=>'LOAD','target'=>trim($m[1], '"')];
        }
    }

    if ($upper === 'LIST') return ['type'=>'LIST'];
    if ($upper === 'RUN') return ['type'=>'RUN'];
    if ($upper === 'HELP') return ['type'=>'HELP'];

    return null;
}

function callChatGPT($prompt, $key, $systemPrompt = null) {
    $messages = [];
    if ($systemPrompt) {
        $messages[] = ["role"=>"system","content"=>$systemPrompt];
    } else {
        $messages[] = ["role"=>"system","content"=>"You simulate a Commodore BASIC environment..."];
    }

    $messages[] = ["role"=>"user","content"=>$prompt];

    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => $messages,
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

function detectGameMention($input, $games) {
    $lower = strtolower($input);
    // If any known game name (without extension) is substring of input
    foreach ($games as $g) {
        $gLower = strtolower($g);
        $gBase = preg_replace('/\..+$/','',$gLower);
        if (strpos($lower, $gBase)!==false) {
            return $g; 
        }
    }
    return null;
}

function showContextList($page, $games) {
    // If in EMU page -> list games
    // If in SHOP page -> list same games (as we use games.txt for now)
    // Otherwise list pages
    if ($page === 'emu') {
        // list games (playable)
        $out = "EMU DIRECTORY:\n";
        foreach ($games as $g) {
            $gBase = preg_replace('/\..+$/','',strtoupper($g));
            $out .= $gBase."\n";
        }
        return $out;
    } elseif ($page === 'shop') {
        // same as above for now
        $out = "SHOP INVENTORY:\n";
        foreach ($games as $g) {
            $gBase = preg_replace('/\..+$/','',strtoupper($g));
            $out .= $gBase."\n";
        }
        return $out;
    } else {
        // default directory listing pages
        global $availablePages;
        $dirList = implode("\n", array_map('strtoupper',$availablePages));
        return "DIRECTORY:\n".$dirList."\n";
    }
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
                $pageContext = $_SESSION['currentPage'];
                $output .= showContextList($pageContext, $playableGames);
                addBreadcrumb("HELP","HELP");
                break;
            case 'HELP':
                $output .= "COMMANDS:\nLIST\nLOAD PAGENAME,8\nRUN\nHELP\nPAGES: HOME, SHOP, GALLERY, ABOUT, EMU\nTO PLAY A GAME: LOAD GAMENAME,8 THEN RUN\nTO VISIT SHOP: LOAD SHOP,8 THEN RUN\n";
                addBreadcrumb("LIST","LIST");
                break;
            case 'LOAD':
                $target = $parsed['target'];
                // Check if target is a known page
                global $availablePages;
                if (in_array(strtolower($target), $availablePages)) {
                    $output .= "LOADED \"".strtoupper($target)."\"\nTYPE RUN TO START.\n";
                    $_SESSION['loadedPage'] = strtolower($target);
                    addBreadcrumb("RUN","RUN");
                } else {
                    // Not a page -> treat as game
                    $match = detectGameMention($target, $playableGames);
                    if ($match) {
                        // known game
                        $output .= "LOADED GAME \"".strtoupper($target)."\"\nTYPE RUN TO START.\n";
                        $_SESSION['loadedPage'] = 'emu'; // playing in EMU
                        addBreadcrumb("RUN","PLAY");
                    } else {
                        // Not found
                        $output .= "FILE NOT FOUND.\n";
                        $output .= "AVAILABLE GAMES:\n";
                        foreach ($playableGames as $g) {
                            $gBase = preg_replace('/\..+$/','',strtoupper($g));
                            $output .= $gBase."\n";
                        }
                        addBreadcrumb("HELP","HELP");
                    }
                }
                break;
            case 'RUN':
                if ($_SESSION['loadedPage']) {
                    $p = $_SESSION['loadedPage'];
                    $_SESSION['currentPage'] = $p;
                    $centered = "[ WELCOME TO THE ".strtoupper($p)." PAGE! ]";
                    $centeredLine = str_pad($centered, 80, " ", STR_PAD_BOTH);
                    $output .= "RUNNING '$p' PAGE...\n".$centeredLine."\n";
                    addBreadcrumb("HELP","HELP");
                } else {
                    $output .= "NO PROGRAM LOADED. USE LOAD PAGENAME,8 FIRST.\n";
                    addBreadcrumb("LIST","LIST");
                }
                break;
        }
    } else {
        // Not a direct command
        $mentionedGame = detectGameMention($input, $playableGames);
        if ($mentionedGame) {
            // User asked about a known game. Call ChatGPT with game context
            $gameBase = preg_replace('/\..+$/','', $mentionedGame);
            $systemPrompt = "User is asking about a known Commodore 64 game called '$gameBase'. Provide factual, brief info. If user wants to play: LOAD \"$gameBase\",8 THEN RUN. If user wants shop: LOAD SHOP,8 THEN RUN.";
            $chatResponse = callChatGPT($input, $openai_api_key, $systemPrompt);
            $output .= $chatResponse."\n";
            addBreadcrumb("HELP","HELP");
        } else {
            // Maybe user is asking about games in general or something else
            if (strpos($lowerIn,'game')!==false || strpos($lowerIn,'play')!==false || strpos($lowerIn,'emu')!==false) {
                // Show all games
                $output .= "AVAILABLE GAMES:\n";
                foreach ($playableGames as $g) {
                    $gBase = preg_replace('/\..+$/','',strtoupper($g));
                    $output .= $gBase."\n";
                }
                $output .= "TO PLAY A GAME: LOAD GAMENAME,8 THEN RUN\n";
                addBreadcrumb("HELP","HELP");
            } else if (strpos($lowerIn,'shop')!==false) {
                $output .= "TO VISIT SHOP: LOAD SHOP,8 THEN RUN.\n";
                addBreadcrumb("LOAD SHOP,8","GO SHOP");
            } else {
                // Use ChatGPT to interpret
                $chatResponse = callChatGPT($input, $openai_api_key);
                $output .= $chatResponse."\n";
                addBreadcrumb("HELP","HELP");
            }
        }
    }

    $output = "> $displayInput\n".$output;
}

$output = formatOutput($output);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>COMMODORE BASIC NAVIGATION (TEST)</title>
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
<p>COMMANDS: LIST, LOAD PAGENAME,8, RUN, HELP<br>
PAGES: HOME, SHOP, GALLERY, ABOUT, EMU<br>
GAMES: SEE GAMES.TXT</p>
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
      <li>WHAT YEAR DID MS PACMAN COME OUT?</li>
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
