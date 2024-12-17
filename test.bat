@echo off
REM Setup script for Commodoreblue.com test project

REM Create the folder structure
echo Creating folder structure...
mkdir test
mkdir test\includes
mkdir test\assets
mkdir test\assets\css
mkdir test\assets\js
mkdir test\assets\images
mkdir test\assets\images\products
mkdir test\assets\images\petscii
mkdir test\assets\images\hint-icons
mkdir test\assets\images\swag
mkdir test\assets\videos
mkdir test\vice
mkdir test\vendor
mkdir test\patreon
mkdir test\patreon\premium

REM Create files with initial content
echo Creating files...

REM index.php
echo ^<?php > test\index.php
echo echo "Welcome to the Commodoreblue.com Homepage - Test Environment"; >> test\index.php
echo ^?> >> test\index.php

REM shop.php
echo ^<?php > test\shop.php
echo echo "Welcome to the Shop - Browse Custom Commodore Hardware and Games"; >> test\shop.php
echo ^?> >> test\shop.php

REM tutor.php
echo ^<?php > test\tutor.php
echo session_start(); >> test\tutor.php
echo include 'includes/chatgpt.php'; >> test\tutor.php
echo echo "Commodore BASIC Tutor Page"; >> test\tutor.php
echo ^?> >> test\tutor.php

REM tournaments.php
echo ^<?php > test\tournaments.php
echo echo "Welcome to the Tournaments Page - Compete and Win!"; >> test\tournaments.php
echo ^?> >> test\tournaments.php

REM petscii.php
echo ^<?php > test\petscii.php
echo echo "PETSCII Art Gallery - Showcase Your Creations"; >> test\petscii.php
echo ^?> >> test\petscii.php

REM forum.php
echo ^<?php > test\forum.php
echo echo "User Forums - Join the Discussion"; >> test\forum.php
echo ^?> >> test\forum.php

REM includes/session.php
echo ^<?php > test\includes\session.php
echo session_start(); >> test\includes\session.php
echo if (!isset($_SESSION['viewMode'])) ^{ $_SESSION['viewMode'] = 'LIST'; ^} >> test\includes\session.php
echo ^?> >> test\includes\session.php

REM includes/config.php
echo ^<?php > test\includes\config.php
echo define('BASE_URL', 'http://localhost/commodoreblue.com/test'); >> test\includes\config.php
echo ^?> >> test\includes\config.php

REM includes/functions.php
echo ^<?php > test\includes\functions.php
echo function c64_upper($text) ^{ return strtoupper($text); ^} >> test\includes\functions.php
echo function centerText($text, $width) ^{ >> test\includes\functions.php
echo     $len = strlen($text); >> test\includes\functions.php
echo     return $len >= $width ? $text : str_repeat(" ", floor(($width - $len) / 2)) . $text; >> test\includes\functions.php
echo ^} >> test\includes\functions.php
echo ^?> >> test\includes\functions.php

REM includes/chatgpt.php
echo ^<?php > test\includes\chatgpt.php
echo function generateBasicCode($prompt) ^{ >> test\includes\chatgpt.php
echo     return "10 PRINT 'Hello World'\n20 GOTO 10"; >> test\includes\chatgpt.php
echo ^} >> test\includes\chatgpt.php
echo ^?> >> test\includes\chatgpt.php

REM assets/css/style.css
echo body { background: #000; color: #0f0; font-family: 'Courier New', monospace; } > test\assets\css\style.css
echo h1 { text-align: center; margin-top: 20px; } >> test\assets\css\style.css

REM assets/css/tutor.css
echo #hint-panel { background: #222; color: #0f0; padding: 10px; border: 1px solid #0f0; } > test\assets\css\tutor.css

REM assets/js/main.js
echo console.log("Main JS Loaded"); > test\assets\js\main.js

REM assets/js/tutor.js
echo console.log("Tutor JS Loaded - Hints System Coming Soon!"); > test\assets\js\tutor.js

REM vice/monitor-client.py
echo # Python script to send BASIC code to VICE emulator > test\vice\monitor-client.py
echo print("VICE Monitor Client Placeholder") >> test\vice\monitor-client.py

REM vendor/products.json
echo [ > test\vendor\products.json
echo     ^{"name": "Custom Commodore 64", "price": 300, "stock": 5^} >> test\vendor\products.json
echo ] >> test\vendor\products.json

REM patreon/patreon.php
echo ^<?php > test\patreon\patreon.php
echo echo "Patreon Integration Placeholder"; >> test\patreon\patreon.php
echo ^?> >> test\patreon\patreon.php

REM README.md
echo # Commodoreblue.com Test Environment > test\README.md
echo This is the test setup for the Commodoreblue.com project. >> test\README.md

REM .gitignore
echo *.log > test\.gitignore
echo *.tmp >> test\.gitignore
echo vendor/ >> test\.gitignore
echo videos/ >> test\.gitignore

echo Setup complete! Your project structure is ready.
