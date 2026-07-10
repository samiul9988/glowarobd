<?php

return [
    'models' => [
        'notices' => [
            'model' => \App\Models\Notice::class,
            'date_field' => 'publish_at',
        ],
        'job-posts' => [
            'model' => \App\Models\JobPost::class,
            'date_field' => 'published_at',
        ],
    ],
];
