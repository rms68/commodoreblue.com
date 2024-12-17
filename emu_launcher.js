// emu_launcher.js: Function to initialize and launch the VICE emulator dynamically

var canvas = null; // Canvas reference
var hasStarted = false; // Prevent multiple starts

// Debug log function
function debugLog(message) {
    console.log(message);
    const display = document.getElementById('display');
    if (display) display.innerHTML += message + "<br>";
}

// Function to Launch VICE Emulator
function launchEmulator(gameFile) {
    if (hasStarted) {
        debugLog("Emulator is already running.");
        return;
    }
    hasStarted = true;

    debugLog(`Starting VICE emulator with game: ${gameFile}...`);

    // Create a canvas dynamically if it doesn't exist
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.id = 'vice-canvas';
        canvas.width = 640;
        canvas.height = 400;
        canvas.style.border = '2px solid #0f0';
        canvas.style.display = 'block';
        document.body.appendChild(canvas);
    }

    // Set up Module for VICE Emulator
    window.Module = {
        canvas: canvas,
        arguments: ['-autostart', gameFile, '-sounddev', 'sdl', '-soundbufsize', '500'],
        preRun: [function() {
            debugLog(`Preloading ${gameFile} into the virtual filesystem...`);
            FS.createPreloadedFile('/', gameFile, 'emu/ROMS/' + gameFile, true, false);
        }],
        print: function(text) {
            debugLog("VICE: " + text);
        },
        printErr: function(text) {
            debugLog("VICE Error: " + text);
        },
        onRuntimeInitialized: function() {
            debugLog("VICE Emulator Initialized.");
            canvas.focus(); // Set focus to the canvas
        }
    };

    // Dynamically load x64.js script
    const script = document.createElement('script');
    script.src = 'emu/js/x64.js';
    script.onload = () => debugLog("VICE Emulator loaded successfully.");
    script.onerror = () => debugLog("Failed to load VICE Emulator script.");
    document.body.appendChild(script);
}

// Catch all JavaScript errors for debugging
window.addEventListener('error', function(event) {
    console.error("JavaScript Error: ", event.message, " at ", event.filename, ":", event.lineno);
});
console.log("emu_launcher.js loaded successfully!");
