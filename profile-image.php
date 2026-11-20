<?php

require __DIR__ . '/config/bootstrap.php';

$user = require_login();

$picture = $user['profile_pic'] ?? '';

if (
    !$picture ||
    !str_starts_with(
        $picture,
        'storage/profiles/'
    )
) {

    http_response_code(404);

    exit;

}

$filename = basename($picture);

$path = __DIR__
    . '/storage/profiles/'
    . $filename;

if (!is_file($path)) {

    http_response_code(404);

    exit;

}

$mime = (
    new finfo(FILEINFO_MIME_TYPE)
)->file($path);

if (!in_array(
    $mime,
    [
        'image/jpeg',
        'image/png',
        'image/webp'
    ],
    true
)) {

    http_response_code(404);

    exit;

}

header('Content-Type: ' . $mime);

header('Cache-Control: private, max-age=300');

readfile($path);

exit;
