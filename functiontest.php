<?php
// functiontest.php
$game = isset($_POST['gamename']) ? $_POST['gamename'] : 'BREAKTHRU.d64';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VICE Emulator - <?php echo htmlspecialchars($game); ?></title>
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
</style>
</head>
<body>
<h1>VICE Emulator - Launch <?php echo htmlspecialchars($game); ?></h1>
<p>Loading '<?php echo htmlspecialchars($game); ?>'...</p>

<!-- Static Canvas -->
<div id="emulator-container">
    <canvas id="vice-canvas" width="640" height="400"></canvas>
</div>
<p id="status">Initializing VICE emulator...</p>

<script>
// Use PHP variable in JavaScript
var gameName = "<?php echo addslashes($game); ?>";

var canvas = document.getElementById('vice-canvas');

// Debug log function
function debugLog(message) {
    console.log(message);
    document.getElementById('status').textContent = message;
}

// Function to (Re)initialize Emulator and Preload PRG
function startEmulator() {
    debugLog("Initializing emulator and preloading " + gameName + "...");

    // Set up Module for VICE Emulator
    window.Module = {
        canvas: canvas,
        arguments: ['-autostart', gameName, '-sounddev', 'sdl', '-soundbufsize', '500'],
        preRun: [function() {
            debugLog("Preloading " + gameName + " into the virtual filesystem...");
            FS.createPreloadedFile('/', gameName, 'emu/ROMS/' + gameName, true, false);
        }],
        print: function(text) {
            debugLog("VICE: " + text);
        },
        printErr: function(text) {
            debugLog("VICE Error: " + text);
        },
        onRuntimeInitialized: function() {
            debugLog("VICE Emulator Initialized. Loading " + gameName + "...");
            canvas.focus();
        }
    };

    // Load x64.js (VICE WebAssembly Build)
    const script = document.createElement('script');
    script.src = 'emu/js/x64.js';
    script.onload = () => debugLog("VICE Emulator loaded successfully.");
    script.onerror = () => debugLog("Failed to load VICE Emulator script.");
    document.body.appendChild(script);
}

// Start emulator automatically
startEmulator();

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
