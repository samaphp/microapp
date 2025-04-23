#!/usr/bin/env php
<?php

$target = getcwd() . '/index.php';

if (file_exists($target)) {
    echo "✅ index.php already exists. Skipping.\n";
    exit(0);
}

$template = <<<PHP
<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use MicroApp\MicroApp;

\$app = new MicroApp();
\$app->loadRoutesFrom(__DIR__ . '/src/Controller', 'App\\\\Controller');
\$app->dispatch();
PHP;

file_put_contents($target, $template);
echo "✅ index.php created in project root.\n";
