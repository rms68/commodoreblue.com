<?php
// ============================================================================
// Visual Functions Library
// ============================================================================

function colorBox($width, $height, $color) {
    $width = htmlspecialchars($width, ENT_QUOTES);
    $height = htmlspecialchars($height, ENT_QUOTES);
    $color = htmlspecialchars($color, ENT_QUOTES);
    $style = "width:{$width}px; height:{$height}px; background-color:{$color}; border:1px solid #000;";
    return "<div style='{$style}'></div>";
}

// Spinning Letters with totalTime (sec) and spinTime (ms)
function spinningLettersTotalControl($from, $to, $totalTime = 4, $spinTime = 200) {
    $from = htmlspecialchars($from, ENT_QUOTES);
    $to   = htmlspecialchars($to, ENT_QUOTES);

    $len = max(strlen($from), strlen($to));
    $from = str_pad($from, $len, ' ');
    $to   = str_pad($to, $len, ' ');

    $totalTimeMs = $totalTime * 1000.0;
    if ($len < 1) $len = 1; // Avoid division by zero if empty strings

    // Time per character with no delay:
    //   perCharBase = 400ms + 2*spinTime
    // Minimum total time with no delays:
    $perCharBase = 400 + 2*$spinTime;
    $minTotalTimeMs = $len * $perCharBase;

    // If requested total time is less than minimum possible:
    if ($totalTimeMs < $minTotalTimeMs) {
        // We cannot shorten the spinTime, so no charDelay and totalTime effectively ignored
        $charDelay = 0;
    } elseif ($len == 1) {
        // Only one character: total time ~ perCharBase
        // Can't stretch total time by adding delay since there's no next char
        $charDelay = 0;
    } else {
        // We have N chars, and we can add delay between them to reach totalTime
        // (N-1)*charDelay = totalTimeMs - N*perCharBase
        $charDelay = ($totalTimeMs - $len*$perCharBase) / ($len - 1);
        if ($charDelay < 0) $charDelay = 0; // Safety check, though handled above
    }

    $blockChar = '█';

    ob_start();
    ?>
    <div class="spinning-letters-container" style="display:flex;">
        <?php for ($i=0; $i<$len; $i++): 
            $f = $from[$i];
            $t = $to[$i];
        ?>
        <div class="char-card" data-oldchar="<?php echo $f; ?>" data-newchar="<?php echo $t; ?>" style="width:40px;height:40px;perspective:1000px;margin:0 5px;position:relative;">
            <div class="flip-card-inner" style="width:100%;height:100%;transform-style:preserve-3d;transform-origin:center;">
                <div class="flip-card-front" style="font-family:monospace;font-size:40px;line-height:40px;display:flex;align-items:center;justify-content:center;background:#f0f0f0;border:1px solid #ccc;backface-visibility:hidden;position:absolute;width:100%;height:100%"><?php echo $f; ?></div>
                <div class="flip-card-back" style="font-family:monospace;font-size:40px;line-height:40px;display:flex;align-items:center;justify-content:center;background:#f0f0f0;border:1px solid #ccc;backface-visibility:hidden;position:absolute;width:100%;height:100%;transform:rotateY(180deg);"><?php echo $blockChar; ?></div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
    <script>
    (function(){
        var cards = document.querySelectorAll('.char-card');
        var index = 0;
        var spinTime = <?php echo (int)$spinTime; ?>; // ms per half-flip
        var charDelay = <?php echo (int)$charDelay; ?>; // ms between chars
        // Each char: sequence = 200ms wait + half-flip(spinTime) + 200ms wait + half-flip(spinTime)
        // After done with one char, wait charDelay then next char (except last char)

        function flipChar(card, callback) {
            var inner = card.querySelector('.flip-card-inner');
            var backFace = card.querySelector('.flip-card-back');
            var frontFace = card.querySelector('.flip-card-front');
            var oldChar = card.getAttribute('data-oldchar');
            var newChar = card.getAttribute('data-newchar');

            inner.style.transition = '';
            inner.style.transform = 'rotateY(0deg)';
            frontFace.textContent = oldChar;
            backFace.textContent = '█';

            setTimeout(function(){
                inner.style.transition = 'transform ' + (spinTime/1000) + 's ease';
                inner.style.transform = 'rotateY(180deg)';
                setTimeout(function(){
                    backFace.textContent = newChar;
                    setTimeout(function(){
                        inner.style.transform = 'rotateY(360deg)';
                        setTimeout(function(){
                            callback();
                        }, spinTime);
                    },200);
                }, spinTime);
            }, 200);
        }

        function flipNextChar() {
            if (index >= cards.length) return;
            flipChar(cards[index], function(){
                index++;
                if (index < cards.length) {
                    setTimeout(flipNextChar, charDelay);
                }
            });
        }
        flipNextChar();
    })();
    </script>
    <?php
    return ob_get_clean();
}

// Typewriter Effect with total time
function typewriterTotalTime($text, $totalTime = 4) {
    $text = htmlspecialchars($text, ENT_QUOTES);
    $len = strlen($text);
    if ($len < 1) $len = 1; 
    $totalTimeMs = $totalTime * 1000.0;
    $charInterval = $totalTimeMs / $len;
    if ($charInterval < 1) {
        $charInterval = 1; // minimum
    }

    ob_start();
    ?>
    <div id="typewriterContainer" style="font-family:monospace;font-size:20px;white-space:pre;"></div>
    <script>
    (function(){
        var text = "<?php echo addslashes($text); ?>";
        var container = document.getElementById('typewriterContainer');
        var index = 0;
        var interval = <?php echo (int)$charInterval; ?>;

        function typeChar() {
            if (index < text.length) {
                container.textContent += text.charAt(index);
                index++;
                setTimeout(typeChar, interval);
            }
        }
        typeChar();
    })();
    </script>
    <?php
    return ob_get_clean();
}

// Flashing Text (unchanged)
function flashingText($text, $frequency = 1) {
    $text = htmlspecialchars($text, ENT_QUOTES);
    $duration = 1/$frequency;
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
        animation: <?php echo $randClass; ?> <?php echo $duration; ?>s infinite;
    }
    </style>
    <div class="<?php echo $randClass; ?>" style="font-family:monospace;font-size:20px;">
        <?php echo $text; ?>
    </div>
    <?php
    return ob_get_clean();
}

// ============================================================================
// Define functions in library
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
        'name' => 'spinningLettersTotalControl',
        'description' => 'Spins letters from one string to another. Control total duration (sec) and half-flip spin time (ms). Delays are calculated based on character count.',
        'params' => [
            [ 'name' => 'from',      'type' => 'text', 'label' => 'From String' ],
            [ 'name' => 'to',        'type' => 'text', 'label' => 'To String' ],
            [ 'name' => 'totalTime', 'type' => 'text', 'label' => 'Total Time (seconds)' ],
            [ 'name' => 'spinTime',  'type' => 'text', 'label' => 'Spin Time per Half-Flip (ms)' ]
        ],
        'callback' => function($params) {
            $tt = is_numeric($params['totalTime']) ? floatval($params['totalTime']) : 4.0;
            $st = is_numeric($params['spinTime']) ? floatval($params['spinTime']) : 200;
            return spinningLettersTotalControl($params['from'], $params['to'], $tt, $st);
        }
    ],
    [
        'name' => 'typewriterTotalTime',
        'description' => 'Types out the given text within the specified total time (seconds). Interval is calculated based on text length.',
        'params' => [
            [ 'name' => 'text',      'type' => 'text', 'label' => 'Text' ],
            [ 'name' => 'totalTime', 'type' => 'text', 'label' => 'Total Time (seconds)' ]
        ],
        'callback' => function($params) {
            $tt = is_numeric($params['totalTime']) ? floatval($params['totalTime']) : 4.0;
            return typewriterTotalTime($params['text'], $tt);
        }
    ],
    [
        'name' => 'flashingText',
        'description' => 'Makes the given text flash at the specified frequency (flashes/second).',
        'params' => [
            [ 'name' => 'text',      'type' => 'text', 'label' => 'Text' ],
            [ 'name' => 'frequency', 'type' => 'text', 'label' => 'Frequency (flashes/second)' ]
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
                            "// Ensure visuals.php is included.\n\n".
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
    width: 250px;
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
<p>Select a function, input parameters, preview, and generate code. The total time calculations now consider the number of characters and reverse-calculate delays or intervals accordingly.</p>

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
