<?php
// galaga.php: Contains the function to add the Galaga ship and handle its logic
?>

<script>
    function addGalagaShip(imagePath) {
        // Create the ship container
        const shipContainer = document.createElement('div');
        shipContainer.id = 'galaga-ship';
        const shipImage = document.createElement('img');
        shipImage.src = imagePath;
        shipImage.alt = 'Galaga Ship';
        shipContainer.appendChild(shipImage);
        document.body.appendChild(shipContainer);

        let position = 50; // Ship position percentage

        // Ship movement
        document.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') position = Math.max(0, position - 5);
            if (event.key === 'ArrowRight') position = Math.min(100, position + 5);
            shipContainer.style.left = `${position}%`;

            if (event.key === ' ') shootBullet();
        });

        function shootBullet() {
            const bullet = document.createElement('div');
            bullet.classList.add('bullet');
            const shipRect = shipContainer.getBoundingClientRect();
            bullet.style.left = `${shipRect.left + shipRect.width / 2 - 2.5}px`;
            bullet.style.top = `${shipRect.top}px`;
            document.body.appendChild(bullet);

            const interval = setInterval(() => {
                bullet.style.top = `${bullet.getBoundingClientRect().top - 10}px`;

                // Check for collision with targets
                document.querySelectorAll('.target').forEach(target => {
                    const targetRect = target.getBoundingClientRect();
                    const bulletRect = bullet.getBoundingClientRect();

                    if (bulletRect.left < targetRect.right &&
                        bulletRect.right > targetRect.left &&
                        bulletRect.top < targetRect.bottom &&
                        bulletRect.bottom > targetRect.top) {
                        window.location.href = target.href; // Redirect on hit
                        bullet.remove();
                        clearInterval(interval);
                    }
                });

                if (bullet.getBoundingClientRect().top < 0) {
                    bullet.remove();
                    clearInterval(interval);
                }
            }, 16);
        }
    }
</script>
