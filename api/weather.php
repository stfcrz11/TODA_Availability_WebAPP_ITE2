<?php
$city = isset($_GET['city']) ? urlencode($_GET['city']) : 'Santiago';
$apiKey = '9a3bd740c75e34eefbc779c69d997096';
$url = "http://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
$response = curl_exec($ch);
curl_close($ch);
echo $response;
?>
