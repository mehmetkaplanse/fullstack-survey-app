<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5175',
        'https://fullstack-survey-app.onrender.com', // Gerçek URL'inizi yazın
        'https://your-frontend-domain.vercel.app',
        // Frontend'inizin deploy edildiği URL'yi buraya ekleyin
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // Bu önemli!
];

