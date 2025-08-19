<?php

declare(strict_types=1);

return [
    'cronwatcher' => [
        'driver' => 'daily',
        'path'   => storage_path('logs/cronwatcher.log'),
        'level'  => 'debug',
        'days'   => 30,
    ],
];
