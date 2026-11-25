<?php
$env = static function (string $key, string $fallback = ''): string {
    $value = getenv($key);

    return ($value === false || $value === '') ? $fallback : $value;
};

return [
    'default' => [
        'host' => $env('DB_HOST', '127.0.0.1'),
        'port' => $env('DB_PORT', '3307'),
        'dbname' => $env('DB_NAME', 'pcaiad'),
        'user' => $env('DB_USER', 'test_user'),
        'pass' => $env('DB_PASS', 'password')
    ],
    'name' => [
        'host' => $env('DB_NAME_HOST'),
        'port' => $env('DB_NAME_PORT', '3306'),
        'dbname' => $env('DB_NAME_DATABASE'),
        'user' => $env('DB_NAME_USER'),
        'pass' => $env('DB_NAME_PASS')
    ]
];
