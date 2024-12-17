
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

