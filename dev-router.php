<?php

$publicPath = realpath(__DIR__ . '/public');
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$requestedFile = realpath($publicPath . $requestPath);

if (
    $_SERVER['REQUEST_METHOD'] === 'GET'
    && $requestedFile !== false
    && str_starts_with($requestedFile, $publicPath . DIRECTORY_SEPARATOR)
    && is_file($requestedFile)
) {
    return false;
}

chdir($publicPath);
require $publicPath . '/index.php';
