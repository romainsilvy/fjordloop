<?php

return [
    'default_src' => ["'self'"],
    'img_src' => ["'self'", 'data:', 'blob:', 'https://*.tile.openstreetmap.org',  'https://minio.silvy-leligois.fr'],
    'style_src' => ["'self'", "'unsafe-inline'", 'https://fonts.bunny.net'],
    'font_src' => ["'self'", 'data:', 'https://fonts.bunny.net'],
    'script_src' => ["'self'"],
    'connect_src' => ["'self'"],
    'worker_src' => ["'self'", 'blob:'],
    'frame_ancestors' => ["'self'"],
];
