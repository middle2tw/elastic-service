<?php

$uri = $_SERVER['REQUEST_URI'];
list(, $index, $other) = explode('/', $uri, 3);
list($index, $secret) = explode(':', urldecode($index), 2);

if (file_exists("/tmp/cache-{$index}-{$secret}")) {
    // 如果有檔案就免查詢了
} else {
    $content = file_get_contents('https://middle2.com/api/checkelastic?index=' . urlencode($index) . '&secret=' . urlencode($secret) . '&sig=' . crc32($index . $secret . getenv('SECRET')));
    if (!$obj = json_decode($content)) {
        echo 'check api error';
        exit;
    }

    if ($obj->error !== false) {
        echo 'api error';
        exit;
    }

    touch("/tmp/cache-{$index}-{$secret}");
}

$url = 'http://' . getenv('ESHOST') . "/{$index}/{$other}";
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, true);
if (array_key_exists('HTTP_CONTENT_LENGTH', $_SERVER)) {
    $content = file_get_contents('php://input');
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Length: ' . $_SERVER['HTTP_CONTENT_LENGTH']));
} else {
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Length: 0'));
}
$content = curl_exec($curl);
curl_close($curl);
list($header, $body) = explode("\r\n\r\n", $content, 2);
foreach (explode("\r\n", $header) as $header_line) {
    header($header_line);
}
echo $body;
