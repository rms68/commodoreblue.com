<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Launch VICE Emulator</title>
<style>
body {
    background: #000;
    color: #0f0;
    font-family: 'Courier New', monospace;
    text-align: center;
    margin: 0;
    padding: 0;
}
input[type="text"] {
    color: #0f0;
    background: #000;
    border: 1px solid #0f0;
    font-family: 'Courier New', monospace;
    font-size: 16px;
    padding: 5px;
    width: 300px;
    text-align: center;
    margin-top: 20px;
}
input[type="submit"] {
    color: #0f0;
    background: #000;
    border: 1px solid #0f0;
    font-family: 'Courier New', monospace;
    font-size: 16px;
    padding: 5px 20px;
    margin: 20px;
    cursor: pointer;
}
</style>
</head>
<body>
<h1>VICE Emulator - Enter Game Name</h1>
<p>Type the exact name of the PRG file you want to load (e.g. "ms pacman.prg")</p>
<form action="functiontest.php" method="post">
    <input type="text" name="gamename" placeholder="e.g. ms pacman.prg" required autofocus><br>
    <input type="submit" value="Launch Emulator">
</form>
</body>
</html>
