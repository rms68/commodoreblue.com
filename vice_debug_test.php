<?php
// index_ms_pacman_f1.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VICE Emulator - ms pacman.prg with F1 Handler</title>
<style>
body {
    background: #000;
    color: #0f0;
    font-family: 'Courier New', monospace;
    text-align: center;
    margin: 0;
    padding: 0;
}
#emulator-container {
    margin: 20px auto;
    width: 640px;
    height: 400px;
    border: 2px solid #0f0;
}
input {
    color: #0f0;
    background: #000;
    border: 1px solid #0f0;
    font-family: 'Courier New', monospace;
    font-size: 16px;
    padding: 5px;
    width: 300px;
    text-align: center;
    margin-top: 20px;
}
</style>
</head>
<body>
<h1>VICE Emulator - Launch ms pacman.prg</h1>
<p>Press Enter to start the emulator and load 'ms pacman.prg'. Use F1 to start the game.</p>

<!-- Input Field for User Interaction -->
<input type="text" id="user-input" placeholder="Press Enter to Start" autofocus>

<!-- Static Canvas -->
<div id="emulator-container">
    <canvas id="vice-canvas" width="640" height="400"></canvas>
</div>
<p id="status">Initializing VICE emulator...</p>

<script>
// Static Canvas
var canvas = document.getElementById('vice-canvas');
var userInput = document.getElementById('user-input');
var hasStarted = false;

// Debug log function
function debugLog(message) {
    console.log(message);
    document.getElementById('status').textContent = message;
}

// Function to (Re)initialize Emulator and Preload PRG
function startEmulator() {
    if (hasStarted) {
        debugLog("Emulator already running.");
        return;
    }
    hasStarted = true;

    debugLog("Initializing emulator and preloading ms pacman.prg...");

    // Set up Module for VICE Emulator
    window.Module = {
        canvas: canvas,
        arguments: ['-autostart', 'ms pacman.prg', '-sounddev', 'sdl', '-soundbufsize', '500'], // Load PRG and enable sound
        preRun: [function() {
            debugLog("Preloading ms pacman.prg into the virtual filesystem...");
            FS.createPreloadedFile('/', 'ms pacman.prg', 'emu/ROMS/ms pacman.prg', true, false);
        }],
        print: function(text) {
            debugLog("VICE: " + text);
        },
        printErr: function(text) {
            debugLog("VICE Error: " + text);
        },
        onRuntimeInitialized: function() {
            debugLog("VICE Emulator Initialized. Loading ms pacman...");
            canvas.focus(); // Explicitly set focus to the canvas
        }
    };

    // Load x64.js (VICE WebAssembly Build)
    const script = document.createElement('script');
    script.src = 'emu/js/x64.js';
    script.onload = () => debugLog("VICE Emulator loaded successfully.");
    script.onerror = () => debugLog("Failed to load VICE Emulator script.");
    document.body.appendChild(script);
}

// Event Listener for Enter Key Press
userInput.addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        startEmulator();
    }
});

// Capture F1 Key and Prevent Chrome Help
document.addEventListener('keydown', function(event) {
    if (event.key === 'F1') {
        event.preventDefault(); // Prevent Chrome Help
        debugLog("F1 key pressed. Sending to emulator...");
        sendKeysToEmulator('\x85'); // Send F1 key to the emulator
    }
});

// Function to Send Key Events to Emulator
function sendKeysToEmulator(keys) {
    debugLog("Sending keys to emulator: " + keys);
    if (typeof Module !== 'undefined' && Module._SDL_SendKey) {
        for (let i = 0; i < keys.length; i++) {
            Module._SDL_SendKey(keys.charCodeAt(i));
        }
    }
}
</script>
</body>
</html>
