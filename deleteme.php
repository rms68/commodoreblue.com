<?php
session_start();

**********************************************************************************
=========================   BEGIN PAGES AVAILABLE   ===========================
***********************************************************************************


= = = = = = = = = = = = BEGIN PAGES ARRAY = = = = = = = = = = = = 

// Pages available
$pages = ['home', 'gallery', 'about', 'emu', 'shop'];

= = = = = = = = = = = = END PAGES ARRAY   = = = = = = = = = = = = 


**********************************************************************************
=========================   END PAGES AVAILABLE   =============================
***********************************************************************************


**********************************************************************************
========================   BEGIN DEFAULT VIEW MODE   ==========================
***********************************************************************************


= = = = = = = = = = = = BEGIN VIEW MODE SETUP = = = = = = = = = = = = 

// Default view mode
if (!isset($_SESSION['viewMode'])) {
    $_SESSION['viewMode'] = 'LIST';
}

= = = = = = = = = = = = END VIEW MODE SETUP   = = = = = = = = = = = = 


**********************************************************************************
========================   END DEFAULT VIEW MODE   ============================
***********************************************************************************


**********************************************************************************
=========================        BEGIN CSV DATA        =========================
***********************************************************************************


= = = = = = = = = = = = BEGIN CSV DATA BLOCK = = = = = = = = = = = = 

// CSV data
$csvData = <<<CSV
type,product_name,PRICE,PLAYABLE,roms,MEDIA,product_description,product_category_1
product,ACEOFACES,25,TRUE,\emu\ROMS\,https://d2j6dbq0eux0bg.cloudfront.net/images/108400041/4678629693.jpg,TEMP DESCRIPTION,GAMES
product,AFTERBURNER,25,TRUE,\emu\ROMS\,https://d2j6dbq0eux0bg.cloudfront.net/images/108400041/4678634776.jpg,TEMP DESCRIPTION,GAMES
product,ARCHON,25,TRUE,\emu\ROMS\,https://d2j6dbq0eux0bg.cloudfront.net/images/108400041/4678633815.jpg,TEMP DESCRIPTION,GAMES
product,ARKANOID,25,TRUE,\emu\ROMS\,https://d2j6dbq0eux0bg.cloudfront.net/images/108400041/4678629735.jpg,TEMP DESCRIPTION,GAMES
product,BATMAN,25,TRUE,\emu\ROMS\,https://d2j6dbq0eux0bg.cloudfront.net/images/108400041/4678634866.jpg,TEMP DESCRIPTION,GAMES
product,BATMAN CAPED CRUSADER,25,TRUE,\emu\ROMS\,https://d2j6dbq0eux0bg.cloudfront.net/images/108400041/4678633905.jpg,TEMP DESCRIPTION,GAMES
product,BLUE commodore64,2000,FALSE,FALSE,https://d2j6dbq0eux0bg.cloudfront.net/images/108400041/4675993605.png,TEMP DESCRIPTION,BUNDLES
product,BOARD,2000,FALSE,FALSE,https://d2j6dbq0eux0bg.cloudfront.net/images/108400041/4675991736.jpg,TEMP DESCRIPTION,UPGRADES
product,ORANGE commodore64,2000,FALSE,FALSE,https://d2j6dbq0eux0bg.cloudfront.net/images/108400041/4675994557.png,TEMP DESCRIPTION,BUNDLES
product,RED commodore64,2000,FALSE,FALSE,https://d2j6dbq0eux0bg.cloudfront.net/images/108400041/4675993617.png,TEMP DESCRIPTION,BUNDLES
product,SD2IEC,2000,FALSE,FALSE,https://d2j6dbq0eux0bg.cloudfront.net/images/108400041/4675997271.png,TEMP DESCRIPTION,UPGRADES
CSV;

$products = parseCSV($csvData);

= = = = = = = = = = = = END CSV DATA BLOCK   = = = = = = = = = = = = 


**********************************************************************************
=========================        END CSV DATA          =========================
***********************************************************************************


**********************************************************************************
=========================         BEGIN FUNCTIONS       =========================
***********************************************************************************


= = = = = = = = = = = = BEGIN parseCSV FUNCTION = = = = = = = = = = = = 

/**
 * parseCSV
 *
 * Parses a CSV formatted string and returns an array of products.
 *
 * @param string $csvData The CSV data as a string.
 * @return array An array of products, each represented as an associative array.
 */
function parseCSV($csvData) {
    $lines = explode("\n", $csvData);
    $header = str_getcsv(array_shift($lines));
    $products = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $row = str_getcsv($line);
        $item = array_combine($header, $row);
        $products[] = $item;
    }
    return $products;
}

= = = = = = = = = = = = END parseCSV FUNCTION   = = = = = = = = = = = = 


= = = = = = = = = = = = BEGIN c64_upper FUNCTION = = = = = = = = = = = = 

/**
 * c64_upper
 *
 * Converts a given text string to uppercase.
 *
 * @param string $text The text to convert.
 * @return string The uppercase version of the text.
 */
function c64_upper($text) {
    return strtoupper($text);
}

= = = = = = = = = = = = END c64_upper FUNCTION   = = = = = = = = = = = = 


= = = = = = = = = = = = BEGIN parseLoadCommand FUNCTION = = = = = = = = = = = = 

/**
 * parseLoadCommand
 *
 * Parses a LOAD command input to extract the item name.
 *
 * @param string $input The user input command.
 * @return string|null The extracted item name in lowercase, or null if not a valid LOAD command.
 */
function parseLoadCommand($input) {
    $u = strtoupper($input);
    // Accept various syntaxes: LOAD, LOAD"ITEM", LOAD ITEM,8 etc.
    if (stripos($u,'LOAD')===false) return null;
    // Extract after LOAD
    $parts = preg_split('/\bLOAD\b/i', $input);
    if (count($parts)<2) return null;
    $rest = trim($parts[1]);
    // Remove ,8 if present
    $rest = preg_replace('/,8\s*$/i', '', $rest);
    $rest = trim($rest,'" ');
    if ($rest === '') return null;
    return strtolower($rest);
}

= = = = = = = = = = = = END parseLoadCommand FUNCTION   = = = = = = = = = = = = 


= = = = = = = = = = = = BEGIN findItemType FUNCTION = = = = = = = = = = = = 

/**
 * findItemType
 *
 * Determines the type of an item (page or product) based on its name.
 *
 * @param string $item The name of the item to find.
 * @param array $pages The array of available pages.
 * @param array $products The array of available products.
 * @return array|null An associative array with the type and name/product, or null if not found.
 */
function findItemType($item, $pages, $products) {
    if (in_array($item, $pages)) {
        return ['type'=>'page','name'=>$item];
    }
    foreach ($products as $p) {
        if (strtolower($p['product_name']) === $item) {
            return ['type'=>'product','product'=>$p];
        }
    }
    return null;
}

= = = = = = = = = = = = END findItemType FUNCTION   = = = = = = = = = = = = 


= = = = = = = = = = = = BEGIN currentPage FUNCTION = = = = = = = = = = = = 

/**
 * currentPage
 *
 * Retrieves the current page from the session. Defaults to 'home' if not set.
 *
 * @return string The current page.
 */
function currentPage() {
    return isset($_SESSION['currentPage']) ? $_SESSION['currentPage'] : 'home';
}

= = = = = = = = = = = = END currentPage FUNCTION   = = = = = = = = = = = = 


= = = = = = = = = = = = BEGIN centerText FUNCTION = = = = = = = = = = = = 

/**
 * centerText
 *
 * Centers a given text within a specified width by padding with spaces.
 *
 * @param string $text The text to center.
 * @param int $width The total width to center the text within.
 * @return string The centered text.
 */
function centerText($text,$width) {
    $len = strlen($text);
    if ($len >= $width) return $text;
    $spaces = floor(($width - $len)/2);
    return str_repeat(" ",$spaces) . $text;
}

= = = = = = = = = = = = END centerText FUNCTION   = = = = = = = = = = = = 


= = = = = = = = = = = = BEGIN generateListItems FUNCTION = = = = = = = = = = = = 

/**
 * generateListItems
 *
 * Generates a list of items based on the current page and view mode.
 *
 * @param array $pages The array of available pages.
 * @param array $products The array of available products.
 * @return string The generated list as a string.
 */
function generateListItems($pages, $products) {
    $current = currentPage();
    $viewMode = $_SESSION['viewMode'];
    $output = "";

    if ($current === 'emu') {
        $items = [];
        foreach ($products as $p) {
            if (strtoupper($p['PLAYABLE'])==='TRUE') {
                $items[] = $p;
            }
        }
        $output .= "GAMES:\n";
        foreach ($items as $p) {
            $nameU = c64_upper($p['product_name']);
            if ($viewMode==='THUMBNAIL' && !empty($p['MEDIA']) && $p['MEDIA']!=='FALSE') {
                $output .= "$nameU\n[IMAGE BELOW]\n<img src=\"".$p['MEDIA']."\" style=\"max-width:100px;\"><br>\n";
            } else {
                $output .= "$nameU\n";
            }
        }
    } elseif ($current === 'shop') {
        $output .= "PRODUCTS:\n";
        foreach ($products as $p) {
            $nameU = c64_upper($p['product_name']);
            if ($viewMode==='THUMBNAIL' && !empty($p['MEDIA']) && $p['MEDIA']!=='FALSE') {
                $output .= "$nameU (PRICE: ".$p['PRICE'].")\n[IMAGE BELOW]\n<img src=\"".$p['MEDIA']."\" style=\"max-width:100px;\"><br>\n";
            } else {
                $output .= "$nameU (PRICE: ".$p['PRICE'].")\n";
            }
        }
    } elseif ($current === 'gallery') {
        $output .= "GALLERY IMAGES:\n";
        foreach ($products as $p) {
            if (!empty($p['MEDIA']) && $p['MEDIA']!=='FALSE') {
                $nameU = c64_upper($p['product_name']);
                if ($viewMode==='THUMBNAIL') {
                    $output .= "$nameU\n[IMAGE BELOW]\n<img src=\"".$p['MEDIA']."\" style=\"max-width:100px;\"><br>\n";
                } else {
                    $output .= "$nameU\n";
                }
            }
        }
    } else {
        // HOME or ABOUT etc
        $output .= "PAGES:\n";
        foreach ($pages as $pg) {
            $output .= c64_upper($pg)."\n";
        }
    }
    return $output;
}

= = = = = = = = = = = = END generateListItems FUNCTION   = = = = = = = = = = = = 


= = = = = = = = = = = = BEGIN runItem FUNCTION = = = = = = = = = = = = 

/**
 * runItem
 *
 * Executes the RUN command based on the loaded item and current page.
 *
 * @param array $pages The array of available pages.
 * @param array $products The array of available products.
 * @return string The output message after running the item.
 */
function runItem($pages, $products) {
    if (!isset($_SESSION['loadedItem'])) {
        return "NO PROGRAM LOADED. USE LOAD ITEM FIRST.\n";
    }
    $item = $_SESSION['loadedItem'];
    $current = currentPage();
    if (is_array($item)) {
        // Product
        $p = $item;
        $nameU = c64_upper($p['product_name']);
        if ($current === 'emu') {
            // RUN as game if playable
            if (strtoupper($p['PLAYABLE'])==='TRUE') {
                $out = "RUNNING '$nameU' GAME...\nLOADING GAME...\nREADY.\n";
                $welcomeMsg = "WELCOME TO '$nameU' GAME!";
                $out .= centerText($welcomeMsg,40)."\n";
                // After run, show autolist to show directory?
                // On EMU after run, user might want to LIST games again or nothing?
                // Let's show: TYPE LIST TO SEE DIRECTORY with autolist flash
                $out .= "TYPE <span class=\"autolist\" data-command=\"LIST\">LIST</span> TO SEE DIRECTORY\n";
                return $out;
            } else {
                return "CANNOT RUN THIS ITEM HERE. NOT A GAME.\n";
            }
        } elseif ($current === 'shop') {
            // Product detail
            $out = "DISPLAYING PRODUCT DETAIL FOR '$nameU'...\n";
            $out .= "PRICE: ".$p['PRICE']."\n";
            $out .= "DESCRIPTION: ".c64_upper($p['product_description'])."\n";
            if (!empty($p['MEDIA']) && $p['MEDIA']!=='FALSE') {
                $out .= "IMAGE: ".$p['MEDIA']."\n";
                $out .= "<img src=\"".$p['MEDIA']."\" style=\"max-width:300px;\"><br>\n";
            }
            $welcomeMsg = "WELCOME TO $nameU PRODUCT PAGE!";
            $out .= centerText($welcomeMsg,40)."\n";
            // After run in shop, autolist to show directory (products)
            $out .= "TYPE <span class=\"autolist\" data-command=\"LIST\">LIST</span> TO SEE DIRECTORY\n";
            return $out;
        } elseif ($current === 'gallery') {
            // Already show image on load, run is not needed, but if user does run anyway:
            // Just show image again
            if (!empty($p['MEDIA']) && $p['MEDIA']!=='FALSE') {
                $out = "DISPLAYING IMAGE FOR '$nameU'...\n".$p['MEDIA']."\n";
                $welcomeMsg = "IMAGE VIEW: $nameU";
                $out .= centerText($welcomeMsg,40)."\n";
                $out .= "<img src=\"".$p['MEDIA']."\" style=\"max-width:300px;\"><br>\n";
            } else {
                $out = "NO IMAGE AVAILABLE.\n";
            }
            // Gallery doesn't require LIST after run?
            // Let's be consistent: after run on gallery (though not needed?), show autolist to see directory
            $out .= "TYPE <span class=\"autolist\" data-command=\"LIST\">LIST</span> TO SEE DIRECTORY\n";
            return $out;
        } else {
            // HOME or ABOUT
            // It's a product loaded in home/about?
            // run doesn't really make sense here, but let's just show same message:
            $out = "CANNOT RUN THIS ITEM HERE. TRY LOAD EMU, SHOP, OR GALLERY.\n";
            return $out;
        }
    } else {
        // It's a page
        $_SESSION['currentPage'] = $item;
        $out = "RUNNING '".c64_upper($item)."'...\n";
        $welcomeMsg = "WELCOME TO THE ".c64_upper($item)." PAGE!";
        $out .= centerText($welcomeMsg,40)."\n";

        // After running a page, we show TYPE LIST...
        $out .= "TYPE <span class=\"autolist\" data-command=\"LIST\">LIST</span> TO SEE DIRECTORY\n";
        return $out;
    }
}

= = = = = = = = = = = = END runItem FUNCTION   = = = = = = = = = = = = 


**********************************************************************************
=========================   END FUNCTIONS   ==============================
***********************************************************************************


**********************************************************************************
=======================   BEGIN POST ACTION HANDLING  ========================
***********************************************************************************


= = = = = = = = = = = = BEGIN VIEW MODE TOGGLE = = = = = = = = = = = = 

if (isset($_POST['action']) && $_POST['action']==='toggleView') {
    $_SESSION['viewMode'] = ($_SESSION['viewMode']==='LIST')?'THUMBNAIL':'LIST';
}

= = = = = = = = = = = = END VIEW MODE TOGGLE   = = = = = = = = = = = = 


= = = = = = = = = = = = BEGIN AUTORUN ACTION = = = = = = = = = = = = 

if (isset($_POST['action']) && $_POST['action']==='autorun') {
    $_POST['command'] = 'RUN';
}

= = = = = = = = = = = = END AUTORUN ACTION   = = = = = = = = = = = = 


= = = = = = = = = = = = BEGIN AUTOLIST ACTION = = = = = = = = = = = = 

if (isset($_POST['action']) && $_POST['action']==='autolist') {
    $_POST['command'] = 'LIST';
}

= = = = = = = = = = = = END AUTOLIST ACTION   = = = = = = = = = = = = 


**********************************************************************************
=======================   END POST ACTION HANDLING   ========================
***********************************************************************************


**********************************************************************************
=======================     BEGIN COMMAND PROCESSING    ======================
***********************************************************************************


= = = = = = = = = = = = BEGIN LIST COMMAND = = = = = = = = = = = = 

if (isset($_POST['command'])) {
    $inputCmd = $_POST['command'];
    $inputCmd = trim($inputCmd);
    $inputCmdUpper = strtoupper($inputCmd);

    if ($inputCmdUpper === 'LIST') {
        $output .= generateListItems($pages, $products);
    } elseif ($inputCmdUpper === 'HELP') {
        $output .= "COMMANDS:\n";
        $output .= "  LIST\n";
        $output .= "  LOAD ITEM (QUOTES OPTIONAL, ,8 OPTIONAL)\n";
        $output .= "  RUN\n";
        $output .= "  HELP\n";
        $output .= "EXAMPLES:\n";
        $output .= "  LOAD HOME THEN RUN\n";
        $output .= "  LOAD EMU THEN RUN (TO ENTER EMU MODE)\n";
        $output .= "  LOAD BATMAN IN EMU THEN RUN TO PLAY GAME\n";
        $output .= "  LOAD BATMAN IN SHOP THEN RUN TO SEE PRODUCT DETAILS\n";
        $output .= "  LOAD BATMAN IN GALLERY JUST SHOWS IMAGE (NO RUN NEEDED)\n";
    } elseif ($inputCmdUpper === 'RUN') {
        $output .= runItem($pages, $products);
    } else {
        // Check if LOAD command
        $itemName = parseLoadCommand($inputCmd);
        if ($itemName) {
            $found = findItemType($itemName, $pages, $products);
            if ($found) {
                $_SESSION['loadedItem'] = ($found['type']==='page'?$found['name']:$found['product']);
                $current = currentPage();
                if ($found['type']==='product') {
                    $p = $found['product'];
                    $isPlayable = (strtoupper($p['PLAYABLE'])==='TRUE');
                    if ($current==='gallery') {
                        // Show image immediately
                        if (!empty($p['MEDIA']) && $p['MEDIA']!=='FALSE') {
                            $nameU = c64_upper($p['product_name']);
                            $output .= "DISPLAYING IMAGE FOR '$nameU'...\n".$p['MEDIA']."\n";
                            $welcomeMsg = "IMAGE VIEW: $nameU";
                            $output .= centerText($welcomeMsg,40)."\n";
                            $output .= "<img src=\"".$p['MEDIA']."\" style=\"max-width:300px;\"><br>\n";
                            // On gallery no run needed, but after load image, maybe show LIST?
                            $output .= "TYPE <span class=\"autolist\" data-command=\"LIST\">LIST</span> TO SEE DIRECTORY\n";
                        } else {
                            $output .= "NO IMAGE AVAILABLE.\n";
                        }
                    } else {
                        // Elsewhere, we need RUN
                        if ($current==='emu' && $isPlayable) {
                            $output .= "LOADED \"".strtoupper($itemName)."\"\nTYPE <span class=\"autorun\" data-command=\"RUN\">RUN</span> TO START.\n";
                        } elseif ($current==='shop' || $current==='home' || $current==='about') {
                            $output .= "LOADED \"".strtoupper($itemName)."\"\nTYPE <span class=\"autorun\" data-command=\"RUN\">RUN</span> TO START.\n";
                        } else {
                            $output .= "LOADED \"".strtoupper($itemName)."\"\nTHIS ITEM MAY NOT RUN HERE, TRY RUN ANYWAY.\n<span class=\"autorun\" data-command=\"RUN\">RUN</span>\n";
                        }
                    }
                } else {
                    // It's a page
                    // Page requires RUN to start
                    $output .= "LOADED \"".strtoupper($itemName)."\"\nTYPE <span class=\"autorun\" data-command=\"RUN\">RUN</span> TO START.\n";
                }
            } else {
                $output .= "FILE NOT FOUND ERROR.\nTRY LIST.\n";
            }
        } else {
            // Not a load command
            // Check if just a word like 'batman'
            $tryItem = strtolower($inputCmd);
            $found = findItemType($tryItem, $pages, $products);
            if ($found) {
                if ($found['type']==='page') {
                    $out = "I RECOGNIZE '".strtoupper($tryItem)."' AS A PAGE.\n";
                    $out .= "TRY: LOAD \"".strtoupper($tryItem)."\" THEN RUN\n";
                    $output .= $out;
                } else {
                    // Product
                    $p = $found['product'];
                    $nameU = c64_upper($p['product_name']);
                    $out = "I RECOGNIZE '$nameU' AS AN ITEM.\n";
                    $isPlayable = (strtoupper($p['PLAYABLE'])==='TRUE');
                    if ($isPlayable) {
                        $out .= "YOU CAN PLAY THIS GAME IN EMU MODE:\n  LOAD \"EMU\" THEN RUN\n  THEN LOAD \"$nameU\" AND RUN TO PLAY.\n";
                    }
                    if (!empty($p['MEDIA']) && $p['MEDIA']!=='FALSE') {
                        $out .= "YOU CAN VIEW ITS IMAGE IN GALLERY MODE:\n  LOAD \"GALLERY\" THEN RUN\n  THEN LOAD \"$nameU\" (IMAGE WILL SHOW IMMEDIATELY).\n";
                    }
                    $out .= "YOU CAN VIEW/BUY IN SHOP MODE:\n  LOAD \"SHOP\" THEN RUN\n  THEN LOAD \"$nameU\" AND RUN TO SEE PRODUCT DETAIL.\n";
                    $output .= $out;
                }
            } else {
                $output .= "SYNTAX ERROR\n";
            }
        }
    = = = = = = = = = = = = END LIST COMMAND   = = = = = = = = = = = = 


**********************************************************************************
========================      END COMMAND PROCESSING     ======================
***********************************************************************************
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>COMMODORE BASIC NAVIGATION TEST</title>
<style>
body {
    background: #000;
    color: #0f0;
    font-family: 'Courier New', monospace;
    padding: 20px;
}
#display {
    white-space: pre-wrap;
    border: 2px solid #0f0;
    padding: 10px;
    height: 300px;
    overflow-y: auto;
    background: #000;
    color:#0f0;
    font-family:'Courier New', monospace;
}
input[type="text"] {
    width: 80%;
    background: #111;
    color: #0f0;
    border: 1px solid #0f0;
    font-family: 'Courier New', monospace;
    padding: 5px;
}
button {
    background: #222;
    color: #0f0;
    border: 1px solid #0f0;
    padding: 5px 10px;
    cursor: pointer;
}
.autorun, .autolist {
    font-weight: bold;
    cursor: pointer;
    text-decoration: underline;
    position: relative;
    padding-right: 3ch;
}
.autorun::after, .autolist::after {
    content: "";
    position: absolute;
    right:0;
    top:0;
}

/* We'll add JS to handle flashing and a spinner */
</style>
</head>
<body>
<h1 style="color:#0f0;font-family:'Courier New', monospace;">COMMODORE BASIC NAVIGATION TEST</h1>
<div id="display"><?php echo $output; ?></div>

<form method="post" style="margin-top:10px;" id="cmdform">
    <input type="text" name="command" autofocus>
    <button type="submit">ENTER</button>
</form>

<form method="post" style="margin-top:5px;">
    <input type="hidden" name="action" value="toggleView">
    <button type="submit">TOGGLE VIEW MODE (CURRENT: <?php echo $_SESSION['viewMode']; ?>)</button>
</form>

<p style="color:#0f0;font-family:'Courier New', monospace;font-size:14px;margin-top:10px;">
HINTS: TRY 'LIST', 'LOAD HOME', 'LOAD EMU', 'LOAD SHOP', 'HELP', OR TYPE A PRODUCT NAME LIKE 'BATMAN'.<br>
ONCE A PAGE IS LOADED AND RUN, YOU'LL BE PROMPTED TO LIST TO SEE DIRECTORY.<br>
ON GALLERY, LOADING AN ITEM IMMEDIATELY SHOWS ITS IMAGE, NO RUN NEEDED.<br>
EMU/SHOP REQUIRE RUN AFTER LOADING AN ITEM.<br>
YOU CAN TOGGLE VIEW MODE TO SEE THUMBNAILS IF AVAILABLE.<br>
</p>

<form method="post" id="autorunForm" style="display:none;">
    <input type="hidden" name="action" value="autorun">
</form>

<form method="post" id="autolistForm" style="display:none;">
    <input type="hidden" name="action" value="autolist">
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Both autorun and autolist share similar logic
    handleAutoCommand('autorun','RUN',5); // 5 flashes for run
    handleAutoCommand('autolist','LIST',5); // 5 flashes for list
});

function handleAutoCommand(cls, command, maxFlashes) {
    const el = document.querySelector('.'+cls+'[data-command="'+command+'"]');
    if (el) {
        let flashes = 0;
        let visible = true;
        const spinnerFrames = ['|','/','-','\\'];
        let frameIndex = 0;
        let interval = setInterval(() => {
            visible = !visible;
            el.style.visibility = visible ? 'visible' : 'hidden';

            // Update spinner
            // frameIndex = (frameIndex+1)%spinnerFrames.length;
            // el.textContent = command + " " + spinnerFrames[frameIndex];

            flashes++;
            if (flashes >= maxFlashes*2) { // Each flash toggles visibility twice
                clearInterval(interval);
                // After done flashing, automatically submit the command
                if (command === 'RUN') {
                    document.getElementById('autorunForm').submit();
                } else if (command === 'LIST') {
                    document.getElementById('autolistForm').submit();
                }
            }
        },200); // 200ms per toggle
    }
}

// Convert user input to uppercase on submit
const cmdform = document.getElementById('cmdform');
cmdform.addEventListener('submit', function(e){
    const inp = cmdform.querySelector('input[name="command"]');
    if (inp) {
        inp.value = inp.value.toUpperCase();
    }
});
</script>
</body>
</html>
