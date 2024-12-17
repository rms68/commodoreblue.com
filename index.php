<!DOCTYPE html>
<html lang="en">
<head>
<?php /* ***********************************************************************
                            START CSS STYLING
*********************************************************************** */ ?>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Define CSS Variables for Colors */
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
        background-color: var(--color-64border); /* Corrected to use the variable */
        width: 100%;
        height: 10vh;
        display: flex;
        align-items: center;
        padding-left: 20px;
        font-size: 2vh;
        position: relative;
    }
    <?php /* ********************** END MARQUEE (orange) *********************** */ ?>

    <?php /* ********************** NAVIGATION / TITLE (purple) *********************** */ ?>
    #purple-rectangle,
    #purple-rectangle-home,
    #purple-rectangle-shop,
    #purple-rectangle-gallery,
    #purple-rectangle-about {
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
    <?php /* ********************** END NAVIGATION / TITLE (purple) *********************** */ ?>

    <?php /* ********************** BORDERS (left and right columns) *********************** */ ?>
    #main-content {
        display: flex;
        width: 100%;
        height: 100%;
    }

    #column1 {
        background-color: var(--color-64border);
        flex: 0 0 5%;  /* Ensure it takes up 10% of the width */
        height: 100%;
    }

    #column3 {
        background-color: var(--color-64border);
        flex: 0 0 5%;  /* Ensure it takes up 10% of the width */
        height: 100%;
    }
    <?php /* ********************** END BORDERS (left and right columns) *********************** */ ?>

    <?php /* ********************** MAINPAGE (green content area) *********************** */ ?>
    #main-content-area {
        background-color: var(--color-64body);
        flex: 1;  /* Takes up the remaining space */
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: flex-start;
        font-size: 2vh;
        line-height: 1.3;
        color: var(--color-64border); /* Ensure text inside content area is white */
        position: relative;
    }

    #load-output {
        white-space: pre-wrap;
    }

    /* Page containers */
    #landing-page,
    #home-page,
    #shop-page,
    #gallery-page,
    #about-page {
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

    /* Initially show landing-page */
    #landing-page {
        display: flex;
    }

    /* Menu styling with knockout style */
    #menu-container ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        display: flex;
        justify-content: space-evenly;
        width: 100%;
    }

    #menu-container li {
        margin: 10px 0;
    }

    /* Knockout style: background = c64border, font = c64body */
    #menu-container a {
        text-decoration: none;
        font-size: 2vh;
        background-color: var(--color-64border);
        color: var(--color-64body);
        padding: 10px 20px;
        border-radius: 5px;
        transition: background-color 0.3s, color 0.3s;
    }

    #menu-container a:hover {
        background-color: var(--color-64body);
        color: var(--color-64border);
    }
    <?php /* ********************** END MAINPAGE (green content area) *********************** */ ?>
</style>

</head>
<body>
    <?php /* ***********************************************************************
                            START HTML STRUCTURE
*********************************************************************** */ ?>
    <div id="orange-rectangle"></div>
    <div id="main-content">
        <div id="column1"></div>
        <div id="main-content-area">
            <?php /* ********************** LANDING PAGE *********************** */ ?>
            <div id="landing-page">
                <div id="purple-rectangle">
                    <span>**** COMMODORE 64 BASIC V2</span><br>
                    <span>64K RAM SYSTEM 38911 BASIC BYTES FREE</span>
                </div>
                <div id="load-output"></div>
            </div>
            <?php /* ********************** END LANDING PAGE *********************** */ ?>

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
                <div style="margin:10px;">
                    <!-- Greeting for the Shop Page -->
                    <p>HELLO, WELCOME TO THE SHOP PAGE!</p>
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

        </div>
        <div id="column3"></div>
    </div>
    <?php /* ***********************************************************************
                            END HTML STRUCTURE
*********************************************************************** */ ?>

<?php /* ********************** START JAVASCRIPT CODE *********************** */ ?>
<script>
    // A helper function for typing text one character at a time.
    function typeWriterEffect(element, text, speed, callback) {
        let i = 0;
        const interval = setInterval(() => {
            if (i < text.length) {
                element.innerHTML += text.charAt(i);
                i++;
            } else {
                clearInterval(interval);
                if (callback) callback();
            }
        }, speed);
    }

    // Returns the HTML for the menu
    function getMenu() {
        return `
            <div id="menu-container">
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#shop">Shop</a></li>
                    <li><a href="#gallery">Gallery</a></li>
                    <li><a href="#about-us">About Us</a></li>
                </ul>
            </div>
        `;
    }

    // Show the load sequence: READY, LOAD..., LOADING..., READY., RUN
    function runLoadSequence() {
        return new Promise((resolve) => {
            const loadOutput = document.getElementById('load-output');
            loadOutput.innerHTML = '';

            // Display READY and cursor at the start
            loadOutput.innerHTML += '<br>READY.<br>█';

            // Add a slight delay before starting to type the LOAD line
            setTimeout(() => {
                loadOutput.innerHTML += '<br>';
                typeWriterEffect(loadOutput, 'LOAD"COMMODOREblue.COM', 50, () => {
                    setTimeout(() => {
                        loadOutput.innerHTML += '<br>LOADING...';
                        setTimeout(() => {
                            loadOutput.innerHTML += '<br>READY.<br>█';
                            setTimeout(() => {
                                loadOutput.innerHTML += '<br>';
                                typeWriterEffect(loadOutput, 'RUN', 50, () => {
                                    // Finished load sequence
                                    setTimeout(() => {
                                        // Add greeting for Landing Page
                                        loadOutput.innerHTML += '<br>HELLO, WELCOME TO THE LANDING PAGE!<br>';
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

    // Flash the colors a few times, then reset
    function runFlashing() {
        return new Promise((resolve) => {
            const c64Colors = ['red', 'cyan', 'purple', 'green', 'yellow', 'blue', 'white', 'orange'];
            let flashCount = 0;
            const totalFlashes = 10;

            const flashInterval = setInterval(() => {
                const randomColor1 = c64Colors[Math.floor(Math.random() * c64Colors.length)];
                const randomColor2 = c64Colors[Math.floor(Math.random() * c64Colors.length)];
                const randomColor3 = c64Colors[Math.floor(Math.random() * c64Colors.length)];

                document.getElementById('column1').style.backgroundColor = randomColor1;
                document.getElementById('orange-rectangle').style.backgroundColor = randomColor2;
                document.getElementById('column3').style.backgroundColor = randomColor3;

                flashCount++;
                if (flashCount >= totalFlashes) {
                    clearInterval(flashInterval);
                    // Reset to original colors
                    document.getElementById('column1').style.backgroundColor = 'var(--color-64border)';
                    document.getElementById('orange-rectangle').style.backgroundColor = 'var(--color-64border)';
                    document.getElementById('column3').style.backgroundColor = 'var(--color-64border)';
                    resolve();
                }
            }, 100);
        });
    }

    // Show the marquee in the orange section (top of page)
    function runShowMarquee() {
        return new Promise((resolve) => {
            const orangeRect = document.getElementById('orange-rectangle');
            orangeRect.innerHTML = ''; // Clear previous content
            const marquee = document.createElement('marquee');
            marquee.textContent = 'WELCOME TO COMMODOREblue.COM';
            orangeRect.appendChild(marquee);
            resolve();
        });
    }

    // Simple function to show a given page and hide others
    function showPage(pageId) {
        const pages = ['landing-page', 'home-page', 'shop-page', 'gallery-page', 'about-page'];
        pages.forEach(p => {
            document.getElementById(p).style.display = (p === pageId) ? 'flex' : 'none';
        });
    }

    // Show the menu on the current page
    function showMenuOnPage(pagePrefix) {
        const purpleRect = document.getElementById(`purple-rectangle-${pagePrefix}`);
        if (purpleRect) {
            purpleRect.innerHTML = getMenu();
        }
        return Promise.resolve();
    }

    // Handle navigation
    function handleNavigation() {
        const hash = window.location.hash.replace('#', '');
        switch(hash) {
            case 'home':
                showPage('home-page');
                showMenuOnPage('home');
                break;
            case 'shop':
                showPage('shop-page');
                showMenuOnPage('shop');
                break;
            case 'gallery':
                showPage('gallery-page');
                showMenuOnPage('gallery');
                break;
            case 'about-us':
                showPage('about-page');
                showMenuOnPage('about');
                break;
            default:
                // Landing page if no hash or unknown
                showPage('landing-page');
                break;
        }
    }

    // Main function to run everything in order
    async function main() {
        await runLoadSequence();  // Wait for the loading sequence on landing page
        await runFlashing();      // Then run the flashing
        // Now switch to home page as initial "after landing" state
        showPage('home-page');
        await runShowMarquee();   // Show the marquee on home page
        await showMenuOnPage('home'); // Show menu on home page

        // Handle navigation if user clicks a menu link
        window.addEventListener('hashchange', handleNavigation);
    }

    main();  // Run the main function when the page loads
</script>
<?php /* ********************** END JAVASCRIPT CODE *********************** */ ?>
</body>
</html>
