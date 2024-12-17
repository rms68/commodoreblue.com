// js/loadsequence.js

function typeWriterEffect(element, text, speed, callback) {
    console.log('typeWriterEffect start:', text);
    let i = 0;
    let interval = setInterval(() => {
        if (i < text.length) {
            element.innerHTML += text.charAt(i);
            i++;
        } else {
            clearInterval(interval);
            console.log('typeWriterEffect complete:', text);
            if (callback) callback();
        }
    }, speed);
}

/**
 * runLoadSequence
 * Displays LOAD, LOADING..., READY., RUN, then resolves
 */
function runLoadSequence() {
    return new Promise((resolve) => {
        console.log('Running load sequence...');
        const loadOutput = document.getElementById('load-output');
        loadOutput.innerHTML = '';

        typeWriterEffect(loadOutput, 'LOAD"COMMODOREblue.COM', 50, () => {
            setTimeout(() => {
                loadOutput.innerHTML += '<br>LOADING...';
                console.log('After LOADING...');
                setTimeout(() => {
                    loadOutput.innerHTML += '<br>READY.<br>█';
                    console.log('After READY...');
                    setTimeout(() => {
                        loadOutput.innerHTML += '<br>';
                        console.log('Before typing RUN');
                        typeWriterEffect(loadOutput, 'RUN', 50, () => {
                            console.log('After typing RUN');
                            setTimeout(() => {
                                console.log('Load sequence complete');
                                resolve(); // Resolve here, no flashColors called
                            }, 500);
                        });
                    }, 500);
                }, 1000);
            }, 1000);
        });
    });
}

window.runLoadSequence = runLoadSequence;
