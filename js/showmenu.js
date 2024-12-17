// js/showmenu.js

function runShowMenu() {
    return new Promise((resolve) => {
        console.log('Showing menu...');

        const purpleRect = document.getElementById('purple-rectangle');
        if (!purpleRect) {
            console.error('#purple-rectangle not found');
            resolve();
            return;
        }

        if (document.getElementById('menu-container')) {
            console.log('Menu already exists');
            resolve();
            return;
        }

        const menuContainer = document.createElement('div');
        menuContainer.id = 'menu-container';
        menuContainer.style.display = 'flex';
        menuContainer.style.justifyContent = 'space-evenly';
        menuContainer.style.alignItems = 'center';
        menuContainer.style.gap = '20px';
        menuContainer.style.width = '100%';
        menuContainer.style.fontFamily = 'C64 Pro, monospace';
        menuContainer.style.fontSize = '2vh';
        menuContainer.style.color = 'white';

        const links = [
            { text: 'HOME', page: '#' },
            { text: 'SHOP', page: '#' },
            { text: 'GALLERY', page: '#' },
            { text: 'ABOUT US', page: '#' },
            { text: 'ACCOUNT', page: '#' }
        ];

        links.forEach(linkData => {
            const link = document.createElement('a');
            link.textContent = linkData.text;
            link.href = linkData.page;
            link.style.color = 'white';
            link.style.textDecoration = 'none';
            link.style.cursor = 'pointer';
            link.addEventListener('mouseenter', () => link.style.textDecoration = 'underline');
            link.addEventListener('mouseleave', () => link.style.textDecoration = 'none');
            menuContainer.appendChild(link);
        });

        purpleRect.appendChild(menuContainer);

        console.log('Menu displayed');
        resolve();
    });
}

window.runShowMenu = runShowMenu;

