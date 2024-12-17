<?php

curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

// Replace these with your ScreenScraper credentials
$username = 'rs@soussan.com';
$password = 'Xmw5dRvs36z@!@W';
// Set the API endpoint
$apiUrl = 'https://www.screenscraper.fr/api2/hello.php?output=json';

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Provide credentials for Basic Auth
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

// Execute the request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
    curl_close($ch);
    exit;
}

// Get HTTP status code
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Display HTTP status and raw response for a basic check
echo "HTTP Status Code: $httpCode\n";
echo "Response:\n$response\n";
