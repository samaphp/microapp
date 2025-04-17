<?php
declare(strict_types=1);

class MicroApp {
    private array $routes = [];

    public function get(string $route, callable $handler): void {
        $this->routes['GET'][$this->normalize($route)] = $handler;
    }

    public function post(string $route, callable $handler): void {
        $this->routes['POST'][$this->normalize($route)] = $handler;
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
        $path = $this->normalize($path);

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $params = [];
            if ($this->match($route, $path, $params)) {
                echo call_user_func_array($handler, $params);
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    private function normalize(string $path): string {
        return '/' . trim($path, '/');
    }

    private function match(string $route, string $path, array &$params): bool {
        $routeParts = explode('/', trim($route, '/'));
        $pathParts = explode('/', trim($path, '/'));

        if (count($routeParts) !== count($pathParts)) return false;

        foreach ($routeParts as $i => $part) {
            if (preg_match('/^{\w+}$/', $part)) {
                $params[] = $pathParts[$i];
            } elseif ($part !== $pathParts[$i]) {
                return false;
            }
        }

        return true;
    }
}
