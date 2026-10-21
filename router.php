<?php
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) ? rawurldecode($requestPath) : '/';
$requestedFile = __DIR__ . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $requestPath), DIRECTORY_SEPARATOR);
$realRoot = realpath(__DIR__);
$realFile = realpath($requestedFile);

if (
    $requestPath !== '/'
    && $realRoot !== false
    && $realFile !== false
    && stripos($realFile, $realRoot . DIRECTORY_SEPARATOR) === 0
    && is_file($realFile)
) {
    return false;
}

require __DIR__ . '/index.php';
