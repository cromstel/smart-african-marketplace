<?php
$ch = curl_init('http://localhost:8000/services/beauty-grooming');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$r = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo 'Status: ' . $httpCode . PHP_EOL;
if ($httpCode >= 400) {
    echo 'Body: ' . substr($r, 0, 5000) . PHP_EOL;
}
curl_close($ch);
