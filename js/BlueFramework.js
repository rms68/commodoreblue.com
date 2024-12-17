let loadingScreen = document.getElementById('loadingScreen');

function typeWriterEffect(element, text, speed, callback) {
    console.log('typeWriterEffect start:', text);
    let i = 0;
    let interval = setInterval(function() {
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

function flashColors() {
    console.log('flashColors start');
    const c64Colors = ['red', 'cyan', 'purple', 'green', 'yellow', 'blue', 'white', 'orange'];
    let flashCount = 0;
    let flashInterval = setInterval(function() {
        let randomColor1 = c64Colors[Math.floor(Math.random() * c64Colors.length)];
        let randomColor2 = c64Colors[Math.floor(Math.random() * c64Colors.length)];
        let randomColor3 = c64Colors[Math.floor(Math.random() * c64Colors.length)];

        document.getElementById('column1').style.backgroundColor = randomColor1;
        document.getElementById('orange-rectangle').style.backgroundColor = randomColor2;
        document.getElementById('column3').style.backgroundColor = randomColor3;

        flashCount++;
        if (flashCount >= 10) {
            clearInterval(flashInterval);
            document.getElementById('column1').style.backgroundColor = 'red';
            document.getElementById('orange-rectangle').style.backgroundColor = 'orange';
            document.getElementById('column3').style.backgroundColor = 'blue';
            console.log('flashColors complete');
            showMenu();
        }
    }, 100);
}

function showMenu() {
    console.log('showMenu called');
let purpleRect = document.getElementById('purple-rectangle');
purpleRect.innerHTML = '';
let menuContainer = document.createElement('div');
menuContainer.style.display = 'flex';
menuContainer.style.flexDirection = 'row';
menuContainer.style.justifyContent = 'space-evenly'; // Evenly distribute links across purple region
menuContainer.style.alignItems = 'center';
menuContainer.style.gap = '20px';
menuContainer.style.width = '100%';
menuContainer.style.height = '100%';
menuContainer.style.fontFamily = 'C64 Pro, monospace';
menuContainer.style.fontSize = '190%'; // Increased font size
menuContainer.style.lineHeight = '1.14';
menuContainer.style.color = 'white';

const links = [
    { text: 'HOME', page: 'pages/home.php' },
    { text: 'SHOP', page: 'pages/shop.php' },
    { text: 'GALLERY', page: 'pages/gallery.php' },
    { text: 'ABOUT US', page: 'pages/about.php' },
    { text: 'ACCOUNT', page: 'pages/account.php' }
];

links.forEach(linkData => {
    let link = document.createElement('a');
    link.textContent = linkData.text;
    link.style.color = 'white';
    link.style.textDecoration = 'none';
    link.style.cursor = 'pointer';
    link.addEventListener('mouseenter', () => {
        link.style.textDecoration = 'underline';
    });
    link.addEventListener('mouseleave', () => {
        link.style.textDecoration = 'none';
    });
    link.addEventListener('click', (e) => {
        e.preventDefault();
        console.log('Link clicked:', linkData.page);
        // Replace the entire page with the new link
        window.location.href = linkData.page;
    });
    menuContainer.appendChild(link);
});

purpleRect.appendChild(menuContainer);

let marquee = document.createElement('marquee');
marquee.style.fontFamily = 'C64 Pro, monospace';
marquee.style.color = 'white';
marquee.style.fontSize = '3vh';
marquee.style.height = '100%';
marquee.style.display = 'flex';
marquee.style.alignItems = 'center';
marquee.setAttribute('scrollamount', '24'); // Scrolling speed
marquee.textContent = 'WELCOME TO COMMODOREblue.COM';

let orangeRect = document.getElementById('orange-rectangle');
orangeRect.innerHTML = '';
orangeRect.appendChild(marquee);

}

function loadPageContent(url) {
    console.log('loadPageContent called with url:', url);
    fetch(url)
        .then(response => {
            console.log('Response for', url, 'status:', response.status);
            if (!response.ok) {
                console.log('Response not ok for', url);
                return '';
            }
            return response.text();
        })
        .then(data => {
            console.log('Data loaded from:', url);
            console.log('Current loadingScreen content before update:', loadingScreen.innerHTML);
            loadingScreen.innerHTML = data;
            console.log('Updated loadingScreen content from:', url);
            let debugInfo = document.createElement('p');
            debugInfo.style.color = 'yellow';
            debugInfo.innerText = '[DEBUG] Showing content from: ' + url;
            loadingScreen.appendChild(debugInfo);
            console.log('Debug info appended for:', url);
        })
        .catch(error => {
            console.log('Fetch error for', url, ':', error);
            loadingScreen.innerHTML = '[ERROR] Failed to load content.';
        });
}

function loadSequence() {
    console.log('loadSequence start');
    loadingScreen.innerHTML = '';
    typeWriterEffect(loadingScreen, 'LOAD"COMMODOREblue.COM', 50, function() {
        setTimeout(function() {
            loadingScreen.innerHTML += '<br>LOADING...';
            console.log('After LOADING...');
            setTimeout(function() {
                loadingScreen.innerHTML += '<br>READY.<br>█';
                console.log('After READY...');
                setTimeout(function() {
                    loadingScreen.innerHTML += '<br>';
                    console.log('Before typing RUN');
                    typeWriterEffect(loadingScreen, 'RUN', 50, function() {
                        console.log('After typing RUN');
                        setTimeout(function() {
                            console.log('Before flashColors');
                            flashColors();
                        }, 500);
                    });
                }, 500);
            }, 1000);
        }, 1000);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - calling loadSequence');
    loadSequence();
});
