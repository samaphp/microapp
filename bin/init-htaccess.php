#!/usr/bin/env php
<?php

$target = getcwd() . '/.htaccess';
$source = __DIR__ . '/../.htaccess';

if (file_exists($target)) {
    echo "✅ .htaccess already exists. Skipping copy.\n";
    exit(0);
}

if (!file_exists($source)) {
    echo "❌ .htaccess not found in MicroApp package.\n";
    exit(1);
}

copy($source, $target);
echo "✅ .htaccess copied to project root as .htaccess\n";
