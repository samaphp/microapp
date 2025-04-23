#!/usr/bin/env php
<?php
require getcwd() . '/vendor/autoload.php';

use MicroApp\MicroApp;

$app = new MicroApp();
$dir = getcwd() . '/src/Controller';
$app->loadRoutesFrom($dir, 'App\\Controller');

$routes = $app->getAllRoutes();

echo "📋 Registered Routes:\n\n";

foreach ($routes as $method => $routeList) {
    foreach ($routeList as $route => $handler) {
        $controller = 'unknown';

        if (is_array($handler) && is_object($handler[0])) {
            $controller = get_class($handler[0]);
        }

        echo str_pad(strtoupper($method), 8)
            . str_pad($route, 20)
            . $controller . "\n";
    }
}
