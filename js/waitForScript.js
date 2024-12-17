// js/waitForScript.js

function loadScript(src) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
        document.body.appendChild(script);
    });
}

async function waitForScripts(scripts) {
    for (const scriptInfo of scripts) {
        await loadScript(scriptInfo.src);
        console.log(`${scriptInfo.src} loaded`);

        // Wait a moment to ensure globals are set
        await new Promise(r => setTimeout(r, 50));

        if (typeof window[scriptInfo.run] !== 'function') {
            throw new Error(`Function ${scriptInfo.run} not found after loading ${scriptInfo.src}`);
        }

        await window[scriptInfo.run]();
        console.log(`${scriptInfo.run} completed`);
    }
    console.log('All scripts executed in sequence');
}

export { waitForScripts };
