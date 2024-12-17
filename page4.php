<?php
// page1.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Page 1</title>
    <style>
        body { background-color: lightblue; text-align: center; overflow: hidden; }
        h1 { color: navy; }
        .target {
            position: absolute;
            width: 50px;
            height: 50px;
            background-color: orange;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <h1>Welcome to Page 1!</h1>
    <?php
    $pages = ['index.php', 'page2.php', 'page3.php', 'page4.php'];
    foreach ($pages as $index => $page) {
        $top = rand(100, 500);
        $left = rand(100, 900);
        echo "<a href='$page' class='target' style='top: {$top}px; left: {$left}px;'>".($index + 1)."</a>";
    }
    ?>
</body>
</html>
