<?php
// vice_debug_test.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VICE Emulator Launch Debug</title>
<style>
body {
    background: #000;
    color: #0f0;
    font-family: 'Courier New', monospace;
}
#emulator-container {
    border: 2px solid #0f0;
    width: 640px;
    height: 400px;
    margin: 20px auto;
}
</style>
</head>
<body>
<h1 style="text-align:center;">VICE Emulator Debug Test</h1>
<p style="text-align:center;">Testing launch of 'ms pacman.prg'</p>
<div id="emulator-container">
    <!-- Static Canvas -->
    <canvas id="vice-canvas" width="640" height="400"></canvas>
</div>
<p id="status" style="text-align:center;">Initializing emulator...</p>

<script>
// Static Canvas Reference
var canvas = document.getElementById('vice-canvas');

// Define Module before loading x64.js
var Module = {
  preRun: [],
  postRun: [],
  print: (text) => console.log(text),
  printErr: (text) => console.error(text),
  canvas: canvas, // Assign the static canvas
  noInitialRun: true, // Prevent automatic launch
  onRuntimeInitialized: function() {
    console.log("Emulator runtime initialized.");
    startEmulator('emu/ROMS/ms pacman.prg');
  }
};

// Start the Emulator with a PRG File
function startEmulator(prgPath) {
    fetch(prgPath)
        .then(response => {
            if (!response.ok) {
                throw new Error("Failed to load PRG: " + response.status);
            }
            return response.arrayBuffer();
        })
        .then(data => {
            console.log("PRG file loaded successfully:", prgPath);

            // Write PRG to the emulator's filesystem
            Module['FS_writeFile']('autostart.prg', new Uint8Array(data));

            // Set arguments to autostart the PRG file
            Module['arguments'] = ['-autostart', 'autostart.prg'];

            // Call main to launch the emulator
            Module.callMain();
            document.getElementById('status').textContent = "Emulator running...";
        })
        .catch(error => {
            console.error("Error starting emulator:", error);
            document.getElementById('status').textContent = "Error: " + error.message;
        });
}
</script>

<!-- Load the VICE WebAssembly Build -->
<script src="emu/js/x64.js"></script>
</body>
</html>
