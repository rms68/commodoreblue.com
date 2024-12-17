<?php 
/* ***********************************************************************
   Reverted code structure with requested changes:
   - The initial page is now a 'welcome' page (instead of 'landing-page').
   - On page load, we show 'welcome-page' and run the loading sequence.
   - After loading and flashing sequences finish, switch to 'home-page'.
   - The 'welcome-page' does not have a menu item in the navigation.
   - The menu only appears once we get to the 'home' page.
   - Ecwid code (Store ID: 108400041) is in its own 'ECWID' section, 
     and only loads into 'shop-page' when #shop is selected.
   - Commenting structure and code blocks remain consistent with the 
     previously requested format.

   Key Steps:
   1. Show welcome-page at start, run load sequence and flashing.
   2. After done, switch to home-page, show menu with Home, Shop, Gallery, About Us.
*********************************************************************** */
?>

<?php
// EMU logic: read games and print them. No linking outside pages. All inside index.php.
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
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php /* ***********************************************************************
                            START CSS STYLING
*********************************************************************** */ ?>
<style>
    <?php /* ********************** RESET & VARIABLES *********************** */ ?>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        --color-64body: #3e31a1; /* Blue-violet */
        --color-64border: #7c71da; /* Light purple */
        --color-menufont: #7c71da;
        --color-menuback: #3e31a1;
        --color-green: #008000;
        --color-yellow: #ffff00;
        --color-cyan: #00ffff;
        --color-white: #ffffff;
    }

    body {
        display: flex;
        flex-direction: column;
        height: 100vh;
        font-family: 'C64 Pro', monospace;
        color: var(--color-white);
    }

    @font-face {
        font-family: 'C64 Pro';
        src: url('fonts/C64_Pro_Mono-STYLE.ttf');
    }

    <?php /* ********************** MARQUEE (orange) *********************** */ ?>
    #orange-rectangle {
        background-color: var(--color-64border);
        width: 100%;
        height: 10vh;
        display: flex;
        align-items: center;
        padding-left: 20px;
        font-size: 2vh;
        position: relative;
        overflow: hidden;
    }

    <?php /* ********************** NAVIGATION / TITLE (purple) *********************** */ ?>
    #purple-rectangle,
    #purple-rectangle-home,
    #purple-rectangle-shop,
    #purple-rectangle-gallery,
    #purple-rectangle-about,
    #purple-rectangle-emu {
        background-color: var(--color-64body);
        width: 100%;
        padding: 10px 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        font-size: 1.8vh;
        line-height: 1.4;
    }

    <?php /* ********************** BORDERS (left and right columns) *********************** */ ?>
    #main-content {
        display: flex;
        width: 100%;
        height: 100%;
    }

    #column1 {
        background-color: var(--color-64border);
        flex: 0 0 5%;
        height: 100%;
    }

    #column3 {
        background-color: var(--color-64border);
        flex: 0 0 5%;
        height: 100%;
    }

    <?php /* ********************** MAINPAGE (green content area) *********************** */ ?>
    #main-content-area {
        background-color: var(--color-64body);
        flex: 1;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: flex-start;
        font-size: 2vh;
        line-height: 1.3;
        color: var(--color-64border);
        position: relative;
    }

    #load-output {
        white-space: pre-wrap;
    }

    /* Page containers: welcome, home, shop, gallery, about, emu */
    #welcome-page,
    #home-page,
    #shop-page,
    #gallery-page,
    #about-page,
    #emu-page {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        display: none;
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-start;
    }

    /* Initially show welcome-page as landing sequence */
    #welcome-page {
        display: flex;
    }

    <?php /* ********************** MENU STYLING *********************** */ ?>
    #menu-container ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        display: flex;
        justify-content: space-evenly;
        width: 100%;
    }

    #menu-container li {
        margin: 0 20px;
    }

    #menu-container a {
        text-decoration: none;
        font-size: 2vh;
        background-color: var(--color-64border);
        color: var(--color-64body);
        padding: 5px 10px;
        border-radius: 5px;
        transition: background-color 0.3s, color 0.3s;
        display: inline-block;
        box-sizing: border-box;
    }

    #menu-container a.active {
        background-color: var(--color-white) !important;
        color: var(--color-64border) !important;
    }

    #menu-container a:hover {
        background-color: var(--color-64body);
        color: var(--color-64border);
    }

    #shop-page {
        align-items: center;
        color: var(--color-white);
    }
</style>
<?php /* ********************** END CSS STYLING *********************** */ ?>

</head>
<body>
    <?php /* ***********************************************************************
                            START HTML STRUCTURE
    *********************************************************************** */ ?>
    <div id="orange-rectangle"></div>
    <div id="main-content">
        <div id="column1"></div>
        <div id="main-content-area">
            <?php /* ********************** WELCOME PAGE *********************** */ ?>
            <div id="welcome-page">
                <div id="purple-rectangle">
                    <span>**** COMMODORE 64 BASIC V2 ****></span><br>
                    <span>64K RAM SYSTEM 38911 BASIC BYTES FREE</span>
                </div>
                <div id="load-output"></div>
            </div>
            <?php /* ********************** END WELCOME PAGE *********************** */ ?>

            <?php /* ********************** HOME PAGE *********************** */ ?>
            <div id="home-page">
                <div id="purple-rectangle-home"></div>
                <div style="margin:10px;">
                    <!-- Greeting for the Home Page -->
                    <p>HELLO, WELCOME TO THE HOME PAGE!</p>
                </div>
            </div>
            <?php /* ********************** END HOME PAGE *********************** */ ?>

<?php /* ********************** SHOP PAGE *********************** */ ?>
<div id="shop-page">
    <div id="purple-rectangle-shop"></div>
    <div style="margin:10px; text-align:center;">
        <!-- Greeting for the Shop Page -->
        <p>HELLO, WELCOME TO THE SHOP PAGE!</p>

        <!-- Ecwid Integration Code -->
        <div id="my-store-108400041"></div>
        <div>
            <script data-cfasync="false" type="text/javascript" src="https://app.ecwid.com/script.js?108400041&data_platform=code" charset="utf-8"></script>
            <script type="text/javascript">
                xProductBrowser("id=my-store-108400041");
            </script>
        </div>
        <!-- End Ecwid Integration Code -->
    </div>
</div>
<?php /* ********************** END SHOP PAGE *********************** */ ?>

            <?php /* ********************** GALLERY PAGE *********************** */ ?>
            <div id="gallery-page">
                <div id="purple-rectangle-gallery"></div>
                <div style="margin:10px;">
                    <!-- Greeting for the Gallery Page -->
                    <p>HELLO, WELCOME TO THE GALLERY PAGE!</p>
                </div>
            </div>
            <?php /* ********************** END GALLERY PAGE *********************** */ ?>

            <?php /* ********************** ABOUT US PAGE *********************** */ ?>
            <div id="about-page">
                <div id="purple-rectangle-about"></div>
                <div style="margin:10px;">
                    <!-- Greeting for the About Us Page -->
                    <p>HELLO, WELCOME TO THE ABOUT US PAGE!</p>
                </div>
            </div>
            <?php /* ********************** END ABOUT US PAGE *********************** */ ?>

<?php /* ********************** EMU PAGE *********************** */ ?>
<div id="emu-page">
    <div id="purple-rectangle-emu"></div>
    <div id="emu-content" style="margin:10px; display:flex; flex-direction:column; align-items:center; justify-content:center;">
        <div style="display:flex; align-items:flex-start;">
            <div style="background:#000033; border:2px solid #7c71da; padding:10px; max-width:300px; height:300px; overflow:auto; font-size:1vh; margin-right:10px;">
                <h3 style="color:#6f8fff; text-align:center;">FOR DEREK</h3>
                <ul style="list-style:none; padding:0; margin:0;">
                    <?php if (!empty($allowedGames)): 
                        foreach ($allowedGames as $g): ?>
                    <li style="margin:5px 0; text-align:center;">
                        <a class="game-link" href="#" data-game="<?php echo htmlspecialchars($g); ?>" style="color:#fff; background:#000044; padding:3px 6px; border:1px solid #333; border-radius:4px; display:inline-block; font-size:1vh;">
                            <?php echo htmlspecialchars($g); ?>
                        </a>
                    </li>
                    <?php endforeach; else: ?>
                    <li style="color:#fff; text-align:center;">No games available</li>
                    <?php endif; ?>
                </ul>
                <div style="text-align:center; margin-top:5px;">
                    <button class="reset-button" style="color:#fff; background:#333; border:1px solid #666; border-radius:4px; padding:3px 6px; font-size:1vh; cursor:pointer;">Reset Emulator</button>
                </div>
            </div>
            <canvas id="emulatorContainer" width="640" height="400" style="background:#000; border:3px solid #333;"></canvas>
            <div style="margin-left:10px;">
                <button class="reset-c64-button" style="color:#fff; background:#444; border:1px solid #666; border-radius:4px; padding:5px 10px; font-size:1vh; cursor:pointer;">Reset C64</button>
            </div>
        </div>

        <div style="margin-top:10px; background:#000033; padding:10px; height:200px; overflow-y:auto; box-sizing:border-box;">
            <h3 style="color:#6f8fff; margin:5px 0;">Retro VICE Window</h3>
            <div id="retroWindow" style="background:#0000AA; color:#fff; font-family:'Courier New', monospace; font-size:14px; padding:5px; margin-top:5px; height:150px; overflow-y:auto; border:1px solid #333; line-height:1.2em; white-space:pre-wrap;">
            </div>
        </div>
    </div>
    <script>
    function loadGame(game) {
        debugRetro('PRINT" I AM GOD"');
        currentGame = game;
        const ext = game.split('.').pop().toLowerCase();
        let args = [];
        if (!game) {
            args = []; 
        } else {
            if (ext === 'crt') {
                args = ['-cartcrt', 'emu/ROMS/' + game];
            } else if (ext === 'prg' || ext === 'd64' || ext === 't64') {
                args = ['-autostart', 'emu/ROMS/' + game];
            } else {
                args = ['-autostart', 'emu/ROMS/' + game];
            }
        }
        reinitEmulator(args, game);
    }
    </script>
</div>
<?php /* ********************** END EMU PAGE *********************** */ ?>


        </div>
        <div id="column3"></div>
    </div>
    <?php /* ***********************************************************************
                            END HTML STRUCTURE
    *********************************************************************** */ ?>

<?php /* ********************** START JAVASCRIPT CODE *********************** */ ?>
<script>
    // New variable to allow skipping loading if 'S' is pressed
    let skipLoading = false;

    // On page load, restore lastHash if available to ensure refreshing returns to same page
    const storedHash = localStorage.getItem('lastHash');
    if (storedHash) {
        window.location.hash = storedHash;
    }

    // Add event listener for 'S' key to skip loading sequence
    document.addEventListener('keydown', (e) => {
        if (e.key.toLowerCase() === 's') {
            skipLoading = true;
        }
    });

    function debugRetro(msg) {
        const retroElem = document.getElementById('retroWindow');
        if (retroElem) {
            const line = document.createElement('div');
            line.textContent = msg;
            retroElem.appendChild(line);
            retroElem.scrollTop = retroElem.scrollHeight;
        }
        console.log(msg);
    }

    function typeWriterEffect(element, text, speed, callback) {
        let i = 0;
        const interval = setInterval(() => {
            if (skipLoading) {
                clearInterval(interval);
                if (callback) callback();
                return;
            }
            if (i < text.length) {
                element.innerHTML += text.charAt(i);
                i++;
            } else {
                clearInterval(interval);
                if (callback) callback();
            }
        }, speed);
    }

    function getMenu() {
        const pages = ['home','shop','gallery','about-us','emu'];
        const activeClass = (h) => currentHash === h ? 'active' : '';
        return `
        <?php /* ********************** MENU *********************** */ ?>
        <div id="menu-container">
            <ul>
                <li><a href="#home" class="${activeClass('home')}">Home</a></li>
                <li><a href="#shop" class="${activeClass('shop')}">Shop</a></li>
                <li><a href="#gallery" class="${activeClass('gallery')}">Gallery</a></li>
                <li><a href="#about-us" class="${activeClass('about-us')}">About Us</a></li>
                <li><a href="#emu" class="${activeClass('emu')}">EMU</a></li>
            </ul>
        </div>
        <?php /* ********************** END MENU *********************** */ ?>
        `;
    }

    function showPage(pageId) {
        const pages = ['welcome-page', 'home-page', 'shop-page', 'gallery-page', 'about-page','emu-page'];
        pages.forEach(p => {
            document.getElementById(p).style.display = (p === pageId) ? 'flex' : 'none';
        });
    }

    function showMenuOnPage(pagePrefix) {
        const purpleRect = document.getElementById(`purple-rectangle-${pagePrefix}`);
        if (purpleRect) {
            purpleRect.innerHTML = getMenu();
        }
        return Promise.resolve();
    }

    async function handleNavigation() {
        let hash = window.location.hash.replace('#', '');
        if (!hash) {
            hash = 'home';
            window.location.hash = '#home';
        }

        const validPages = ['home','shop','gallery','about-us','emu'];
        currentHash = validPages.includes(hash) ? hash : 'home';

        let targetPage = 'home-page';
        let pagePrefix = 'home';

        switch(currentHash) {
            case 'shop': targetPage='shop-page'; pagePrefix='shop'; break;
            case 'gallery': targetPage='gallery-page'; pagePrefix='gallery'; break;
            case 'about-us': targetPage='about-page'; pagePrefix='about'; break;
            case 'emu': targetPage='emu-page'; pagePrefix='emu'; break;
            default: targetPage='home-page'; pagePrefix='home'; break;
        }

        showPage(targetPage);
        showMenuOnPage(pagePrefix).then(() => {
            if (currentHash === 'emu') {
                setupEmuLinks(); 
            }
        });

        // Store current hash to restore on refresh
        localStorage.setItem('lastHash', window.location.hash);
    }

    function runLoadSequence() {
        return new Promise((resolve) => {
            const loadOutput = document.getElementById('load-output');
            loadOutput.innerHTML = '';
            loadOutput.innerHTML += '<br>READY.<br>█';
            setTimeout(() => {
                if (skipLoading) { resolve(); return; }
                loadOutput.innerHTML += '<br>';
                typeWriterEffect(loadOutput, 'LOAD"COMMODOREblue.COM', 50, () => {
                    if (skipLoading) { resolve(); return; }
                    setTimeout(() => {
                        if (skipLoading) { resolve(); return; }
                        loadOutput.innerHTML += '<br>LOADING...';
                        setTimeout(() => {
                            if (skipLoading) { resolve(); return; }
                            loadOutput.innerHTML += '<br>READY.<br>█';
                            setTimeout(() => {
                                if (skipLoading) { resolve(); return; }
                                loadOutput.innerHTML += '<br>';
                                typeWriterEffect(loadOutput, 'RUN', 50, () => {
                                    if (skipLoading) { resolve(); return; }
                                    setTimeout(() => {
                                        if (skipLoading) { resolve(); return; }
                                        loadOutput.innerHTML += '<br>HELLO, WELCOME TO THE WELCOME PAGE!<br>';
                                        resolve();
                                    }, 500);
                                });
                            }, 500);
                        }, 1000);
                    }, 1000);
                });
            }, 500);
        });
    }

    function runFlashing() {
        return new Promise((resolve) => {
            const c64Colors = ['red', 'cyan', 'purple', 'green', 'yellow', 'blue', 'white', 'orange'];
            let flashCount = 0;
            const totalFlashes = 10;
            const flashInterval = setInterval(() => {
                if (skipLoading) {
                    clearInterval(flashInterval);
                    resolve();
                    return;
                }
                const randomColor1 = c64Colors[Math.floor(Math.random() * c64Colors.length)];
                const randomColor2 = c64Colors[Math.floor(Math.random() * c64Colors.length)];
                const randomColor3Val = c64Colors[Math.floor(Math.random() * c64Colors.length)];

                document.getElementById('column1').style.backgroundColor = randomColor1;
                document.getElementById('orange-rectangle').style.backgroundColor = randomColor2;
                document.getElementById('column3').style.backgroundColor = randomColor3Val;

                flashCount++;
                if (flashCount >= totalFlashes) {
                    clearInterval(flashInterval);
                    document.getElementById('column1').style.backgroundColor = 'var(--color-64border)';
                    document.getElementById('orange-rectangle').style.backgroundColor = 'var(--color-64border)';
                    document.getElementById('column3').style.backgroundColor = 'var(--color-64border)';
                    resolve();
                }
            }, 100);
        });
    }

    function runShowMarquee() {
        return new Promise((resolve) => {
            const orangeRect = document.getElementById('orange-rectangle');
            orangeRect.innerHTML = '';
            resolve();
        });
    }

    const marqueePlaylist = [
        { type: 'text', content: 'WELCOME TO COMMODOREblue.COM' },
        { type: 'image', src: 'images/logo.png' },
        { type: 'text', content: 'ENJOY OUR SELECTION!' }
    ];
    let currentIndex = 0;

    function runTextMarquee(text) {
        return new Promise((resolve) => {
            const orangeRect = document.getElementById('orange-rectangle');
            orangeRect.innerHTML = '';
            const span = document.createElement('span');
            span.textContent = text;
            span.style.whiteSpace = 'nowrap';
            span.style.position = 'absolute';
            span.style.right = '-100%';
            span.style.fontSize = '2vh';
            span.style.top = '50%';
            span.style.transform = 'translateY(-50%)';
            span.style.fontFamily = 'C64 Pro, monospace';

            orangeRect.appendChild(span);
            let position = orangeRect.offsetWidth;
            function animateText() {
                position -= 2; 
                span.style.right = position + 'px';
                if (position + span.offsetWidth < 0) {
                    resolve();
                } else {
                    requestAnimationFrame(animateText);
                }
            }
            requestAnimationFrame(animateText);
        });
    }

    function runWavyImageMarquee(imageSrc) {
        return new Promise((resolve) => {
            const orangeRect = document.getElementById('orange-rectangle');
            orangeRect.innerHTML = '';
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            canvas.style.display = 'block';
            canvas.style.position = 'absolute';
            canvas.style.top = '50%';
            canvas.style.transform = 'translateY(-50%)';

            orangeRect.appendChild(canvas);
            const img = new Image();
            img.src = imageSrc;

            img.onload = () => {
                const halfWidth = Math.floor(img.width / 2);
                const halfHeight = Math.floor(img.height / 2);
                canvas.width = halfWidth;
                canvas.height = halfHeight;

                let time = 0;
                const amplitude = 20;
                const frequency = 0.02;
                const waveSpeed = 0.05;
                const scrollSpeed = 2;
                let globalShift = orangeRect.offsetWidth;
                function drawFrame() {
                    requestAnimationFrame(drawFrame);
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    for (let y = 0; y < canvas.height; y++) {
                        const waveOffset = Math.sin(y * frequency - time * waveSpeed) * amplitude;
                        const totalOffset = globalShift + waveOffset;
                        ctx.drawImage(img, 0, y*2, img.width, 2, totalOffset, y, canvas.width, 1);
                    }
                    globalShift -= scrollSpeed;
                    if (globalShift + canvas.width < 0) {
                        resolve();
                        return;
                    }
                    time++;
                }
                requestAnimationFrame(drawFrame);
            };

            img.onerror = () => {
                resolve();
            };
        });
    }

    function runNextMarqueeItem() {
        const item = marqueePlaylist[currentIndex];
        let animationPromise;
        if (item.type === 'text') {
            animationPromise = runTextMarquee(item.content);
        } else if (item.type === 'image') {
            animationPromise = runWavyImageMarquee(item.src);
        }
        animationPromise.then(() => {
            currentIndex = (currentIndex + 1) % marqueePlaylist.length;
            runNextMarqueeItem();
        });
    }

    function reinitEmulator(args, game) {
        const existingScript = document.querySelector('script[src="emu/js/x64.js"]');
        if (existingScript) {
            existingScript.remove();
        }

        args = args.concat(['-sounddev', 'sdl', '-soundbufsize', '500']);

        window.Module = {
            canvas: document.getElementById('emulatorContainer'),
            arguments: args,
            preRun: [function() {
                try { FS.mkdir('emu'); } catch(e){}
                try { FS.mkdir('emu/ROMS'); } catch(e){}
                if (game) {
                    FS.createPreloadedFile('emu/ROMS', game, 'emu/ROMS/' + game, true, false);
                }
            }],
            print: function(text) {
                debugRetro(text);
            },
            printErr: function(text) {
                debugRetro("[ERR]: " + text);
            },
            onRuntimeInitialized: function() {
                debugRetro("Runtime initialized. Running emulator...");
                if (typeof Module.run === 'function') {
                    Module.run();
                }

                setTimeout(() => {
                    sendKeysToEmulator('PRINT" RONY IS GOD"\r');
                }, 1000);
            }
        };

        const script = document.createElement('script');
        script.src = 'emu/js/x64.js';
        script.onload = () => {
            debugRetro("x64.js loaded successfully with args: " + args.join(' '));
        };
        script.onerror = () => {
            debugRetro("Failed to load x64.js script.");
        };
        document.body.appendChild(script);
    }

    function loadGame(game) {
        currentGame = game;
        const ext = game.split('.').pop().toLowerCase();
        let args = [];
        if (!game) {
            args = []; 
        } else {
            if (ext === 'crt') {
                args = ['-cartcrt', 'emu/ROMS/' + game];
            } else if (ext === 'prg' || ext === 'd64' || ext === 't64') {
                args = ['-autostart', 'emu/ROMS/' + game];
            } else {
                args = ['-autostart', 'emu/ROMS/' + game];
            }
        }
        reinitEmulator(args, game);
    }

    function resetEmulatorToBoot() {
        debugRetro("Resetting to boot screen (no game)...");
        currentGame = null;
        reinitEmulator([], null);
    }

    function sendKeysToEmulator(keys) {
        debugRetro("Sending keys to emulator: " + keys);
    }

    function setupEmuLinks() {
        const emuContentDiv = document.getElementById('emu-content');
        const links = emuContentDiv.querySelectorAll('.game-link');
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const game = link.getAttribute('data-game');
                loadGame(game);
            });
        });

        const resetBtn = emuContentDiv.querySelector('.reset-button');
        if (resetBtn) {
            resetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                resetEmulatorToBoot();
            });
        }

        const resetC64Btn = emuContentDiv.querySelector('.reset-c64-button');
        if (resetC64Btn) {
            resetC64Btn.addEventListener('click', (e) => {
                e.preventDefault();
                resetEmulatorToBoot();
            });
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'F1') {
            e.preventDefault();
            debugRetro("F1 pressed, sending to emulator...");
            sendKeysToEmulator('\x85');
        }
    });

    (async function main() {
        await runLoadSequence(); 
        await runFlashing();
        runShowMarquee().then(runNextMarqueeItem);
        window.addEventListener('hashchange', handleNavigation);
        handleNavigation();
    })();
</script>
<?php /* ********************** END JAVASCRIPT CODE *********************** */ ?>

<?php /* ********************** ECWID *********************** */ ?>
<!-- Ecwid code block (Store ID: 108400041). Will be inserted into shop page dynamically -->
<div id="ecwid-code-container" style="display:none;">
    <div id="my-store-108400041" data-ecwid="productBrowser"></div>
    <script data-cfasync="false" src="https://app.ecwid.com/script.js?108400041" type="text/javascript"></script>
    <script type="text/javascript">Ecwid.init();</script>
</div>
<?php /* ********************** END ECWID *********************** */ ?>

<!-- Do not link to outside pages, just load x64.js initially. The reinitEmulator will remove/add it again -->
<script src="emu/js/x64.js"></script>
</body>
</html>
