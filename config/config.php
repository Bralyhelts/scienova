<?php
declare(strict_types=1);

return [

    'app_name' => 'ELITESHELF',

    'base_url' => '',

    'timezone' => 'Africa/Kampala',

    'db' => [
        'host' => 'localhost',
        'name' => 'YOUR_DATABASE_NAME',
        'user' => 'YOUR_DATABASE_USER',
        'pass' => 'YOUR_DATABASE_PASSWORD',
        'charset' => 'utf8mb4'
    ],

    'ai' => [
        'endpoint' => 'https://api.openai.com/v1/responses',
        'api_key' => '',
        'model' => 'gpt-4.1-mini'
    ],

    'admin_seed_email' => 'admin@example.com',

    'admin_seed_password' => '',

    'upload_dir' => dirname(__DIR__) . '/storage/papers',

    'private_backup_dir' => dirname(__DIR__) . '/storage/backups',

    'max_upload_bytes' => 250 * 1024 * 1024,

    'currency' => 'UGX',

    'payment_phone' => '0766599663',

    'session_name' => 'ELITESHELFSESSID'

];
