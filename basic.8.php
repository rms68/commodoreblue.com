<?php
session_start();

$openai_api_key = 'sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

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

// Current page context
if (!isset($_SESSION['currentPage'])) {
    $_SESSION['currentPage'] = null;
}

// Breadcrumbs
if (!isset($_SESSION['breadcrumbs'])) {
    $_SESSION['breadcrumbs'] = [];
}
if (!isset($_SESSION['loadedTarget'])) {
    $_SESSION['loadedTarget'] = null; // can be a page or a game
    $_SESSION['loadedType'] = null;   // 'page' or 'game'
}

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

// Parse user input for LOAD variants
function parseLoad($input) {
    // Remove LOAD keyword
    $pattern = '/\bLOAD\b/i';
    if (!preg_match($pattern, $input)) return null;
    // Remove 'LOAD'
    $rest = preg_replace($pattern, '', $input);
    $rest = trim($rest, "\"' \t,8"); 
    // This tries to extract the main target word (page or game)
    // We assume the first word after LOAD is the target
    $parts = preg_split('/\s+/', $rest);
    if (count($parts) > 0 && $parts[0] !== '') {
        return strtolower($parts[0]);
    }
    return null;
}

function parseUserInput($input) {
    $upper = strtoupper($input);
    if ($upper === 'LIST') return ['type'=>'LIST'];
    if ($upper === 'RUN') return ['type'=>'RUN'];
    if ($upper === 'HELP') return ['type'=>'HELP'];

    // Check LOAD
    $target = parseLoad($input);
    if ($target) return ['type'=>'LOAD','target'=>$target];

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
    // Check if any known game base name is in input
    foreach ($games as $g) {
        $gLower = strtolower($g);
        $gBase = preg_replace('/\..+$/','',$gLower);
        if (strpos($lower, $gBase)!==false) {
            return $g;
        }
    }
    return null;
}

function showContextList($page, $games, $availablePages) {
    if ($page === 'emu') {
        $out = "EMU DIRECTORY:\n";
        foreach ($games as $g) {
            $gBase = preg_replace('/\..+$/','',strtoupper($g));
            $out .= $gBase."\n";
        }
        return $out;
    } else if ($page === 'shop') {
        $out = "SHOP INVENTORY:\n";
        foreach ($games as $g) {
            $gBase = preg_replace('/\..+$/','',strtoupper($g));
            $out .= $gBase."\n";
        }
        return $out;
    } else {
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
                $output .= showContextList($pageContext, $playableGames, $availablePages);
                addBreadcrumb("HELP","HELP");
                break;
            case 'HELP':
                $output .= "COMMANDS:\nLIST\nLOAD PAGENAME,8\nRUN\nHELP\nPAGES: HOME, SHOP, GALLERY, ABOUT, EMU\nTO PLAY A GAME: LOAD GAMENAME,8 THEN RUN\nTO VISIT SHOP: LOAD SHOP,8 THEN RUN\n";
                addBreadcrumb("LIST","LIST");
                break;
            case 'LOAD':
                $target = $parsed['target'];
                // Check if target is a known page
                if (in_array($target, $availablePages)) {
                    $output .= "LOADED \"".strtoupper($target)."\"\nTYPE RUN TO START.\n";
                    $_SESSION['loadedTarget'] = $target;
                    $_SESSION['loadedType'] = 'page';
                    addBreadcrumb("RUN","RUN");
                } else {
                    // Not a page -> treat as game
                    $match = detectGameMention($target, $playableGames);
                    if ($match) {
                        $output .= "LOADED GAME \"".strtoupper($target)."\"\nTYPE RUN TO START.\n";
                        $_SESSION['loadedTarget'] = $target;
                        $_SESSION['loadedType'] = 'game';
                        addBreadcrumb("RUN","PLAY");
                    } else {
                        // Not found
                        $output .= "FILE NOT FOUND.\nAVAILABLE GAMES:\n";
                        foreach ($playableGames as $g) {
                            $gBase = preg_replace('/\..+$/','',strtoupper($g));
                            $output .= $gBase."\n";
                        }
                        addBreadcrumb("HELP","HELP");
                    }
                }
                break;
            case 'RUN':
                if ($_SESSION['loadedTarget']) {
                    if ($_SESSION['loadedType']==='page') {
                        $p = $_SESSION['loadedTarget'];
                        $_SESSION['currentPage'] = $p;
                        $centered = "[ WELCOME TO THE ".strtoupper($p)." PAGE! ]";
                        $line = str_pad($centered,40," ",STR_PAD_BOTH);
                        $output .= "RUNNING '$p' PAGE...\n".$line."\n";
                        addBreadcrumb("HELP","HELP");
                    } else if ($_SESSION['loadedType']==='game') {
                        // Simulate loading a game in emulator
                        $g = $_SESSION['loadedTarget'];
                        $output .= "LOADING GAME '".strtoupper($g)."'...\n";
                        $output .= "SEARCHING...\nFOUND '".strtoupper($g)."' ON DISK...\nLOADING...\n\n";
                        // Add a few lines simulating loading time
                        $output .= "...LOADING COMPLETE...\nGAME STARTED: ".strtoupper($g)."\n";
                        addBreadcrumb("HELP","HELP");
                    }
                } else {
                    $output .= "NO PROGRAM LOADED. USE LOAD PAGENAME,8 OR LOAD GAMENAME,8 FIRST.\n";
                    addBreadcrumb("LIST","LIST");
                }
                break;
        }
    } else {
        // Not a direct command
        $mentionedGame = detectGameMention($input, $playableGames);
        if ($mentionedGame) {
            // Q&A about known game
            $gameBase = preg_replace('/\..+$/','',$mentionedGame);
            $systemPrompt = "User asks about a known Commodore 64 game '$gameBase'. Provide factual, brief info. They can LOAD $gameBase,8 THEN RUN to play, or LOAD SHOP,8 THEN RUN to view shop.";
            $chatResponse = callChatGPT($input, $openai_api_key, $systemPrompt);
            $output .= $chatResponse."\n";
            addBreadcrumb("HELP","HELP");
        } else {
            // Possibly asking about games or pages in general
            if (strpos($lowerIn,'game')!==false || strpos($lowerIn,'play')!==false || strpos($lowerIn,'emu')!==false) {
                // show games
                $output .= "AVAILABLE GAMES:\n";
                foreach ($playableGames as $g) {
                    $gBase = preg_replace('/\..+$/','',strtoupper($g));
                    $output .= $gBase."\n";
                }
                $output .= "TO PLAY: LOAD GAMENAME,8 THEN RUN\n";
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
<p>COMMANDS: LIST, LOAD PAGENAME,8, RUN, HELP</p>
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
