<?php

$uri = $_SERVER['REQUEST_URI'];
list(, $index, $other) = explode('/', $uri, 3);
list($index, $secret) = explode(':', $index, 2);

if ($secret != crc32($index . getenv('SECRET'))) {
    echo 'wrong url';
    exit;
}

$url = 'http://' . getenv('ESHOST') . "/{$index}/{$other}";
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
if (array_key_exists('CONTENT_LENGTH', $_SERVER)) {
    $content = file_get_contents('php://input');
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
}
curl_exec($curl);
curl_close($curl);
