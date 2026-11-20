<?php

return [

    'db' => [

        'host' => 'localhost',

        'name' => 'YOUR_CPANEL_DATABASE',

        'user' => 'YOUR_CPANEL_DB_USER',

        'pass' => 'YOUR_DATABASE_PASSWORD',

        'charset' => 'utf8mb4'

    ],

    'ai' => [

        'endpoint' => 'https://api.openai.com/v1/responses',

        'api_key' => 'YOUR_AI_API_KEY',

        'model' => 'gpt-4.1-mini'

    ],

    'admin_seed_email' => 'admin@yourdomain.com',

    'admin_seed_password' => 'CHANGE_TO_A_LONG_UNIQUE_PASSWORD'

];
