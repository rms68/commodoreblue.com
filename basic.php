<?php
// Read games from emu/ROMS/games.txt
$gameListFile = __DIR__ . '/emu/ROMS/games.txt';
$allowedGames = [];
if (file_exists($gameListFile)) {
    $lines = file($gameListFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $allowedGames[] = $line;
        }
    }
} else {
    // If no file, fallback to a small list or empty
    $allowedGames = ["BATMAN.D64","MINE2049.T64","MS PACMAN.PRG"];
}

// Normalize function
function normalizeGameName($name) {
    // Remove punctuation, convert to lowercase, remove spaces
    $name = strtolower($name);
    $name = preg_replace('/[^a-z0-9]/', '', $name);
    return $name;
}

// Prepare normalized game names
$normalizedGames = [];
foreach ($allowedGames as $g) {
    $parts = explode('.', $g);
    $base = $parts[0];
    $normalizedGames[normalizeGameName($base)] = $g;
}

// Pass the game data to JavaScript
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Commodore BASIC Navigation</title>
<style>
@font-face {
    font-family: 'C64 Pro';
    src: url('fonts/C64_Pro_Mono-STYLE.ttf');
}
body {
    background: #000;
    color: #0f0;
    font-family: 'C64 Pro', monospace;
    font-size: 18px;
    margin: 0;
    padding: 20px;
}
#screen {
    width: 100%;
    height: 80vh;
    background: #000;
    border: 2px solid #0f0;
    padding: 10px;
    box-sizing: border-box;
    overflow-y: auto;
    position: relative;
}
#input-line {
    display: flex;
    align-items: center;
    margin-top: 10px;
}
#cmd-input {
    background: #000;
    border: none;
    color: #0f0;
    font-family: 'C64 Pro', monospace;
    font-size: 18px;
    flex: 1;
    outline: none;
}
#popup {
    position: absolute;
    top: 20px;
    left: 20px;
    background: #111;
    color: #fff;
    padding: 10px;
    border: 1px solid #0f0;
    display: none;
}
</style>
</head>
<body>
<div id="screen">
    <div>
        <span>**** COMMODOREblue PORTAL V1 *****</span><br>
        <span>COMMUNITY SHOPPING PLAYING WITH TONS BYTES FREE</span><br><br>
        <span>Type HELP for commands.</span><br>
        <span>Type LIST for available games.</span><br><br>
    </div>
    <div id="output"></div>
    <div id="popup"></div>
</div>
<div id="input-line">
    <span>READY.</span>&nbsp;<span>█</span>
    <input type="text" id="cmd-input" autocomplete="off" autofocus>
</div>

<!-- Emulator code (similar from previous) -->
<audio id="pop-sound" src="pop.mp3" preload="auto" style="display:none;"></audio>
<script>
const allowedGames = <?php echo json_encode($allowedGames); ?>;
const normalizedGames = <?php echo json_encode($normalizedGames); ?>;

const output = document.getElementById('output');
const cmdInput = document.getElementById('cmd-input');
const popup = document.getElementById('popup');

// Commands: LOAD"filename", RUN, LIST, HELP
// On LOAD errors: show partial suggestions if close matches
// On syntax error: show popup with commands

// Normalization function
function normalizeName(name) {
    return name.toLowerCase().replace(/[^a-z0-9]/g, '');
}

function printLine(text) {
    const line = document.createElement('div');
    line.textContent = text;
    output.appendChild(line);
    output.scrollTop = output.scrollHeight;
}

function showPopup(msg, buttons) {
    // msg is a string
    // buttons is an array of {label, callback}
    popup.innerHTML = '';
    const p = document.createElement('div');
    p.textContent = msg;
    popup.appendChild(p);
    if (buttons) {
        buttons.forEach(btnDef => {
            const b = document.createElement('button');
            b.textContent = btnDef.label;
            b.style.margin = '5px';
            b.onclick = btnDef.callback;
            popup.appendChild(b);
        });
    }
    popup.style.display = 'block';
}

function hidePopup() {
    popup.style.display = 'none';
}

function listGames() {
    printLine('GAMES AVAILABLE:');
    allowedGames.forEach(g => {
        const base = g.split('.')[0];
        printLine('  ' + base.toUpperCase());
    });
}

let loadedGame = null;

// In actual scenario, integrate emulator code: reinitEmulator, etc.
// For now just simulate:
function loadGame(name) {
    // name is user-typed inside LOAD""
    // strip quotes
    let raw = name.replace(/"/g,'').trim();
    if (!raw) {
        printLine('?SYNTAX ERROR');
        return;
    }

    // correct spelling if close
    let norm = normalizeName(raw);
    if (normalizedGames[norm]) {
        loadedGame = normalizedGames[norm];
        printLine('LOADING ' + loadedGame + ' ...');
        setTimeout(() => {
            printLine('GAME LOADED.');
            // here you would call reinitEmulator and run the emulator
        }, 500);
    } else {
        // try fuzzy match
        let candidates = Object.keys(normalizedGames).filter(k=>k.includes(norm)||norm.includes(k));
        if (candidates.length == 1) {
            // Auto correct
            const correct = candidates[0];
            loadedGame = normalizedGames[correct];
            printLine('DID YOU MEAN "'+loadedGame.split('.')[0].toUpperCase()+'"?');
            printLine('LOADING ' + loadedGame + ' ...');
            setTimeout(() => {
                printLine('GAME LOADED.');
            }, 500);
        } else if (candidates.length > 1) {
            printLine('?AMBIGUOUS REQUEST. POSSIBLE MATCHES:');
            candidates.forEach(c => {
                printLine('  '+normalizedGames[c]);
            });
        } else {
            printLine('?FILE NOT FOUND');
            printLine('Type LIST for available games.');
        }
    }
}

function runCommand(cmd) {
    cmd = cmd.trim();
    if (!cmd) return;

    printLine(cmd);
    cmd = cmd.toUpperCase();

    if (cmd === 'HELP') {
        printLine('COMMANDS:');
        printLine('  LIST - Show available games');
        printLine('  LOAD"GAME" - Load a game (e.g. LOAD"MS PACMAN")');
        printLine('  RUN - Run loaded game (if not auto-run)');
        printLine('  HELP - Show this help');
    } else if (cmd === 'LIST') {
        listGames();
    } else if (cmd.startsWith('LOAD')) {
        // expect LOAD"something"
        let match = cmd.match(/^LOAD"(.*)"$/i);
        if (!match) {
            // try spell correct: RUM -> RUN
            // Here we do a simple correction: if user typed 'RUM', correct to 'RUN'
            // but actually user typed load incorrectly?
            let corrected = cmd.replace('RUM','RUN'); // simplistic approach
            if (corrected != cmd) {
                printLine('DID YOU MEAN RUN?');
            } else {
                // Show popup for syntax error
                showPopup('?SYNTAX ERROR', [
                    {label:'HELP', callback:()=>{hidePopup(); printLine('Type HELP for instructions.');}}
                ]);
            }
            return;
        }
        let filename = match[1];
        loadGame(filename);
    } else if (cmd === 'RUN') {
        if (loadedGame) {
            printLine('RUNNING ' + loadedGame + '...');
        } else {
            printLine('?NOTHING LOADED');
        }
    } else {
        // Unknown command
        // show popup with commands
        showPopup('?SYNTAX ERROR', [
            {label:'HELP', callback:()=>{hidePopup(); printLine('Type HELP for instructions.');}}
        ]);
    }
}

cmdInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        let val = cmdInput.value;
        cmdInput.value = '';
        hidePopup();
        runCommand(val);
    }
});

cmdInput.focus();
</script>
<!-- This is where we'd integrate emulator code if needed. -->
</body>
</html>
