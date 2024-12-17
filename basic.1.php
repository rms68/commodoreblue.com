<?php
session_start();

// basic-navigation.php
// Integrate ChatGPT for interpreting user inputs into Commodore BASIC-style commands.

// Configure your OpenAI API key
$openai_api_key = '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';

// Known valid BASIC commands for navigation
$validCommands = [
    'LIST',       // Show directory/content
    'LOAD',       // LOAD"pagename",8
    'RUN',        // RUN the loaded page/program
    'HELP'        // Show help for commands
];

// Pages in your "site"
$availablePages = ['HOME', 'SHOP', 'GALLERY', 'ABOUT', 'EMU'];

// Load game list from games.txt (emu/ROMS/games.txt)
$gamesFile = __DIR__ . '/emu/ROMS/games.txt';
$availableGames = [];
if (file_exists($gamesFile)) {
    $lines = file($gamesFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            // Store base name in uppercase for easy match
            $base = explode('.', $line)[0];
            $availableGames[] = strtoupper($base);
        }
    }
}

// Function to parse user input and see if it's a valid command
function parseUserInput($input) {
    $input = trim($input);
    $input = strtoupper($input); // Force uppercase
    // BASIC commands:
    // LIST
    // HELP
    // LOAD"pagename",8
    // RUN

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

// Function to call the ChatGPT API
function callChatGPT($prompt, $openai_api_key) {
    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "You are simulating a Commodore BASIC navigation system. Valid commands: LIST, LOAD\"pagename\",8 then RUN to open a page. HELP for command info. Available pages: HOME, SHOP, GALLERY, ABOUT, EMU. If user input doesn't match these commands, interpret their intent, suggest proper BASIC commands, or provide a quick link. If user mentions GAMES, PLAY ARCADE, EMULATOR, EMU or wants to play games, suggest SHOP (for games for sale) or EMU (to play). If user says YES after suggestion, automatically load and run that page. If user tries to load a game name from games.txt, highlight that game."],
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
        return "Error: " . curl_error($ch);
    }
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status !== 200) {
        return "Error: HTTP status $http_status returned from API. Raw response: $response";
    }

    $res = json_decode($response, true);
    if (isset($res['choices'][0]['message']['content'])) {
        return $res['choices'][0]['message']['content'];
    } else {
        return "No response from ChatGPT.";
    }
}

// Handle user input (if any)
$output = "";
if (!isset($_SESSION['loadedPage'])) {
    $_SESSION['loadedPage'] = null;
}
if (!isset($_SESSION['awaitingYesNo'])) {
    $_SESSION['awaitingYesNo'] = null;
    $_SESSION['awaitingYesNoPage'] = null;
}
if (!isset($_SESSION['highlightGame'])) {
    $_SESSION['highlightGame'] = null;
}

if (isset($_POST['command'])) {
    $input = strtoupper(trim($_POST['command']));
    if ($_SESSION['awaitingYesNo'] === true) {
        // User was prompted Y/N for navigation
        if ($input === 'Y') {
            // Load and run the page stored in awaitingYesNoPage
            $page = $_SESSION['awaitingYesNoPage'];
            if (in_array($page, $GLOBALS['availablePages'])) {
                $_SESSION['loadedPage'] = $page;
                $output = "LOADED \"$page\"\nType RUN to start.\n";
            } else {
                // Just in case
                $output = "Page not found.\n";
            }
        } else {
            $output = "OK, not navigating.\n";
        }
        $_SESSION['awaitingYesNo'] = null;
        $_SESSION['awaitingYesNoPage'] = null;
    } else {
        // Normal command handling
        $parsed = parseUserInput($input);

        if ($parsed) {
            // Known command
            switch ($parsed['type']) {
                case 'LIST':
                    $output = "DIRECTORY:\n" . implode("\n", $GLOBALS['availablePages']) . "\n";
                    break;
                case 'HELP':
                    $output = "Commands:\nLIST\nLOAD\"pagename\",8\nRUN\nHELP\nAvailable pages: HOME, SHOP, GALLERY, ABOUT, EMU\nTo load a page: LOAD\"HOME\",8 then RUN\n";
                    break;
                case 'LOAD':
                    $page = $parsed['page'];
                    // Check if page is directly available
                    if (in_array($page, $GLOBALS['availablePages'])) {
                        $output = "LOADED \"$page\"\nType RUN to start.\n";
                        $_SESSION['loadedPage'] = $page;
                    } else {
                        // Not a known page, maybe a game?
                        // Check against availableGames
                        if (in_array($page, $GLOBALS['availableGames'])) {
                            // Show the list in games.txt and highlight the requested game
                            $output = "Game found in games.txt!\nGAMES AVAILABLE:\n";
                            foreach ($GLOBALS['availableGames'] as $g) {
                                if ($g === $page) {
                                    $output .= "> " . $g . " < (HIGHLIGHTED)\n";
                                    $_SESSION['highlightGame'] = $g;
                                } else {
                                    $output .= "  " . $g . "\n";
                                }
                            }
                            $output .= "Type RUN to start the emulator with this game.\n";
                            $_SESSION['loadedPage'] = 'EMU'; // Assume EMU page for running game
                        } else {
                            // Page/Game not found
                            $output = "File not found error.\nAvailable pages: " . implode(", ", $GLOBALS['availablePages']) . "\nOr check games with LIST first.\n";
                        }
                    }
                    break;
                case 'RUN':
                    if ($_SESSION['loadedPage'] !== null) {
                        $page = $_SESSION['loadedPage'];
                        // Center "Welcome to X page"
                        $welcomeMessage = "Welcome to the $page page!";
                        // To center, we find the width: assume about 40 chars wide display?
                        // Just add some spacing:
                        $lines = [];
                        $lineWidth = 50;
                        $padSize = intval(($lineWidth - strlen($welcomeMessage))/2);
                        if ($padSize < 0) $padSize = 0;
                        $centeredMsg = str_repeat(' ', $padSize) . $welcomeMessage;
                        $output = "Running '$page' page...\n".$centeredMsg."\n";
                        if ($_SESSION['highlightGame'] !== null) {
                            // If a game was highlighted, we can mention loading that game in EMU
                            $output .= "Loading " . $_SESSION['highlightGame'] . " in emulator...\n";
                            $_SESSION['highlightGame'] = null;
                        }
                    } else {
                        $output = "No program loaded. Use LOAD\"pagename\",8 first.\n";
                    }
                    break;
            }
        } else {
            // Not a known command
            // If input related to games or pages, let's see if user typed something like GAMES, EMU, SHOP
            if (preg_match('/\b(GAMES|PLAY ARCADE|EMULATOR|EMU)\b/i', $input)) {
                // Suggest SHOP or EMU
                $suggestion = "You mentioned games. We have SHOP for games for sale and EMU for playing.\nDo you want to go to SHOP or EMU? (Type LOAD\"SHOP\",8 then RUN or LOAD\"EMU\",8 then RUN)\nOr just type 'Y' to go to SHOP, 'N' to cancel.\n";
                // We'll just prompt them
                $_SESSION['awaitingYesNo'] = true;
                $_SESSION['awaitingYesNoPage'] = 'SHOP'; // Default to SHOP
                $output = $suggestion;
            } else {
                // Use ChatGPT to interpret
                $chatResponse = callChatGPT($input, $openai_api_key);

                // If chat suggests a Y/N, we handle that in future calls
                // If chat suggests going to a page, we can also do that logic
                // For simplicity, just print the response
                $output = "I didn't understand your command.\n" . $chatResponse;

                // If response suggests a quick link, we might want to parse it for Y/N logic
                // This is a simplification: if user says yes on next step, handle above
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Commodore BASIC Navigation Test</title>
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
}
</style>
</head>
<body>
<h1 style="font-family:'C64 Pro', monospace;">Commodore BASIC Navigation (Test)</h1>
<p>Commands: LIST, LOAD"pagename",8, RUN, HELP</p>
<div id="display"><?php echo htmlspecialchars($output); ?></div>
<form method="post">
    <input type="text" name="command" autofocus>
    <button type="submit">Enter</button>
</form>
</body>
</html>
