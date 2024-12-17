<?php
// phase1.php with debugging

$apiKey = "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";

$prompt = "You are a programming assistant specializing in Commodore 64 BASIC. Provide a short BASIC code snippet that prints 'HELLO WORLD' continuously.";

// Prepare the request data
$data = [
    "model" => "gpt-3.5-turbo",
    "messages" => [
        ["role" => "system", "content" => "You are a C64 BASIC coding assistant."],
        ["role" => "user", "content" => $prompt]
    ],
    "max_tokens" => 200,
    "temperature" => 0.2
];

// Use cURL to send the request to OpenAI
$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "cURL error: " . curl_error($ch);
    exit;
}

curl_close($ch);

$json = json_decode($response, true);

if (!isset($json['choices'][0]['message']['content'])) {
    echo "No valid response from API.";
    exit;
}

// Extract the code snippet
$code = $json['choices'][0]['message']['content'];

// Remove Markdown code fences if present
$code = str_replace("```", "", $code);

// Save to program.bas
if (file_put_contents("program.bas", $code) === false) {
    echo "Error: Could not write to program.bas";
    exit;
}

// Convert to PRG using petcat
$petcatCmd = "./emu/petcat -w2 -o program.prg -- program.bas";
exec($petcatCmd, $petcatOutput, $petcatReturnVar);

if ($petcatReturnVar !== 0) {
    echo "<pre>Error running petcat. Output:\n";
    echo "Command: $petcatCmd\n";
    echo "Return code: $petcatReturnVar\n";
    echo "Output:\n" . implode("\n", $petcatOutput);
    echo "</pre>";
    exit;
}

// Run in VICE
$viceCmd = "./emu/x64sc -autostart program.prg > /dev/null 2>&1 &";
exec($viceCmd, $viceOutput, $viceReturnVar);

// Even if vice fails to launch, we’ll show the code for debugging
echo "<pre>";
echo "BASIC code generated:\n$code\n\n";
echo "Program started in VICE (or attempted to).\n";
echo "Command used: $viceCmd\n";
echo "Return code: $viceReturnVar\n";
echo "Output:\n" . implode("\n", $viceOutput) . "\n";
echo "</pre>";
?>
