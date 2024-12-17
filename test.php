<?php
// index.php: Single file to handle all "pages" and include Galaga ship logic
$page = isset($_GET['page']) ? $_GET['page'] : 'home'; // Determine the current page
$pageTitles = [
    'home' => 'Welcome to Galaga Navigation!',
    '1' => 'SHOP Page',
    '2' => 'PLAY Page',
    '3' => 'LEARN Page',
    '4' => 'CHAT Page'
];
$pageLabels = [
    '1' => 'SHOP',
    '2' => 'PLAY',
    '3' => 'LEARN',
    '4' => 'CHAT'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitles[$page] ?? 'Galaga Navigation'; ?></title>
    <style>
        /* General Styles */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f0f0f0;
        }
        h1 {
            margin-top: 20px;
            color: #333;
        }
        .page-content {
            padding: 20px;
        }

        /* Targets as navigation links */
        .target {
            position: absolute;
            width: 80px;
            height: 80px;
            background-color: blue;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            border-radius: 50%;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }

        /* Galaga Ship */
        #galaga-ship {
            position: fixed;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
        }
        #galaga-ship img {
            width: 80px;
        }

        .bullet {
            position: fixed;
            width: 5px;
            height: 15px;
            background-color: red;
            z-index: 9998;
        }
    </style>
</head>
<body>
    <h1><?php echo $pageTitles[$page] ?? 'Galaga Navigation'; ?></h1>

    <div class="page-content">
        <?php
        // Render page content based on the query parameter
        if ($page === 'home') {
            echo "<p>Use the ship to hit obstacles and navigate between pages!</p>";
        } else {
            echo "<p>Welcome to the <strong>{$pageLabels[$page]}</strong> page!</p>";
        }
        ?>

        <!-- Randomly Placed Deflector Targets -->
        <?php
        foreach ($pageLabels as $targetPage => $label) {
            $top = rand(150, 500);
            $left = rand(100, 900);
            echo "<a href='?page=$targetPage' class='target' style='top: {$top}px; left: {$left}px;'>$label</a>";
        }
        ?>
    </div>

    <!-- Include Galaga Ship Logic -->
    <?php include 'galaga.php'; ?>

    <script>
        // Call the Galaga function
        addGalagaShip('images/galaga/galaga.png');
    </script>
</body>
</html>
