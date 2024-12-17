<?php
session_start();

// basic-navigation.php
// Integrate ChatGPT for interpreting user inputs into Commodore BASIC-style commands and enhanced game logic.
// No HTML in text window; all text uppercase; show page navigation buttons below output if needed.

$openai_api_key = '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';

// Known commands and pages
$validCommands = ['LIST', 'LOAD', 'RUN', 'HELP'];
$availablePages = ['HOME', 'SHOP', 'GALLERY', 'ABOUT', 'EMU'];

// Load game list from games.txt (emu/ROMS/games.txt)
$gamesFile = __DIR__ . '/emu/ROMS/games.txt';
$availableGames = [];
if (file_exists($gamesFile)) {
    $lines = file($gamesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $base = explode('.', $line)[0];
            $availableGames[] = strtoupper($base);
        }
    }
}

// Parse user input
function parseUserInput($input) {
    $input = trim($input);
    $input = strtoupper($input);
    if ($input === 'LIST') {
        return ['type' => 'LIST'];
    } elseif ($input === 'RUN') {
        return ['type' => 'RUN'];
    } elseif ($input === 'HELP') {
        return ['type' => 'HELP'];
    } elseif (preg_match('/^LOAD\s*"([^"]+)",8$/i', $input, $matches)) {
        $page = strtoupper($matches[1]);
        return ['type' => 'LOAD', 'page' => $page];
    }
    return null;
}

// Call ChatGPT
function callChatGPT($prompt, $openai_api_key) {
    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "You are simulating a Commodore BASIC navigation system. Valid commands: LIST, LOAD\"pagename\",8 then RUN, HELP. Pages: HOME, SHOP, GALLERY, ABOUT, EMU. If input doesn't match these commands, interpret and suggest correct BASIC commands or quick links. If user mentions games or tries to load a known C64 game from given list, highlight it. If not found, show what we have. If user tries to navigate pages or asks how to get somewhere, you can say 'WOULD YOU LIKE ME TO TAKE YOU THERE?' Also mention page names that can be navigated to. DO NOT USE ANY HTML IN YOUR RESPONSE. Just mention the page names in uppercase."],
            ["role" => "user", "content" => $prompt]
        ],
        "temperature" => 0.7
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openai_api_key
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return "ERROR: " . curl_error($ch);
    }
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status !== 200) {
        return "ERROR: HTTP STATUS $http_status RETURNED. RAW RESPONSE: $response";
    }

    $res = json_decode($response, true);
    if (isset($res['choices'][0]['message']['content'])) {
        return $res['choices'][0]['message']['content'];
    } else {
        return "NO RESPONSE FROM CHATGPT.";
    }
}

function centerMessage($message, $lineWidth = 50) {
    $lines = explode("\n", $message);
    $result = "";
    foreach ($lines as $line) {
        $padSize = intval(($lineWidth - strlen($line))/2);
        if ($padSize < 0) $padSize = 0;
        $result .= str_repeat(' ', $padSize) . $line . "\n";
    }
    return $result;
}

if (!isset($_SESSION['loadedPage'])) {
    $_SESSION['loadedPage'] = null;
}
if (!isset($_SESSION['highlightGame'])) {
    $_SESSION['highlightGame'] = null;
}

$output = "";
if (isset($_POST['command'])) {
    $input = strtoupper(trim($_POST['command']));
    $parsed = parseUserInput($input);

    if ($parsed) {
        switch ($parsed['type']) {
            case 'LIST':
                $output = "DIRECTORY:\n" . implode("\n", $availablePages) . "\n";
                break;
            case 'HELP':
                $output = "COMMANDS:\nLIST\nLOAD\"PAGENAME\",8\nRUN\nHELP\nAVAILABLE PAGES: HOME, SHOP, GALLERY, ABOUT, EMU\nTO LOAD A PAGE: LOAD\"HOME\",8 THEN RUN\n";
                break;
            case 'LOAD':
                $page = $parsed['page'];
                if (in_array($page, $availablePages)) {
                    $output = "LOADED \"$page\"\nTYPE RUN TO START.\n";
                    $_SESSION['loadedPage'] = $page;
                    $_SESSION['highlightGame'] = null;
                } else {
                    // Not a known page. Check games
                    if (in_array($page, $availableGames)) {
                        $output = "GAME FOUND!\nGAMES AVAILABLE:\n";
                        foreach ($availableGames as $g) {
                            if ($g === $page) {
                                $output .= "> " . $g . " < (HIGHLIGHTED)\n";
                                $_SESSION['highlightGame'] = $g;
                            } else {
                                $output .= "  " . $g . "\n";
                            }
                        }
                        $output .= "TYPE RUN TO START THE EMU WITH THIS GAME.\n";
                        $_SESSION['loadedPage'] = 'EMU';
                    } else {
                        // Not in pages, not in games
                        $output = "FILE NOT FOUND.\nWE HAVE THESE PAGES: ".implode(", ", $availablePages)."\nAND THESE GAMES:\n" . implode("\n", $availableGames) . "\n";
                    }
                }
                break;
            case 'RUN':
                if ($_SESSION['loadedPage'] !== null) {
                    $page = $_SESSION['loadedPage'];
                    $welcomeMessage = "WELCOME TO THE $page PAGE!";
                    $centeredMsg = centerMessage($welcomeMessage);
                    $output = "RUNNING '$page' PAGE...\n".$centeredMsg."\n";
                    if ($_SESSION['highlightGame'] !== null) {
                        $output .= "LOADING " . $_SESSION['highlightGame'] . " IN EMULATOR...\n";
                        $_SESSION['highlightGame'] = null;
                    }
                } else {
                    $output = "NO PROGRAM LOADED. USE LOAD\"PAGENAME\",8 FIRST.\n";
                }
                break;
        }
    } else {
        // Not a known command, use ChatGPT
        $chatResponse = callChatGPT($input, $openai_api_key);
        $output = "I DIDN'T UNDERSTAND YOUR COMMAND.\n" . $chatResponse . "\n";
    }
}

// Detect references to pages and store them separately, no HTML in output
$pagesMentioned = [];
foreach ($availablePages as $pg) {
    $pattern = '/\b'.$pg.'\b/i';
    if (preg_match($pattern, $output)) {
        if (!in_array($pg, $pagesMentioned)) {
            $pagesMentioned[] = $pg;
        }
    }
}

// Convert output to uppercase
$output = strtoupper($output);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Commodore BASIC Navigation (Test)</title>
<style>
body {
    background: #000;
    color: #0f0; /* green text like C64 */
    font-family: 'C64 Pro', monospace;
    padding: 20px;
}
#display {
    white-space: pre-wrap;
    border: 2px solid #0f0;
    padding: 10px;
    height: 300px;
    overflow-y: auto;
    background: #000;
    color: #0f0;
    font-family:'C64 Pro', monospace;
    font-size:14px;
}
input[type="text"] {
    width: 80%;
    background: #111;
    color: #0f0;
    border: 1px solid #0f0;
    font-family: 'C64 Pro', monospace;
    padding: 5px;
}
button {
    background: #222;
    color: #0f0;
    border: 1px solid #0f0;
    padding: 5px 10px;
    cursor: pointer;
    font-family: 'C64 Pro', monospace;
    margin-left:10px;
}
.goto-btn {
    margin-right:10px;
    background: #333;
    color: #0f0;
    border: 1px solid #0f0;
}
</style>
</head>
<body>
<h1 style="font-family:'C64 Pro', monospace;">COMMODORE BASIC NAVIGATION (TEST)</h1>
<p>COMMANDS: LIST, LOAD"PAGENAME",8, RUN, HELP</p>
<div id="display"><?php echo htmlspecialchars($output, ENT_QUOTES|ENT_SUBSTITUTE); ?></div>

<?php if (!empty($pagesMentioned)): ?>
<p>QUICK NAVIGATION:</p>
<?php foreach ($pagesMentioned as $pg): ?>
    <button type="button" class="goto-btn" data-goto="<?php echo htmlspecialchars($pg); ?>">GO TO <?php echo htmlspecialchars($pg); ?></button>
<?php endforeach; ?>
<?php endif; ?>

<form method="post">
    <input type="text" name="command" autofocus>
    <button type="submit">ENTER</button>
</form>

<script>
// Add event listener to goto buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('goto-btn')) {
        const page = e.target.getAttribute('data-goto');
        // We'll simulate LOAD"page",8 then RUN
        // First submit LOAD command
        const form = document.createElement('form');
        form.method = 'post';

        // Insert LOAD command
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'command';
        input.value = 'LOAD"'+page+'",8';
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    }
});
</script>
</body>
</html>
