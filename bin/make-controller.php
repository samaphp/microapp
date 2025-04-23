#!/usr/bin/env php
<?php

if ($argc < 2) {
    echo "❌ Usage: php make-controller.php ControllerClassName [route]\n";
    echo "   Example: php make-controller.php HomeController /home\n";
    exit(1);
}

$controllerClass = preg_replace('/[^a-zA-Z0-9]/', '', $argv[1]);

if (substr($controllerClass, -10) !== 'Controller') {
    echo "❌ Controller name must end with 'Controller'.\n";
    echo "   Example: php make-controller.php HomeController\n";
    exit(1);
}

$namespace = 'App\\Controller';
$dir = getcwd() . '/src/Controller';
$file = "$dir/{$controllerClass}.php";

$defaultRoute = '/' . strtolower(substr($controllerClass, 0, -10));
$route = $argv[2] ?? $defaultRoute;

if (substr($route, 0, 1) !== '/') {
    echo "❌ Route must start with '/'.\n";
    echo "   Example: php make-controller.php HomeController /home\n";
    exit(1);
}

// validate route it is real web route
if (preg_match('/[^a-zA-Z0-9\/]/', $route)) {
    echo "❌ Route must only contain letters, numbers, and '/'.\n";
    echo "   Example: php make-controller.php HomeController /home\n";
    exit(1);
}

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
    echo "📁 Created directory: $dir\n";
}

if (file_exists($file)) {
    echo "✅ Controller already exists: $file\n";
    exit(0);
}

$template = <<<PHP
<?php
declare(strict_types=1);

namespace $namespace;

use MicroApp\MicroApp;

class {$controllerClass}
{
    public function routes(MicroApp \$app): void
    {
        \$app->get('{$route}', [\$this, 'index']);
    }

    public function index(): void
    {
        echo 'Hello from {$controllerClass}';
    }
}
PHP;

file_put_contents($file, $template);
echo "✅ Created: $file\n";
