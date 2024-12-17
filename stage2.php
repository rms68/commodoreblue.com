// ******************************************
// ************ CONFIG SECTION **************
// ******************************************

session_start();

// Base Configuration
$CONFIG = [
    'PAGES' => ['home', 'gallery', 'about', 'emu', 'shop'],
    'DEFAULT_VIEW_MODE' => 'LIST',
    'EMULATOR_PATH' => 'emu/js/x64.js',
    'ROMS_PATH' => 'emu/ROMS/',
    'CANVAS_WIDTH' => 640,
    'CANVAS_HEIGHT' => 400
];

// Initialize view mode if not set
if (!isset($_SESSION['viewMode'])) {
    $_SESSION['viewMode'] = $CONFIG['DEFAULT_VIEW_MODE'];
}

// ******************************************
// ************ DATA LAYER *****************
// ******************************************

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

function parseCSV($csvData) {
    $lines = explode("\n", $csvData);
    $header = str_getcsv(array_shift($lines));
    $products = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $row = str_getcsv($line);
        $products[] = array_combine($header, $row);
    }
    return $products;
}

$products = parseCSV($csvData);

// ******************************************
// ********** EMULATOR FUNCTIONS ***********
// ******************************************

function launchEmulator($gameFile) {
    global $CONFIG;
    
    $output = "<div id='emulator-container'>";
    $output .= "<canvas id='vice-canvas' width='{$CONFIG['CANVAS_WIDTH']}' height='{$CONFIG['CANVAS_HEIGHT']}'></canvas>";
    $output .= "</div>";
    $output .= "<script>
        var gameName = '" . addslashes($gameFile) . "';
        var canvas = document.getElementById('vice-canvas');
        
        window.Module = {
            canvas: canvas,
            arguments: ['-autostart', gameName, '-sounddev', 'sdl', '-soundbufsize', '500'],
            preRun: [function() {
                FS.createPreloadedFile('/', gameName, '{$CONFIG['ROMS_PATH']}' + gameName, true, false);
            }],
            onRuntimeInitialized: function() {
                canvas.focus();
            }
        };
        
        const script = document.createElement('script');
        script.src = '{$CONFIG['EMULATOR_PATH']}';
        document.body.appendChild(script);
    </script>";
    
    return $output;
}

// ******************************************
// ********** CORE FUNCTIONS **************
// ******************************************

function c64_upper($text) {
    return strtoupper($text);
}

function parseLoadCommand($input) {
    $u = strtoupper($input);
    if (stripos($u,'LOAD')===false) return null;
    $parts = preg_split('/\bLOAD\b/i', $input);
    if (count($parts)<2) return null;
    $rest = trim($parts[1]);
    $rest = preg_replace('/,8\s*$/i', '', $rest);
    $rest = trim($rest,'" ');
    if ($rest === '') return null;
    return strtolower($rest);
}

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

function currentPage() {
    return isset($_SESSION['currentPage']) ? $_SESSION['currentPage'] : 'home';
}

function centerText($text,$width) {
    $len = strlen($text);
    if ($len >= $width) return $text;
    $spaces = floor(($width - $len)/2);
    return str_repeat(" ",$spaces) . $text;
}

// ******************************************
// ********** DISPLAY FUNCTIONS ************
// ******************************************

function generateListItems($pages, $products) {
    $current = currentPage();
    $viewMode = $_SESSION['viewMode'];
    $output = "";

    if ($current === 'emu') {
        $items = array_filter($products, function($p) {
            return strtoupper($p['PLAYABLE'])==='TRUE';
        });
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
        $output .= "PAGES:\n";
        foreach ($pages as $pg) {
            $output .= c64_upper($pg)."\n";
        }
    }
    return $output;
}

function runItem($pages, $products) {
    if (!isset($_SESSION['loadedItem'])) {
        return "NO PROGRAM LOADED. USE LOAD ITEM FIRST.\n";
    }
    
    $item = $_SESSION['loadedItem'];
    $current = currentPage();
    
    if (is_array($item)) {
        $p = $item;
        $nameU = c64_upper($p['product_name']);
        
        if ($current === 'emu') {
            if (strtoupper($p['PLAYABLE'])==='TRUE') {
                $gameFile = $p['product_name'] . '.d64';
                return launchEmulator($gameFile);
            } else {
                return "CANNOT RUN THIS ITEM HERE. NOT A GAME.\n";
            }
        } elseif ($current === 'shop') {
            $out = "DISPLAYING PRODUCT DETAIL FOR '$nameU'...\n";
            $out .= "PRICE: ".$p['PRICE']."\n";
            $out .= "DESCRIPTION: ".c64_upper($p['product_description'])."\n";
            if (!empty($p['MEDIA']) && $p['MEDIA']!=='FALSE') {
                $out .= "IMAGE: ".$p['MEDIA']."\n";
                $out .= "<img src=\"".$p['MEDIA']."\" style=\"max-width:300px;\"><br>\n";
            }
            $welcomeMsg = "WELCOME TO $nameU PRODUCT PAGE!";
            $out .= centerText($welcomeMsg,40)."\n";
            $out .= "TYPE <span class=\"autolist\" data-command=\"LIST\">LIST</span> TO SEE DIRECTORY\n";
            return $out;
        } elseif ($current === 'gallery') {
            if (!empty($p['MEDIA']) && $p['MEDIA']!=='FALSE') {
                $out = "DISPLAYING IMAGE FOR '$nameU'...\n".$p['MEDIA']."\n";
                $welcomeMsg = "IMAGE VIEW: $nameU";
                $out .= centerText($welcomeMsg,40)."\n";
                $out .= "<img src=\"".$p['MEDIA']."\" style=\"max-width:300px;\"><br>\n";
            } else {
                $out = "NO IMAGE AVAILABLE.\n";
            }
            $out .= "TYPE <span class=\"autolist\" data-command=\"LIST\">LIST</span> TO SEE DIRECTORY\n";
            return $out;
        } else {
            return "CANNOT RUN THIS ITEM HERE. TRY LOAD EMU, SHOP, OR GALLERY.\n";
        }
    } else {
        $_SESSION['currentPage'] = $item;
        $out = "RUNNING '".c64_upper($item)."'...\n";
        $welcomeMsg = "WELCOME TO THE ".c64_upper($item)." PAGE!";
        $out .= centerText($welcomeMsg,40)."\n";
        $out .= "TYPE <span class=\"autolist\" data-command=\"LIST\">LIST</span> TO SEE DIRECTORY\n";
        return $out;
    }
}

// ******************************************
// ********** COMMAND HANDLING ************
// ******************************************

$output = "";
$inputCmd = "";

if (isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'toggleView':
            $_SESSION['viewMode'] = ($_SESSION['viewMode']==='LIST')?'THUMBNAIL':'LIST';
            break;
        case 'autorun':
            $_POST['command'] = 'RUN';
            break;
        case 'autolist':
            $_POST['command'] = 'LIST';
            break;
    }
}

if (isset($_POST['command'])) {
    $inputCmd = trim($_POST['command']);
    $inputCmdUpper = strtoupper($inputCmd);

    switch($inputCmdUpper) {
        case 'LIST':
            $output .= generateListItems($CONFIG['PAGES'], $products);
            break;
            
        case 'HELP':
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
            break;
            
        case 'RUN':
            $output .= runItem($CONFIG['PAGES'], $products);
            break;
            
        default:
            $itemName = parseLoadCommand($inputCmd);
            if ($itemName) {
                $found = findItemType($itemName, $CONFIG['PAGES'], $products);
                if ($found) {
                    $_SESSION['loadedItem'] = ($found['type']==='page'?$found['name']:$found['product']);
                    $current = currentPage();
                    if ($found['type']==='product') {
                        handleProductLoad($found['product'], $current, $output);
                    } else {
                        $output .= "LOADED \"".strtoupper($itemName)."\"\nTYPE <span class=\"autorun\" data-command=\"RUN\">RUN</span> TO START.\n";
                    }
                } else {
                    $output .= "FILE NOT FOUND ERROR.\nTRY LIST.\n";
                }
            } else {
                handleUnknownCommand($inputCmd, $CONFIG['PAGES'], $products, $output);
            }
    }
}

// ******************************************
// ************ VIEW LAYER ****************
// ******************************************

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
            font-family: 'Courier New', monospace
                        padding: 20px;
            margin: 0;
        }
        #display {
            white-space: pre-wrap;
            border: 2px solid #0f0;
            padding: 10px;
            height: 300px;
            overflow-y: auto;
            background: #000;
            color: #0f0;
            font-family: 'Courier New', monospace;
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
            right: 0;
            top: 0;
        }
        #emulator-container {
            margin: 20px auto;
            width: <?php echo $CONFIG['CANVAS_WIDTH']; ?>px;
            height: <?php echo $CONFIG['CANVAS_HEIGHT']; ?>px;
            border: 2px solid #0f0;
        }
    </style>
</head>
<body>
    <h1>COMMODORE BASIC NAVIGATION TEST</h1>
    <div id="display"><?php echo $output; ?></div>

    <form method="post" style="margin-top:10px;" id="cmdform">
        <input type="text" name="command" autofocus>
        <button type="submit">ENTER</button>
    </form>

    <form method="post" style="margin-top:5px;">
        <input type="hidden" name="action" value="toggleView">
        <button type="submit">TOGGLE VIEW MODE (CURRENT: <?php echo $_SESSION['viewMode']; ?>)</button>
    </form>

    <p style="font-size:14px;margin-top:10px;">
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
            handleAutoCommand('autorun', 'RUN', 5);
            handleAutoCommand('autolist', 'LIST', 5);
        });

        function handleAutoCommand(cls, command, maxFlashes) {
            const el = document.querySelector('.' + cls + '[data-command="' + command + '"]');
            if (el) {
                let flashes = 0;
                let visible = true;
                let interval = setInterval(() => {
                    visible = !visible;
                    el.style.visibility = visible ? 'visible' : 'hidden';
                    flashes++;
                    if (flashes >= maxFlashes * 2) {
                        clearInterval(interval);
                        if (command === 'RUN') {
                            document.getElementById('autorunForm').submit();
                        } else if (command === 'LIST') {
                            document.getElementById('autolistForm').submit();
                        }
                    }
                }, 200);
            }
        }

        const cmdform = document.getElementById('cmdform');
        cmdform.addEventListener('submit', function(e) {
            const inp = cmdform.querySelector('input[name="command"]');
            if (inp) {
                inp.value = inp.value.toUpperCase();
            }
        });
    </script>
</body>
</html>

