<?php

// project root
define('BASE_PATH', dirname(__DIR__));

// storage
define('STORAGE_PATH', BASE_PATH . '/storage');

// private file storage. In production this can point to a NAS via FILE_STORAGE_PATH.
$configuredFileStoragePath = getenv('FILE_STORAGE_PATH');
define(
    'MNT_PATH',
    rtrim(
        ($configuredFileStoragePath === false || $configuredFileStoragePath === '')
            ? BASE_PATH . '/mnt'
            : $configuredFileStoragePath,
        '/\\'
    )
);
