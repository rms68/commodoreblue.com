// js/flashing.js
console.log('Debug: flashing.js is running at the top level');

// Remove any showMenu references here if you find them.

window.runFlashing = function runFlashing() {
    console.log('Debug: runFlashing is now defined on window');
    return new Promise((resolve) => {
        console.log('Starting flashing...');
        const c64Colors = ['red', 'cyan', 'purple', 'green', 'yellow', 'blue', 'white', 'orange'];
        let flashCount = 0;
        const totalFlashes = 10;

        const flashInterval = setInterval(() => {
            // NO showMenu calls here
            const randomColor1 = c64Colors[Math.floor(Math.random() * c64Colors.length)];
            const randomColor2 = c64Colors[Math.floor(Math.random() * c64Colors.length)];
            const randomColor3 = c64Colors[Math.floor(Math.random() * c64Colors.length)];

            document.getElementById('column1').style.backgroundColor = randomColor1;
            document.getElementById('orange-rectangle').style.backgroundColor = randomColor2;
            document.getElementById('column3').style.backgroundColor = randomColor3;

            flashCount++;
            if (flashCount >= totalFlashes) {
                clearInterval(flashInterval);
                document.getElementById('column1').style.backgroundColor = 'red';
                document.getElementById('orange-rectangle').style.backgroundColor = 'orange';
                document.getElementById('column3').style.backgroundColor = 'blue';

                console.log('flashColors complete');
                // No showMenu call here!
                resolve();
            }
        }, 100);
    });
};

console.log('Debug: At the end of flashing.js, typeof runFlashing is', typeof runFlashing);
console.log('Debug: At the end of flashing.js, typeof window.runFlashing is', typeof window.runFlashing);
