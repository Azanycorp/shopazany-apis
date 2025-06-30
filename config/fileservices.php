<?php

return [
    'providers' => [
        [
            'name' => 'imagekit',
            'retries' => 3,
        ],
        [
            'name' => 's3',
            'retries' => 2,
        ],
        [
            'name' => 'cloudinary',
            'retries' => 2,
        ],
        [
            'name' => 'local',
            'retries' => 1,
        ],
    ],
];
