<?php

declare(strict_types=1);

$config = require __DIR__ . '/config.php';

$local = __DIR__ . '/local.php';

if (is_file($local)) {

    $override = require $local;

    $config = array_replace_recursive(
        $config,
        $override
    );

}

date_default_timezone_set(
    $config['timezone']
);

if (session_status() !== PHP_SESSION_ACTIVE) {

    session_name($config['session_name']);

    session_set_cookie_params([

        'httponly' => true,

        'secure' => (
            !empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off'
        ),

        'samesite' => 'Lax',

        'path' => '/'

    ]);

    session_start();

}

require_once __DIR__ . '/../includes/functions.php';

$db = $config['db'];

$dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";

$pdo = new PDO(

    $dsn,

    $db['user'],

    $db['pass'],

    [

        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        PDO::ATTR_EMULATE_PREPARES => false

    ]

);
