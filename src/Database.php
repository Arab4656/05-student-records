<?php

function db(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $config = require __DIR__ . '/../config/config.php';
    $settings = $config['db'];
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $settings['host'],
        $settings['name']
    );

    $connection = new PDO($dsn, $settings['user'], $settings['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}
