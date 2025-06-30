<?php

return [
    'encoding_key' => env('ENCODING_KEY'),
    'header_key' => env('X_HEADER_KEY', 'X-SHPAZY-AUTH'),
    'header_value' => env('X_HEADER_VALUE', 'your-secret-value'),

    // Reward service keys
    'auth_header_key' => env('REWARD_HEADER_KEY'),
    'auth_header_value' => env('REWARD_HEADER_VALUE'),
];
