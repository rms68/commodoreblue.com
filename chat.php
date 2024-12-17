<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set your OpenAI API key here
    $apiKey = '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';

    // Get the raw POST input and decode the JSON payload
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate the input
    if (!isset($data['messages']) || !is_array($data['messages'])) {
        http_response_code(400);
        echo json_encode([
            "error" => "Invalid input. Please send a valid JSON payload with 'messages'."
        ]);
        exit;
    }

    // Prepare the data for OpenAI API
    $postData = json_encode([
        "model" => "gpt-4", // Specify the GPT model
        "messages" => $data['messages']
    ]);

    // Initialize cURL to call the OpenAI API
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    // Execute the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check for errors
    if ($httpCode !== 200) {
        http_response_code($httpCode);
        echo json_encode([
            "error" => "Failed to communicate with OpenAI API.",
            "details" => $response
        ]);
        exit;
    }

    // Return the response from OpenAI
    header('Content-Type: application/json');
    echo $response;
} else {
    // Handle invalid request methods
    http_response_code(405);
    echo json_encode([
        "error" => "Invalid request method. Use POST."
    ]);
}
?>
