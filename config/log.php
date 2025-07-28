<?php
return [
  'cronwatcher' => [
    'driver' => 'daily',
    'path' => storage_path('logs/cronwatcher.log'),
    'level' => 'debug',
    'days' => 30,
  ],
];
