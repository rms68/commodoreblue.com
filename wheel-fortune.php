<?php
// ============================================================================
// Visual Functions Library
// ============================================================================
// Each function entry in $visualFunctions should have:
// 'name' => string
// 'description' => string
// 'params' => array of arrays (with 'name', 'type', 'label')
// 'callback' => function(params) returns HTML

// 1. Color Box (existing example)
function colorBox($width, $height, $color) {
    $width = htmlspecialchars($width, ENT_QUOTES);
    $height = htmlspecialchars($height, ENT_QUOTES);
    $color = htmlspecialchars($color, ENT_QUOTES);
    $style = "width:{$width}px; height:{$height}px; background-color:{$color}; border:1px solid #000;";
    return "<div style='{$style}'></div>";
}

// 2. Spinning Letters effect (simplified):
// Displays a container with from/to strings flipping each letter from old to block to new.
// 'speed' controls flip duration; 'blockChar' is optional or fixed.
function spinningLetters($from, $to, $speed = 10) {
    // speed: 0 to 20 => duration = speed * 0.05
    $from = htmlspecialchars($from, ENT_QUOTES);
    $to = htmlspecialchars($to, ENT_QUOTES);
    $len = max(strlen($from), strlen($to));
    $from = str_pad($from, $len, ' ');
    $to = str_pad($to, $len, ' ');
    
    $blockChar = '█';
    $duration = $speed * 0.05; // seconds per flip half
    ob_start();
    ?>
    <div class="spinning-letters-container" style="display:flex;">
        <?php 
        for ($i=0; $i<$len; $i++):
            $f = $from[$i];
            $t = $to[$i];
        ?>
        <div class="char-card" data-oldchar="<?php echo $f; ?>" data-newchar="<?php echo $t; ?>" style="width:40px;height:40px;perspective:1000px;margin:0 5px;position:relative;">
            <div class="flip-card-inner" style="width:100%;height:100%;transform-style:preserve-3d;transform-origin:center;transition: transform <?php echo $duration; ?>s ease;">
                <div class="flip-card-front" style="font-family:monospace;font-size:40px;line-height:40px;display:flex;align-items:center;justify-content:center;background:#f0f0f0;border:1px solid #ccc;backface-visibility:hidden;position:absolute;width:100%;height:100%;"><?php echo $f; ?></div>
                <div class="flip-card-back" style="font-family:monospace;font-size:40px;line-height:40px;display:flex;align-items:center;justify-content:center;background:#f0f0f0;border:1px solid #ccc;backface-visibility:hidden;position:absolute;width:100%;height:100%;transform:rotateY(180deg);"><?php echo $blockChar; ?></div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
    <script>
    (function(){
        var cards = document.querySelectorAll('.char-card');
        var index = 0;
        function flipNextChar() {
            if(!cards.length) return;
            var card = cards[index];
            var oldChar = card.getAttribute('data-oldchar');
            var newChar = card.getAttribute('data-newchar');
            var inner = card.querySelector('.flip-card-inner');
            var backFace = card.querySelector('.flip-card-back');
            var frontFace = card.querySelector('.flip-card-front');

            // reset
            inner.style.transform = 'rotateY(0deg)';
            frontFace.textContent = oldChar;
            backFace.textContent = '█';

            // first flip
            setTimeout(function(){
                inner.style.transform = 'rotateY(180deg)';
                setTimeout(function(){
                    backFace.textContent = newChar;
                    // second flip
                    setTimeout(function(){
                        inner.style.transform = 'rotateY(360deg)';
                        setTimeout(function(){
                            index++;
                            if(index >= cards.length) index=0;
                            setTimeout(flipNextChar,500); 
                        }, <?php echo (int)($duration*1000); ?>);
                    },200);
                }, <?php echo (int)($duration*1000); ?>);
            }, 200);
        }
        flipNextChar();
    })();
    </script>
    <?php
    return ob_get_clean();
}

// 3. Typewriter Effect:
// 'text' and 'speed' (ms per character)
function typewriterEffect($text, $speed = 100) {
    $text = htmlspecialchars($text, ENT_QUOTES);
    ob_start();
    ?>
    <div id="typewriterContainer" style="font-family:monospace;font-size:20px;white-space:pre;"></div>
    <script>
    (function(){
        var text = "<?php echo addslashes($text); ?>";
        var container = document.getElementById('typewriterContainer');
        var index = 0;
        var speed = <?php echo (int)$speed; ?>;
        function typeChar() {
            if (index < text.length) {
                container.textContent += text.charAt(index);
                index++;
                setTimeout(typeChar, speed);
            }
        }
        typeChar();
    })();
    </script>
    <?php
    return ob_get_clean();
}

// 4. Flashing Effect:
// 'text' and 'frequency' (times per second)
function flashingText($text, $frequency = 1) {
    $text = htmlspecialchars($text, ENT_QUOTES);
    // frequency: times per second => period for one on/off cycle = (1/frequency)*1000 ms
    // We'll do a simple CSS animation that toggles visibility.
    $duration = 1/$frequency; // seconds per on/off cycle (we can do half on, half off)
    $animationTime = $duration; 
    ob_start();
    $randClass = 'flashText'.rand(1000,9999);
    ?>
    <style>
    @keyframes <?php echo $randClass; ?> {
        0% {opacity:1;}
        50% {opacity:0;}
        100% {opacity:1;}
    }
    .<?php echo $randClass; ?> {
        animation: <?php echo $randClass; ?> <?php echo ($animationTime); ?>s infinite;
    }
    </style>
    <div class="<?php echo $randClass; ?>" style="font-family:monospace;font-size:20px;">
        <?php echo $text; ?>
    </div>
    <?php
    return ob_get_clean();
}

// ============================================================================
// Add the functions to the library array
// ============================================================================
$visualFunctions = [
    [
        'name' => 'colorBox',
        'description' => 'Generates a colored box of given width, height, and background color.',
        'params' => [
            [ 'name' => 'width',  'type' => 'text', 'label' => 'Width (px)' ],
            [ 'name' => 'height', 'type' => 'text', 'label' => 'Height (px)' ],
            [ 'name' => 'color',  'type' => 'text', 'label' => 'Color (CSS)' ]
        ],
        'callback' => function($params) {
            return colorBox($params['width'], $params['height'], $params['color']);
        }
    ],
    [
        'name' => 'spinningLetters',
        'description' => 'Spins letters from one string to another using a card-flip effect.',
        'params' => [
            [ 'name' => 'from',   'type' => 'text', 'label' => 'From String' ],
            [ 'name' => 'to',     'type' => 'text', 'label' => 'To String' ],
            [ 'name' => 'speed',  'type' => 'text', 'label' => 'Speed (0-20)' ]
        ],
        'callback' => function($params) {
            $speed = is_numeric($params['speed']) ? $params['speed'] : 10;
            return spinningLetters($params['from'], $params['to'], $speed);
        }
    ],
    [
        'name' => 'typewriterEffect',
        'description' => 'Simulates typing the given text character-by-character at given speed.',
        'params' => [
            [ 'name' => 'text',   'type' => 'text', 'label' => 'Text' ],
            [ 'name' => 'speed',  'type' => 'text', 'label' => 'Speed (ms per char)' ]
        ],
        'callback' => function($params) {
            $speed = is_numeric($params['speed']) ? $params['speed'] : 100;
            return typewriterEffect($params['text'], $speed);
        }
    ],
    [
        'name' => 'flashingText',
        'description' => 'Makes the given text flash on and off at given frequency.',
        'params' => [
            [ 'name' => 'text',       'type' => 'text', 'label' => 'Text' ],
            [ 'name' => 'frequency',  'type' => 'text', 'label' => 'Frequency (flashes per second)' ]
        ],
        'callback' => function($params) {
            $freq = is_numeric($params['frequency']) ? $params['frequency'] : 1;
            return flashingText($params['text'], $freq);
        }
    ],
];

// ============================================================================
// Handle Form Submission
// ============================================================================
$selectedFunctionName = isset($_POST['functionName']) ? $_POST['functionName'] : '';
$selectedFunction = null;
$generatedOutput = '';
$generatedCodeSnippet = '';

foreach ($visualFunctions as $func) {
    if ($func['name'] === $selectedFunctionName) {
        $selectedFunction = $func;
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedFunction) {
    $params = [];
    foreach ($selectedFunction['params'] as $p) {
        $paramName = $p['name'];
        $params[$paramName] = isset($_POST[$paramName]) ? $_POST[$paramName] : '';
    }

    $generatedOutput = call_user_func($selectedFunction['callback'], $params);

    // Generate code snippet
    $argsList = [];
    foreach ($params as $val) {
        $argsList[] = "'" . addslashes($val) . "'";
    }
    $argString = implode(', ', $argsList);
    $generatedCodeSnippet = "<?php\n\n// Example code to call '{$selectedFunction['name']}'\n".
                            "// Ensure visuals.php is included or that function is defined.\n\n".
                            "\$output = {$selectedFunction['name']}({$argString});\n".
                            "echo \$output;\n";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Visuals Library</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
}
label {
    display: inline-block;
    width: 120px;
    vertical-align: top;
    margin-bottom: 5px;
}
input[type="text"], select {
    padding: 5px;
    width: 200px;
    margin-bottom: 10px;
    box-sizing: border-box;
}
button {
    padding: 8px 15px;
    margin-top: 10px;
    display: inline-block;
}
.preview-container {
    margin-top: 20px;
    border-top: 1px solid #ccc;
    padding-top: 20px;
}
.code-container {
    margin-top: 20px;
    background: #f9f9f9;
    padding: 10px;
    border:1px solid #ccc;
    font-family: monospace;
    white-space: pre;
}
</style>
</head>
<body>

<h1>Visuals Library & Testing Interface</h1>
<p>Select a function to test, input parameters, preview the result, and generate code for using it.</p>

<form method="post" action="">
    <div>
        <label for="functionName">Function:</label>
        <select name="functionName" id="functionName" onchange="this.form.submit()">
            <option value="">-- Select a function --</option>
            <?php foreach ($visualFunctions as $func): ?>
                <option value="<?php echo htmlspecialchars($func['name'], ENT_QUOTES); ?>"
                    <?php if ($selectedFunction && $selectedFunction['name'] === $func['name']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($func['name'], ENT_QUOTES); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($selectedFunction): ?>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($selectedFunction['description'], ENT_QUOTES); ?></p>
        <?php foreach ($selectedFunction['params'] as $p): ?>
            <div>
                <label for="<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>"><?php echo htmlspecialchars($p['label'], ENT_QUOTES); ?>:</label>
                <input type="<?php echo htmlspecialchars($p['type'], ENT_QUOTES); ?>" 
                       name="<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>" 
                       id="<?php echo htmlspecialchars($p['name'], ENT_QUOTES); ?>"
                       value="<?php echo isset($_POST[$p['name']]) ? htmlspecialchars($_POST[$p['name']], ENT_QUOTES) : ''; ?>">
            </div>
        <?php endforeach; ?>

        <button type="submit">Preview</button>
    <?php endif; ?>
</form>

<?php if ($generatedOutput): ?>
<div class="preview-container">
    <h2>Preview:</h2>
    <?php echo $generatedOutput; ?>
</div>
<?php endif; ?>

<?php if ($generatedCodeSnippet): ?>
<div class="preview-container">
    <h2>Generated Code Snippet:</h2>
    <div class="code-container">
        <?php echo htmlspecialchars($generatedCodeSnippet, ENT_QUOTES); ?>
    </div>
</div>
<?php endif; ?>

</body>
</html>
