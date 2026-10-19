<?php
// api/weather.php - Proxy to OpenWeatherMap (keeps the API key server-side)
require_once '../config.php';
header('Content-Type: application/json');

// Default: Santiago City, Isabela (plain "Santiago" resolves to Chile)
if (!empty($_GET['city'])) {
    $query = 'q=' . urlencode($_GET['city']);
} else {
    $query = 'lat=16.6886&lon=121.5487';
}
$url = "https://api.openweathermap.org/data/2.5/weather?$query&appid=" . WEATHER_API_KEY . "&units=metric";

$response = false;
if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
} else {
    $response = @file_get_contents($url);
}

if ($response === false) {
    http_response_code(502);
    echo json_encode(array('error' => 'Weather service unavailable'));
    exit;
}
echo $response;
