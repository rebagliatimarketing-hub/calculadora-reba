<?php

// Vercel ejecuta PHP en modo serverless; Laravel necesita estas rutas escribibles.
foreach ([
    '/tmp/views',
    '/tmp/ssr',
    '/tmp/framework/cache',
    '/tmp/framework/cache/data',
    '/tmp/framework/sessions',
    '/tmp/framework/testing',
    '/tmp/logs',
] as $path) {
    if (! is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

require __DIR__.'/../public/index.php';
