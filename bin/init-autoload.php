#!/usr/bin/env php
<?php

$composerFile = getcwd() . '/composer.json';

if (!file_exists($composerFile)) {
    echo "❌ composer.json not found in current directory.\n";
    exit(1);
}

$composer = json_decode(file_get_contents($composerFile), true);

$modified = false;

if (!isset($composer['autoload'])) {
    $composer['autoload'] = ['psr-4' => []];
    $modified = true;
}

if (!isset($composer['autoload']['psr-4'])) {
    $composer['autoload']['psr-4'] = [];
    $modified = true;
}

if (!isset($composer['autoload']['psr-4']['App\\'])) {
    $composer['autoload']['psr-4']['App\\'] = 'src/';
    $modified = true;
    echo "✅ Added 'App\\\\' => 'src/' to composer.json\n";
}

if ($modified) {
    file_put_contents($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    echo "🔧 Running composer dump-autoload...\n";
    passthru('composer dump-autoload', $code);
    if ($code === 0) {
        echo "✅ Autoload dumped successfully.\n";
    } else {
        echo "❌ Failed to run composer dump-autoload\n";
        exit(1);
    }
} else {
    echo "✅ 'App\\\\' autoload already exists. Nothing changed.\n";
}
